<?php 
    session_start();
	if(!isset($_SESSION["excelBack"]) && empty($_SESSION["excelBack"])){
		header("location: https://www.e-prepag.com.br/sys/admin/");
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Document</title>
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th style="text-align: center;">Data Devolução</th>
                <th style="text-align: center;">Tipo Devolução</th>
                <th style="text-align: center;">Cliente</th>
                <th style="text-align: center;">Forma</th>
                <th style="text-align: center;">ID Usuário</th>
                <th style="text-align: center;">Titular</th>
                <th style="text-align: center;">CPF Titular</th>
                <th style="text-align: center;">Pedido</th>
                <th style="text-align: center;">Motivo</th>
                <th style="text-align: center;">Publisher</th>
				<?php if($_SESSION["opr_vinculo"] == 124 || $_SESSION["opr_vinculo"] == 0){ ?>
					<th style="text-align: center;">Display txn_id</th>
				<?php } ?>
                <th style="text-align: center;">PIN Bloqueado Publisher</th>
                <th style="text-align: center;">Valor R$</th>
            </tr>
        </thead>
        <tbody>
            <?php

                //error_reporting(E_ALL); 
                //ini_set("display_errors", 1); 
                $arquivo = 'chargeBack.xls';
                header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
                header ("Cache-Control: no-cache, must-revalidate");
                header ("Pragma: no-cache");
                header ("Content-type: application/x-msexcel");
                header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" ); 

                $raiz_do_projeto = "/www/";
                require_once "/www/includes/constantes.php";
                require_once $raiz_do_projeto . "includes/gamer/constantes.php";
                require_once "/www/class/util/Util.class.php";

                // configurações/legendas relacionadas aos dados recebidos do banco de dados
                $vetorTipo = array('1' => 'ChargeBack', '2' => 'Estorno');
                $vetorTipoUsuario = array('G' => 'Gamer', 'L' => 'Lan House');
                $vetorFormaDevolucao = array('1' => 'Devolução em Saldo', '2' => 'Devolução através de Depósito');
                $vetorPINsBloqueados = array('0' => 'NÃO foi Bloqueado', '1' => 'Foi Bloqueado');
                $total_geral = 0;
                $vetorPublisher = $_SESSION["oprs"];
              
                $EstornoChargeBack = $_SESSION["excelBack"];
                foreach($EstornoChargeBack as $informacoes){
                    $total_geral += $informacoes['ec_valor'];
					 $td = "";
					 if(count($informacoes['cod_garena']) > 0){
						foreach($informacoes['cod_garena'] as $value){
							if($value != "" && $value != null){
								$td .= "'".$value."' <br>";
							}
						}
					}else{
						$td = "Não possui";
					}

                    echo '
                        <tr>
                        <td style="text-align: center;">'. Util::getData($informacoes['ec_data_devolucao']).'</td>
						<td style="text-align: center;">'.Util::getData($informacoes['vg_data_inclusao']).'</td>
						<td style="text-align: center;">'.$FORMAS_PAGAMENTO_DESCRICAO_NUMERICO[$informacoes['vg_pagto_tipo']].'</td>
                        <td style="text-align: center;">'.$vetorTipo[$informacoes['ec_tipo']].'</td>
                        <td style="text-align: center;">'.(isset($informacoes['ec_forma_devolucao'])?$vetorFormaDevolucao[$informacoes['ec_forma_devolucao']]:"").'</td>
                        <td style="text-align: center;">'.$informacoes['ug_id'].'</td>
                        <td style="text-align: center;">'.ucwords(strtolower((isset($informacoes['edb_titular'])?$informacoes['edb_titular']:$informacoes['usuarioNome']))).'</td>
                        <td style="text-align: center;">'.(isset($informacoes['edb_cpf_cnpj'])?$informacoes['edb_cpf_cnpj']:substr($informacoes['ug_cpf'], 0, 3).".".substr($informacoes['ug_cpf'], 3, 3).".".substr($informacoes['ug_cpf'], 6, 3)."-".substr($informacoes['ug_cpf'], 9, 2)).'</td>
                        <td style="text-align: center;">'.$informacoes['vg_id'].'</td>
                        <td style="text-align: center;">'.$vetorPublisher[$informacoes['opr_codigo']].'</td>
						<td style="text-align: center;">'.$td.'</td>
                        <td style="text-align: center;">'.$vetorPINsBloqueados[$informacoes['ec_pin_bloqueado']].'</td>
                        <td style="text-align: center;">'.Util::getNumero($informacoes['ec_valor']).'</td>
                        </tr>';
                     
                }

                $numeroColunas = ($_SESSION["opr_vinculo"] == 124 || $_SESSION["opr_vinculo"] == 0)? "12":"11";
                echo '<tr>
                <td colspan="'.$numeroColunas.'" align="right"><b>Total R$</b></td>
                <td><b>'.Util::getNumero($total_geral).'</b></td>
                </tr>';
            ?>
        </tbody>
    </table>
</body>
</html>