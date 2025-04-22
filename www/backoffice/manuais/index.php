<!-- INCLUDES INICIAIS  -->
<?php 

require_once '../../includes/constantes.php';
require_once RAIZ_DO_PROJETO . "backoffice/includes/topo.php";
require_once DIR_CLASS . "util/Util.class.php";

?>

<?php
    //Verifica ação solicitada. Caso não tenha nenhuma ação, a listagem vem por padrão
    $acao	= isset($_REQUEST['acao']) ? $_REQUEST['acao'] : 'listar';
    
    
    //Realizando inserção do novo manual
    if($acao == 'inserir'){
        
        //Verificando se foi enviado um arquivo
        if(!isset($_FILES['manual_pdf'])){
            $msg[] = "É obrigatório enviar um arquivo pro manual";
        }
        //VERIFICANDO EXTENSãO DO ARQUIVO
        $ext	= explode('/',$_FILES['manual_pdf']['type']);
        
        //O ARQUIVO DEVE SER PDF
        if(count($ext) < 1){
            $msg[] = "Por favor, selecione um arquivo referente ao manual.";
        }else if(!in_array($ext[1],['pdf'])) {
            $msg[] = "O arquivo deve ser no formato pdf";
        }
        
        if(!isset($msg)){
            //SE FOR UM NOVO PROJETO, DEVE-SE CRIAR UM NOVO MENU. SE NÃO, APENAS CRIA-SE O ITEM
            if(isset($_POST["novo_projeto"])){
                
                if(isset($_POST['novo_projeto_nome']) && !empty($_POST['novo_projeto_nome'])){
                    $sql = "INSERT INTO bo_menu (menu_id,menu_descricao, aba_id, menu_fixo) VALUES ((select max(menu_id)+1 from bo_menu),'" . $_POST['novo_projeto_nome'] . "', " . $currentAba->getId() . ", 1) RETURNING menu_id, menu_descricao;";
                    $rs_menu = SQLexecuteQuery($sql);

                    $menu = pg_fetch_array($rs_menu);

                    if(empty($menu)){
                        $msg[] = "O menu ".$_POST['novo_projeto_nome']." não foi adicionado com sucesso.";
                        $color = "txt-vermelho";
                    }else{
                        //CRIANDO PASTA REFERENTE AO PROJETO    
                        if(!file_exists(Util::cleanStr($menu["menu_descricao"]))){
                            mkdir(Util::cleanStr($menu["menu_descricao"]));
                        }
                        
                    }
                }else{
                    $msg[] = "A opção de novo projeto foi marcada, porém um nome pro novo projeto no foi informado";
                }

            }else{
                $sql = "SELECT menu_id, menu_descricao FROM bo_menu WHERE menu_id = " . $_POST["menu_id"] . " LIMIT 1";
                $rs_menu = SQLexecuteQuery($sql);
                $menu = pg_fetch_array($rs_menu);
            }
        }
        
        if(!isset($msg)){
            //CRIANDO O ITEM DO MANUAL
            
            //CONTEM A PASTA RELACIONADA AO MENU
            $diretorio = Util::cleanStr($menu["menu_descricao"]);
            
            $sql = "INSERT INTO bo_item (item_descricao, item_link, item_link_linux, item_monitor, menu_id, item_aparece_menu) VALUES ('" . $_POST["manual_nome"] . "', '/manuais/" . $diretorio . "/" . Util::cleanStr($_POST["manual_nome"]) . ".php', '/manuais/" . $diretorio . "/" . Util::cleanStr($_POST["manual_nome"]) . ".php', '', " . $menu["menu_id"] . ", 1) RETURNING item_id;";
            $rs_item = SQLexecuteQuery($sql);

            //CASO O ITEM SEJA CRIADO, COMEÇA O CÓDIGO DE INCLUSÃO DE GRUPOS
            if(!empty($rs_item)){
                //DANDO ACESSO AO GRUPO ADMINISTRADOR PARA O ITEM CRIADO
                $item_id = pg_fetch_array($rs_item)[0];
                $sql = "insert into nivel_acesso_item_grupo (grupos_id, item_id, nivel_id) values (1, " . $item_id . ", 1)";
                $rs_grupo = SQLexecuteQuery($sql);
                //CASO NÃO TENHA TIDO PROBLEMAS EM DAR ACESSO AO GRUPO, ELE IRÁ CRIAR O ARQUIVO REFERENTE AO ITEM
                if(!$rs_grupo){
                    $msg[] = "O grupo de administradores não foi vinculado com sucesso ao novo item.";
                    $color = "txt-vermelho";
                }else{
                    //INICIALIZANDO VARIÁVEIS
                    $caminho = RAIZ_DO_PROJETO . "backoffice/manuais/" . $diretorio . "/";
                    $nome_arquivo = Util::cleanStr(trim($_POST["manual_nome"]));
                    
                    if(!file_exists($caminho)){
                        mkdir($caminho);
                    }

                    //VERIFICANDO EXTENSãO DO ARQUIVO
                    $ext	= explode('/',$_FILES['manual_pdf']['type']);

                    //O ARQUIVO DEVE SER PDF
                    if(in_array($ext[1],['pdf'])) {

                        //SE FOR, O UPLOAD é FEITO
                        move_uploaded_file($_FILES["manual_pdf"]["tmp_name"],"$caminho".$nome_arquivo.".pdf");                  

                        //CRIANDO ARQUIVO PHP
                        $file = fopen($caminho . $nome_arquivo . ".php", "w+");
                        $header = '<?php ' . PHP_EOL
                                . 'require_once "../../../includes/constantes.php";' . PHP_EOL
                                . 'require_once RAIZ_DO_PROJETO . "backoffice/includes/topo.php";' . PHP_EOL   
                                . 'require_once RAIZ_DO_PROJETO . "backoffice/manuais/includes/navegacao.php";' . PHP_EOL
                                . '?>' . PHP_EOL;
                        fwrite($file, $header);

                        $button = "<a href='/manuais/" . $diretorio . "/".$nome_arquivo.".pdf' download='".$nome_arquivo."' class='btn btn-info'>Download</a>";
                        fwrite($file, $button);


                        $iframe = "<div class='row top10'>" . PHP_EOL
                                . "<div class='col-md-12'>" . PHP_EOL
                                . "<iframe style='border:1px solid #666CCC' title='".$_POST["manual_nome"]."' src='<?php echo basename(__FILE__, \".php\") ?>.pdf' frameborder='1' scrolling='auto' height='1100' width='850' ></iframe>" . PHP_EOL
                                . "</div>" . PHP_EOL
                                . "</div>" . PHP_EOL;
                        fwrite($file, $iframe);

                        $footer = '<?php'. PHP_EOL
                                . 'require_once RAIZ_DO_PROJETO . "backoffice/includes/rodape_bko.php";'. PHP_EOL
                                . '?>'. PHP_EOL;

                        fwrite($file, $footer);
                        fclose($file);
                    }

                }
            }else{
                $msg[] = "O item ".$_POST['manual_nome']." não foi adicionado com sucesso.";
                $color = "txt-vermelho";
            }
        }
        
        if(isset($msg)){
            $acao = 'novo';
        }else{
            $acao = 'listar';
        }
        
    }
    
    //CÓDIGO DE ATUALIZAÇÃO DE MANUAL
    if($acao == 'atualizar'){
        
        //SE FOR UM NOVO PROJETO, DEVE-SE CRIAR UM NOVO MENU. SE NÃO, APENAS PEGA O ID DO SELECIONADO
        if(isset($_POST["novo_projeto"])){

            $sql = "INSERT INTO bo_menu (menu_id,menu_descricao, aba_id, menu_fixo) VALUES ((select max(menu_id)+1 from bo_menu),'" . $_POST['novo_projeto_nome'] . "', " . $currentAba->getId() . ", 1) RETURNING menu_id, menu_descricao;";
            $rs_menu = SQLexecuteQuery($sql);
            
            $menu = pg_fetch_array($rs_menu);
            
            if(empty($menu["menu_id"])){
                $msg[] = "O menu ".$_POST['novo_projeto_nome']." não foi adicionado com sucesso.";
            }else{
                if(!file_exists(Util::cleanStr($menu["menu_descricao"]))){
                    mkdir(Util::cleanStr($menu["menu_descricao"]));
                }
            }
            
        }else{
            $sql = "SELECT menu_id, menu_descricao FROM bo_menu WHERE menu_id = " . $_POST["menu_id"] . " LIMIT 1";
            $rs_menu = SQLexecuteQuery($sql);
            $menu = pg_fetch_array($rs_menu);
        }
        
        if(!isset($msg)){
            
            //INICIALIZANDO VARIÁVEIS
            
            //CONTEM A PASTA RELACIONADA AO MENU
            $diretorio = Util::cleanStr($menu["menu_descricao"]);
            
            $caminho = RAIZ_DO_PROJETO . "backoffice/manuais/" . $diretorio . "/";
            $nome_arquivo = Util::cleanStr(trim($_POST["manual_nome"]));
            
            if(!file_exists($caminho)){
                mkdir($caminho);
            }
            
            $link = "/manuais/" . $diretorio . "/" . $nome_arquivo . ".php";
            
            //VERIFICA SE HOUVE TROCA DE MENU
            if($_POST["menu_id_antigo"] != $menu["menu_id"]){              
                
                //ATUALIZANDO MENU E O LINK DO ITEM
                $sql = "UPDATE bo_item SET menu_id = " . $menu["menu_id"] . ", item_link = '" . $link . "', item_link_linux = '" . $link . "' WHERE item_id = " . $manual_id_update;

                $rs_item = SQLexecuteQuery($sql);

                if(!$rs_item) {
                        $msg[] = "Erro ao atualizar informa&ccedil;&otilde;es do manual. ($sql)<br>";
                }else{
                    
                    //APOS ALTERAR O MENU E O LINK, DEVE-SE MOVER O ARQUIVO DO DIRETORIO ANTIGO PARA O NOVO
                    //PARA ISSO SELECIONA-SE O MENU ANTIGO PARA OBTER A DESCRICAO
                    $sql = "SELECT menu_descricao FROM bo_menu WHERE menu_id = " . $_POST["menu_id_antigo"] . "LIMIT 1";
                    $rs_menu = SQLexecuteQuery($sql);
                    $menu_antigo = pg_fetch_array($rs_menu);

                    $diretorio_antigo = Util::cleanStr($menu_antigo["menu_descricao"]);
                    $caminho_antigo = RAIZ_DO_PROJETO . "backoffice/manuais/" . $diretorio_antigo . "/";
  
                    
                    $arquivos = scandir($caminho_antigo);
                    foreach($arquivos as $arquivo){
                        if($arquivo != '.' && $arquivo != ".." && strpos($arquivo, $nome_arquivo) !== false){
                            rename($caminho_antigo . $arquivo, $caminho . $arquivo);
                        }                    
                    }
                    
                }
            }         
            
            if(!isset($msg)) {
                        
                    if(isset($_FILES["manual_pdf"])){
                        //VERIFICANDO EXTENSãO DO ARQUIVO
                        $ext	= explode('/',$_FILES['manual_pdf']['type']);
                        
                        //O ARQUIVO DEVE SER PDF
                        if(count($ext) > 1 && in_array($ext[1],['pdf'])) {
                            //FAZENDO ROTAÇÃO DO MANUAL
                            $ultima_modificacao = date("Ymd_His", filemtime($caminho . $nome_arquivo . ".pdf"));
                            rename($diretorio . "/" . $nome_arquivo . ".pdf", $diretorio . "/" . $nome_arquivo . "_" . $ultima_modificacao . ".pdf");

                            //SE FOR, O UPLOAD é FEITO
                            move_uploaded_file($_FILES["manual_pdf"]["tmp_name"],"$caminho".$nome_arquivo.".pdf");
                        }
                    }
                    
                    $mensagem = "O usuário " . $GLOBALS['_SESSION']['userlogin_bko'] . " alterou o manual '" . $_POST["manual_nome"] . "'" . PHP_EOL;
                    gravaLog_Manuais($mensagem);

            }
        }
        
        $acao = "listar";
        
    }
    
    //ABRINDO PÁGINA DE EDIÇÃO DE MANUAL
    if($acao == 'editar'){
        //SELECIONANDO MANUAL ESCOLHIDO
        $sql = "SELECT
                        item.item_id,
                        item.item_descricao,
                        item.item_link_linux,
                        menu.menu_id
                FROM    
                        bo_item as item
                INNER JOIN 
                        bo_menu as menu ON item.menu_id = menu.menu_id
                WHERE   
                        item.item_id = " . $manual_id;
	//echo $sql."<br>";
	$rs_item = SQLexecuteQuery($sql);
        
	if(!($rs_item_row = pg_fetch_array($rs_item))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es do manual. ($sql)<br>";
	}
	else {
                //INSTANCIANDO VARIÁVEIS COM VALORES DO MANUAL
		$manual_id              = $rs_item_row['item_id'];
		$manual_nome		= $rs_item_row['item_descricao'];
		$menu_id                = $rs_item_row['menu_id'];
                $item_link              = $rs_item_row['item_link_linux'];
                $manual_pdf             = Util::cleanStr($manual_nome) . ".pdf";
                
		if (pg_num_rows($rs_item) > 0)
			include 'manual_edt.php';
		else
			$acao = 'listar';
	}
    }

?>
    
    
<?php

    //Realiza includes com base na ação
    if($acao == 'novo')
    {
        include 'manual_edt.php';
    }

    if($acao == 'listar')
    {
        include 'manual_lst.php';
    }

?>



<!--RODAPE-->
<?php

require_once RAIZ_DO_PROJETO . "backoffice/includes/rodape_bko.php";

?>
