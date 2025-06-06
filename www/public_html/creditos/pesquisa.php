<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
session_start();
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO."class/pdv/controller/IndexController.class.php";
require_once "../../db/ConnectionPDO.php";

function sanitizeDate($date) {
    // Remove todos os caracteres que não são números, hífens, ou barras
    $date = preg_replace('/[^0-9\-\/]/', '', $date);
    // Remove espaços desnecessários
    return trim($date);
}

$controller = new IndexController;
$filtro['b2c'] = false;
$filtro['id_user'] = $controller->usuarios->getId();


$findOperadoras = "select opr_nome,opr_codigo from operadoras where opr_codigo in(124, 13, 53) order by opr_nome;";
$resultadoOperadoras = SQLexecuteQuery($findOperadoras);
$operadoras = pg_fetch_all($resultadoOperadoras);

if(isset($_POST["btnEnviar"])){
	
	$dataInicial = $_POST["dtInicial"];
	$dataFinal = $_POST["dtFinal"];
    $selectOperadora = $_POST["operadora"];
	$erro = "";
	
	if(empty($dataInicial) || empty($dataFinal)){
		$erro = "Voc&ecirc; deve preencher os dois campos de data";
	}else if(empty($selectOperadora)){
        $erro = "Voc&ecirc; deve selecionar uma operadora";
    }else{

		$conexao = ConnectionPDO::getConnection()->getLink();
		// Sanitiza as datas antes de usar
		$dataInicial = sanitizeDate($dataInicial);
		$dataFinal = sanitizeDate($dataFinal);

		// SQL com placeholders para evitar SQL Injection
		$sqlFind = "
			SELECT * 
			FROM tb_dist_venda_games 
			INNER JOIN tb_dist_venda_games_modelo ON vgm_vg_id = vg_id 
			INNER JOIN tb_dist_venda_games_modelo_pins ON vgmp_vgm_id = vgm_id 
			INNER JOIN pins ON vgmp_pin_codinterno = pin_codinterno 
			WHERE vg_ug_id = :usuarioId 
			  AND vgm_opr_codigo = :operadora 
			  AND pin_status = '6' 
			  AND pin_game_id IS NULL 
			  AND pin_guid_parceiro IS NULL 
			  AND date(vg_data_inclusao) BETWEEN :dataInicial AND :dataFinal 
			ORDER BY vg_data_inclusao;
		";

		// Preparando a query
		$query = $conexao->prepare($sqlFind);

		// Associando os valores aos placeholders
		$query->bindValue(':usuarioId', $controller->usuarios->getId(), PDO::PARAM_INT);
		$query->bindValue(':operadora', $selectOperadora, PDO::PARAM_INT);
		$query->bindValue(':dataInicial', $dataInicial, PDO::PARAM_STR);
		$query->bindValue(':dataFinal', $dataFinal, PDO::PARAM_STR);

		// Executando a query
		$query->execute();

		// Fetching the results
		$dados = $query->fetchAll(PDO::FETCH_ASSOC);

		// Armazenando os resultados na sessão, se houverem dados
		if (!empty($dados)) {
		$_SESSION["pins"] = $dados;
		}
	}
	
}

require_once "includes/header.php";
?>
<div style="min-height: 70vh;" class="container txt-azul-claro bg-branco">
	<div class="col-md-12 col-lg-12 col-xs-12 col-sm-12">
	   <div class="row top40" style="margin-bottom: 20px;">
			<div class="col-md-12">
				<span class="glyphicon hidden-sm glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
				<strong class="pull-left top15 color-blue font20">Pesquisa de pins n&atilde;o utilizados</strong>  
			</div>
		</div>
		<div class="row hidden-sm hidden-xs">
            <div class="col-md-8 ">
                <hr class="border-blue">
            </div> 
        </div>
		<div style="margin-top: 10px;">
		    <form method="POST">
                <select name="operadora" style="padding: 10px 10px;margin-right: 8px;margin-bottom: 10px;" id="operadora" class="">
                    <?php
                        if(count($operadoras) > 0){
                            echo '<option value="">Selecione uma operadora</option>';
                            foreach($operadoras as $key => $value){
                                if($selectOperadora == $value["opr_codigo"]){
                                    echo '<option value="'.$value["opr_codigo"].'" selected>'.str_replace("E-pp cash lanhouse", "E-prepag cash", ucfirst(strtolower($value["opr_nome"]))).'</option>';
                                }else{
                                    echo '<option value="'.$value["opr_codigo"].'">'.str_replace("E-pp cash lanhouse", "E-prepag cash", ucfirst(strtolower($value["opr_nome"]))).'</option>';
                                }   
                            }
                        }else{
                            echo '<option value="">Selecione uma operadora</option>';
                        }
                    ?>
                </select>
			    <input type="date" style="padding: 0 10px;margin-right: 8px;margin-bottom: 10px;" value="<?php echo isset($dataInicial)?$dataInicial:"";?>" name="dtInicial" max="<?php echo date("Y-m-d");?>" min="<?php echo date("Y-m-d", strtotime("-6 month"));?>" id="">
				<input type="date" style="padding: 0 10px;margin-right: 8px;margin-bottom: 10px;" value="<?php echo isset($dataFinal)?$dataFinal:"";?>" name="dtFinal" max="<?php echo date("Y-m-d");?>" min="<?php echo date("Y-m-d", strtotime("-6 month"));?>" id="">
				<input value="Pesquisa" style="padding: 9px;" name="btnEnviar" class="btn btn-success" id="" type="submit">
			</form>
		</div>
		<?php if(isset($_POST["btnEnviar"])){ ?>
			<div style="margin: 10px 0;">
			     <?php if(!empty($erro)){ ?>
			          <div id="error" style="padding: 8px;background-color: #246b89;color: white;margin: 10px 0;width: fit-content;border-radius: 5px;"><span style="top: 3px;" class="glyphicon glyphicon-info-sign"></span> <?php echo $erro;?></div>
				 <?php }elseif(empty($erro) && !empty($dados)){ ?>
				      <a href="geraExcel.php" class="btn btn-success bottom10">Excel</a>
			     <?php } ?>
				 <table style="width: 100%;" border="1">
					  <thead>
						   <tr style="background-color: #246b89;color: white;">
							   <th style="padding: 10px;text-align: center;">Data</th>
							   <th style="padding: 10px;text-align: center;">Id do pedido</th>
							   <th style="padding: 10px;text-align: center;">Pin</th>
							   <th style="padding: 10px;text-align: center;">Valor</th>
						   </tr>
					  </thead>
					  <tbody>
						   <?php 
							  if(!empty($dados)){
								   foreach($dados as $key => $value){ ?>
									   <tr>
										   <td style="padding: 10px;text-align: center;"><?php echo substr($value["vg_data_inclusao"], 8, 2)."/".substr($value["vg_data_inclusao"], 5, 2)."/".substr($value["vg_data_inclusao"], 0, 4);?></td>
										   <td style="padding: 10px;text-align: center;"><?php echo $value["vg_id"];?></td>
										   <td style="padding: 10px;text-align: center;"><?php echo $value["pin_codigo"];?></td>
										   <td style="padding: 10px;text-align: center;"><?php echo $value["vgm_valor"];?></td>
									   </tr>
						   <?php 
								   } 
							  }else{
						   ?>
							<tr>
							   <td colspan="4" style="padding: 10px;text-align: center;">Nenhum pin foi encontrado nesse periodo</td>
							<tr>
						   <?php
							  }
						   ?>
					  </tbody>
				 </table>
			</div>
		<?php } ?>
	</div>
</div>
<?php
require_once "includes/footer.php";