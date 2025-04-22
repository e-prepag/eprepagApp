<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/MeuCadastroController.class.php";
header("Content-Type: text/html; charset=ISO-8859-1",true);
$controller = new MeuCadastroController;

if(isset($_POST['telefone_contato']))
{
    $retorno = array();
    
    if(!$controller->atualizaCadastro($_POST))
    {
        $retorno[] = $controller->erros;
        $cor = "txt-vermelho";
    }
    else
    {
        $cor = "txt-verde";
        $retorno[] = "Informações alteradas com sucesso";
        
    }
}

//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$sql_socios = "SELECT * FROM dist_usuarios_games_socios WHERE ug_id = :ug_id order by ugs_percentagem DESC";

$stmt = $pdo->prepare($sql_socios);
$user_id = $controller->usuarios->getId();
$stmt->bindParam(':ug_id', $user_id, PDO::PARAM_INT);

$stmt->execute();
$socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$estabelecimentos = $controller->getEstabelecimentos();

$banner = $controller->getBanner();

require_once "includes/header.php";
?>
<div id="modal-edicao" class="modal fade" role="dialog">
    <div class="modal-dialog" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h5 class="modal-title txt-azul-claro"><strong>Atenção!</strong></h5>
            </div>
            <div class="modal-body txt-verde">
                <p class="txt-cinza">Após inserir os novos dados, clique em "Salvar" no final da página.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<div class="container bg-branco">
    <div class="row">
        <div class="col-md-10 txt-preto">
            <form method="post" id="formCad" name="formCad">
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento txt-azul-claro">
                        <strong>Meu Cadastro</strong>
                    </div>
                </div>
<?php
                if(isset($retorno))
                {
?>
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento <?php echo $cor; ?>">
<?php 
                        foreach($retorno as $val)
                        {
                            echo "<p><strong>".$val."</strong></p>";
                        }
?>
                        
                    </div>
                </div>
<?php
                }
