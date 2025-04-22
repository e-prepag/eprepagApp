<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<link rel="stylesheet" type="text/css" href="/css/gamer/cssClassLista.css" />
<?php
$acao	= isset($_REQUEST['acao']) ? $_REQUEST['acao'] : 'listar';

$msg	= "";

$formatos = array('jpg','jpeg','gif','png');

if($acao == 'inserir')
{
	$ext	= explode('/',$_FILES['mat_promo_banner']['type']);

	if(in_array($ext[1],$formatos)) {
		$pasta = $raiz_do_projeto."public_html/imagens/pdv/material_promocional/";
		if(file_exists("$pasta".$_FILES["mat_promo_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor, renomear antes.<br>";
			$mat_promo_banner = null;
		}
		else {
			move_uploaded_file($_FILES["mat_promo_banner"]["tmp_name"],"$pasta".$_FILES["mat_promo_banner"]["name"]);
			$mat_promo_banner = $_FILES["mat_promo_banner"]["name"];
		}
	}
	else $msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
	
        if(!empty($mat_promo_banner)) {
            $sql = "INSERT INTO dist_materiais_promocionais (
                                                            mp_data_inclusao,
                                                            mp_descricao,
                                                            mp_ativo,
                                                            mp_ordem,
                                                            mp_imagem_banner,
                                                            mp_lista_ids_inclusao,
                                                            mp_wallpapers,
                                                            mp_cartaz,
                                                            mp_torneios,
                                                            mp_detalhes
                                                            ) 
                                            VALUES (
                                                            NOW(), 
                                                            '".str_replace("'",'"',$mat_promo_nome_update)."', 
                                                            ";
            if (empty($mat_promo_ativo)) {
                    $sql .= "0,";
            }else {
                    $sql .= "1,";
            }
            if (empty($mat_promo_ordem)) {
                    $sql .= "0,";
            }else {
                    $sql .= "$mat_promo_ordem,";
            }
            if (empty($mat_promo_banner)) {
                    $sql .= "NULL,";
            }
            else {
                    $sql .= "'".$mat_promo_banner."',";
            }
            if (empty($mat_promo_ids_inclusao)) {
                    $sql .= "NULL,";
            }
            else {
                    $sql .= "'".trim($mat_promo_ids_inclusao)."',";
            }
            $sql .= "   '$mat_promo_wallpapers',
                        '$mat_promo_cartaz',
                        '$mat_promo_torneios',
                        '$mat_promo_detalhes');";

            //echo $sql."<br>";
            $rs_material_promocional = SQLexecuteQuery($sql);
            if(!$rs_material_promocional) {
                    $msg .= "Erro ao salvar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
            }
            $acao = 'listar';
        }//end if(!empty($mat_promo_banner))
	else $msg .= "Não foi informado o Arquivo com o Logotipo (obrigatório).<br>";
}//end if($acao == 'inserir')

if($acao == 'atualizar')
{
	$ext	= explode('/',$_FILES['mat_promo_banner']['type']);

	$mat_promo_banner = null;
        if(!empty($_FILES["mat_promo_banner"]["name"])) {
            if(in_array($ext[1],$formatos)) {

                        $pasta = $raiz_do_projeto."public_html/imagens/pdv/material_promocional/";
                        if(file_exists("$pasta".$_FILES["mat_promo_banner"]["name"])){
                                $msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor renomear antes.<br>";
                        }
                        else {
                                move_uploaded_file($_FILES["mat_promo_banner"]["tmp_name"],"$pasta".$_FILES["mat_promo_banner"]["name"]);
                                $mat_promo_banner = $_FILES["mat_promo_banner"]["name"];
                        }
            }
            else {
                    $msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
            }
  	} 
        $sql = "UPDATE dist_materiais_promocionais SET
						mp_data_alteracao		= NOW(),
						mp_descricao			= '".str_replace("'",'"',$mat_promo_nome_update)."',
						mp_lista_ids_inclusao		= '".trim($mat_promo_ids_inclusao)."',
                                                mp_wallpapers                   = '".trim($mat_promo_wallpapers)."',
                                                mp_cartaz                       = '".trim($mat_promo_cartaz)."',
                                                mp_torneios                     = '".trim($mat_promo_torneios)."',
                                                mp_detalhes		= '".trim($mat_promo_detalhes)."',
                                                ";
	if (!empty($mat_promo_banner)) {
		$sql .= "		mp_imagem_banner			= '".$mat_promo_banner."',";
	}
	if (empty($mat_promo_ativo)) {
		$sql .= "		mp_ativo				= '0',";
	}else {
		$sql .= "		mp_ativo				= '1',";
	}
	if (empty($mat_promo_ordem)) {
		$sql .= "		mp_ordem				= '0'";
	}else {
		$sql .= "		mp_ordem				= '$mat_promo_ordem'";
	}
	$sql .= "	WHERE	mp_id			= $mat_promo_id_update";
	//echo $sql."<br>:SQL<br>";
	$rs_material_promocional = SQLexecuteQuery($sql);
	if(!$rs_material_promocional) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es da question&aacute;rio ID:($mat_promo_id_update).<br>";
	}

	//isset($_REQUEST['mat_promo_id']);
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT *
            FROM dist_materiais_promocionais 
            WHERE mp_id = $mat_promo_id"; 
	//echo $sql."<br>";
	$rs_material_promocional = SQLexecuteQuery($sql);
	if(!($rs_material_promocional_row = pg_fetch_array($rs_material_promocional))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	else {

		$mat_promo_id		= $rs_material_promocional_row['mp_id'];
		$mat_promo_nome		= $rs_material_promocional_row['mp_descricao'];
		$mat_promo_ativo	= $rs_material_promocional_row['mp_ativo'];
                $mat_promo_ordem        = $rs_material_promocional_row['mp_ordem'];
		$mat_promo_banner	= $rs_material_promocional_row['mp_imagem_banner'];
		$mat_promo_ids_inclusao = $rs_material_promocional_row['mp_lista_ids_inclusao'];
                $mat_promo_wallpapers	= $rs_material_promocional_row['mp_wallpapers'];
                $mat_promo_cartaz       = $rs_material_promocional_row['mp_cartaz'];
                $mat_promo_torneios     = $rs_material_promocional_row['mp_torneios'];
                $mat_promo_detalhes     = $rs_material_promocional_row['mp_detalhes'];
		if (pg_num_rows($rs_material_promocional) > 0)
			require_once 'material_promocional_edt.php';
		else
			$acao = 'listar';
	}
}

if($acao == 'novo')
{
    $mat_promo_id		= null;
    $mat_promo_nome		= null;
    $mat_promo_ativo		= null;
    $mat_promo_ordem            = null;
    $mat_promo_banner		= null;
    $mat_promo_ids_inclusao	= null;
    $mat_promo_wallpapers	= null;
    $mat_promo_cartaz           = null;
    $mat_promo_torneios         = null;
    $mat_promo_detalhes         = null;
    require_once 'material_promocional_edt.php';
}

if($acao == 'listar')
{
    require_once 'material_promocional_lst.php';
}
//echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>