?>                
                
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Login: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9 "><?php echo $controller->usuarios->getLogin(); ?> <a class="txt-azul-claro" href="/creditos/alterar_login.php">alterar</a></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">E-mail: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getEmail(); ?> <a class="txt-azul-claro" href="/creditos/alterar_email.php">alterar</a></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Senha: </div>
                    <div class="col-md-3 col-lg-9 col-sm-9 col-xs-9">******** <a class="txt-azul-claro" href="/creditos/alterar_senha.php">alterar</a></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right"><label for="telefone_contato">Telefone: </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="text" name="telefone_contato" id="telefone_contato" maxlength="14" char="14" required <?php if($controller->usuarios->getTelDDD()!= "" && $controller->usuarios->getTel() != ""){ ?>value="<?php echo $controller->usuarios->getTelDDD()." ".$controller->usuarios->getTel(); ?>"<?php } ?> class="telefone form-control w-auto">
                        <span class="txt-vermelho" id="errotelefone_contato" style="display:none;">O telefone está incompleto.</span>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right"><label for="celular_contato">Celular: </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="text" name="celular_contato" id="celular_contato" maxlength="15" char="14" required <?php if($controller->usuarios->getCelDDD()!= "" && $controller->usuarios->getCel() != ""){ ?>value="<?php echo $controller->usuarios->getCelDDD()." ".$controller->usuarios->getCel(); ?>"<?php } ?> class="celular form-control w-auto">
                        <span class="txt-vermelho" id="errocelular_contato" style="display:none;">O telefone está incompleto.</span>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Skype: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="text" name="skype" id="skype" value="<?php echo ($controller->usuarios->getReprVendaMSN() != "") ? $controller->usuarios->getReprVendaMSN() : $controller->usuarios->getReprLegalMSN();?>" class="form-control w-auto"></div>
                </div>
                <div class="row top10">
                    <p class="p-left25"><strong>Representante da empresa:</strong></p>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Nome: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getReprLegalNome(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">RG: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getReprLegalRG(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">CPF: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getReprLegalCPF(); ?></div>
                </div>
                <div class="row top10">
                    <p class="p-left25"><strong>Sócios:</strong></p>
                </div>
<?php
        if(count($socios) > 0){
            for($j=0; $j < count($socios); $j++) {             
?>  
                <div class="row top10">
                    <p class="p-left25"><strong>Sócio <?php echo ($j+1) ?> </strong></p>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Nome: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $socios[$j]['ugs_nome']; ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">CPF: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $socios[$j]['ugs_cpf']; ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Data Nascimento: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo formata_data($socios[$j]['ugs_data_nascimento'], 0); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Porcentagem na Empresa: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $socios[$j]['ugs_percentagem'] . "%"; ?></div>
                </div>
<?php                
            }
        } else{
?>              
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">*</div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">Sócios ainda não informados. Entre em contato com o <a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>/game/suporte.php" target="_blank">Suporte</a> para atualizar seu cadastro.</div>
                </div>
<?php
        }
?>
                <div class="row top10">
                    <p class="p-left25"><strong>Dados do estabelecimento:</strong></p>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Nome: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="hidden" name="fantasia_empresa" id="fantasia_empresa" char="5" maxlength="100" required value="<?php echo $controller->usuarios->getNomeFantasia();?>">
                        <?php echo $controller->usuarios->getNomeFantasia(); ?>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Razão Social: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getRazaoSocial(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right"><?php echo (($controller->usuarios->getTipoCadastro() == "PF")?"CPF":"CNPJ"); ?>: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo (($controller->usuarios->getTipoCadastro() == "PF")?($controller->usuarios->getCPF()?mascara_cnpj_cpf(preg_replace('/[^0-9]/', '', $controller->usuarios->getCPF()),"cpf"):"<div class='txt-vermelho'>Campo faltante entre em contato com o suporte E-Prepag</div>"):($controller->usuarios->getCNPJ()?mascara_cnpj_cpf(preg_replace('/[^0-9]/', '', $controller->usuarios->getCNPJ()),"cnpj"):"<div class='txt-vermelho'>Campo faltante entre em contato com o suporte E-Prepag</div>")); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right"><label for="tipo_estabelecimento">Tipo: </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <select name="tipo_estabelecimento_empresa" id="tipo_estabelecimento" char="1" class="form-control w-auto" required>
                            <option value=""> Selecione </option>
<?php
                        foreach($estabelecimentos as $ind => $val)
                        {
                            $selected = "";
                            if($controller->usuarios->getTipoEstabelecimento() == $ind)
                                $selected = "selected";
                            echo '<option value="'.$ind.'" '.$selected.'>'.$val.'</option>';
                        }
?>                            
                            <!-- option value="Outros" <?php if($controller->usuarios->getTipoEstabelecimento() == "Outros") echo "selected"; ?>> Outros </option -->
                        </select>
                        <span class="txt-vermelho" id="errotipo_estabelecimento" style="display:none;">Opção inválida.</span>
                    </div>
                </div>
<!--                <div class="row top5" id="divOutros_tipo_estabelecimento" style="display:none;">
                    <div class="col-md-3 text-right"><label for="outro_estabelecimento">Qual?</label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <input type="text" id="outro_estabelecimento" name="outro_estabelecimento" size="9" char="5" class="form-control  w-auto" /><span class="form_obs">(Sem hífen ou espaços)</span>
                    </div>
                </div>-->
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-12 col-xs-12 text-left"><label for="faturamento_medio">Faturamento médio mensal: </label></div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9">
                        <select name="faturamento_medio" char="1" id="faturamento_medio" class="form-control w-auto" required>
                            <option value=""> Selecione </option>
                            <option value="1" <?php if($controller->usuarios->getFaturaMediaMensal() == "1") echo "selected"; ?>>Menor que R$ 5.000,00</option>
                            <option value="2" <?php if($controller->usuarios->getFaturaMediaMensal() == "2") echo "selected"; ?>>R$ 5.000,01 - R$ 10.000,00</option>
                            <option value="3" <?php if($controller->usuarios->getFaturaMediaMensal() == "3") echo "selected"; ?>>R$ 10.000,01 - R$ 20.000,00</option>
                            <option value="4" <?php if($controller->usuarios->getFaturaMediaMensal() == "4") echo "selected"; ?>>Acima de R$ 20.000,00</option>
                        </select>
                        <span class="txt-vermelho" id="errofaturamento_medio" style="display:none;">Opção inválida.</span>
                    </div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">CEP: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getCEP(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Estado: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getEstado(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Cidade: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getCidade(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Bairro: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getBairro(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Endereço: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getEndereco(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3  col-lg-3 col-sm-3 col-xs-3 text-right">Número: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getNumero(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Complemento: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><?php echo $controller->usuarios->getComplemento(); ?></div>
                </div>
                <div class="row top5">
                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-3 text-right">Site: </div>
                    <div class="col-md-9 col-lg-9 col-sm-9 col-xs-9"><input type="text" name="site" value="<?php echo $controller->usuarios->getSite();?>" class="form-control w-auto"></div>
                </div>
                <div class="row top10">
                    <div class="col-md-offset-3 col-md-9 fontsize-pp">
                        <p>Precisa alterar algum campo não disponível?</p>
                        <p>Por favor, entre em contato com o <a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>/game/suporte.php" target="_blank">suporte</a>.</p>
                    </div>
                </div>
                <div class="row top10 bottom10">
                    <div class="col-md-offset-3 col-md-9 fontsize-pp">
                        <button type="button" id="btnSalvar" name="salvar" value="1" class="btn btn-info">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-2 hidden-sm hidden-xs p-top10">
<?php 
            if($banner){
                foreach($banner as $b){
?>
                <div class="row pull-right">
                    <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $controller->objBanner->urlLink.$b->imagem; ?>" width="186" class="p-3" title="<?php echo $b->titulo; ?>"></a>
                </div>
<?php 
                }
            }
?>
            <div class="row pull-right facebook">
            </div>
        </div>
    </div>
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script>
$(function(){
    $('.telefone').mask('(99) 9999-9999');
    $('.celular').mask('(00) 90000-0000');
    
    var  aviso = 0;
    
    $(".form-control").focus(function(){
       if(aviso === 0){
           $("#modal-edicao").modal();
           aviso++;
       } 
    });
    
    $("#btnSalvar").click(function(){
       var id = "";
       var char = null;
       var tamanho = null;
       var erros = [];
       
       $(".form-control").each(function(){
            if($(this).attr("required")){
                char = $(this).attr("char");
                tamanho = $(this).val().length;
                id = $(this).attr("id");

                if(tamanho < char){
                    $("label[for='"+id+"']").css("color","red");
                    erros.push(id);
                }
            }
       });

        if(erros.length > 0)
        {
            for (i = 0; i < erros.length; i++)
            { 
                $("#erro"+erros[i]).show();
            }
        }else{
            $("#formCad").submit();
        }
    });
    
//    $("#tipo_estabelecimento").change(function(){
//        if($(this).val() == "Outros"){
//            $("#outro_estabelecimento").attr("required","required");
//            $("#divOutros_tipo_estabelecimento").show();
//        }else{
//            $("#outro_estabelecimento").removeAttr("required");
//            $("#divOutros_tipo_estabelecimento").hide();
//        }
//    });
    
});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";

