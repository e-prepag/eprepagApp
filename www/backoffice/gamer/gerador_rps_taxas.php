<?php
require_once "/www/includes/bourls.php";
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
//header("Content-Type: text/html; charset=UTF-8",true);
//header("Content-Type: text/html; charset=ISO-8859-1",true);
function BuscaMaiorSequencialAdmnistradora() {
        $sql = "
                select MAX(nfes_seq) as nfes_seq
                FROM (
                        select MAX(nfes_seq) as nfes_seq from nfse_epp_adm

                        UNION ALL

                        select MAX(nfes_seq) as nfes_seq from tb_pag_taxa_anual
                ) as t;";
        $resRPS = SQLexecuteQuery($sql);
        if ($resRPSrow = pg_fetch_array($resRPS)) {
                if(empty($resRPSrow['nfes_seq'])) {
                        $varNumeroRPSaux = 1;
                }
                else {
                        $varNumeroRPSaux = $resRPSrow['nfes_seq']+1;
                }
                return $varNumeroRPSaux;
        } //end if ($resRPSrow = pg_fetch_array($resRPS))
        else {
                die("Erro na captura do Sequencial RPS.<br>Contactar o Administrador.");
        } //end else do if ($resRPSrow = pg_fetch_array($resRPS))
}//end function BuscaMaiorSequencialAdmnistradora()

//Função que retorna o valor do ISS para a Cidade informada
function RetonaISSCidade($cidade,$estado,&$varAliquotaRPS,&$varSituacaoRPS) {
    	if(trim($cidade) != "" && trim($estado) != "") {
		//Teste existencia de alicota cadastrada
		$sql = "SELECT iss_aliquota FROM iss_cidade WHERE iss_cidade = '".trim($cidade)."' AND iss_estado = '".trim($estado)."';";
		$rs_iss = SQLexecuteQuery($sql);
		if($rs_iss && pg_num_rows($rs_iss) > 0) {
                    $rs_iss_row = pg_fetch_array($rs_iss);
                    if(trim($cidade) != CIDADE_DEFAULT) {
                        $varSituacaoRPS = "F";
                    }
                    $varAliquotaRPS = str_pad(($rs_iss_row['iss_aliquota']*100),4,"0",STR_PAD_LEFT);
                    return true;
                }
		else {
            		return false;
		}
	}
	else {
            	return false;
	}
}//end function RetonaISSCidade($cidade,$estado,&$varAliquotaRPS,&$varSituacaoRPS) 

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."public_html/sys/admin/vendas/vendas_estab/nfesp_lote.php";
require_once $raiz_do_projeto."includes/configISSCidade.php";
set_time_limit(3600);
define("CIDADE_DEFAULT","SÃO PAULO");
define("ESTADO_DEFAULT","SP");

/* 
    CONTROLLER
 */
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = "";
        
        //Montando SQL para a Busca das Taxas Envolvidas na Geração do RPS
        $sql = "
                SELECT * 
                FROM tb_pag_taxa_anual pta
                    INNER JOIN usuarios_games ug ON ug.ug_id = pta.ug_id
                WHERE 
                    pta_data >= '".formata_data($_POST["data_inicial"],1)." 00:00:00' 
                    AND  pta_data <= '".formata_data($_POST["data_final"],1)." 23:59:59'; 
                ";
        //echo $sql."<br>";
        $rs = SQLexecuteQuery($sql);
        if($rs) {
            
                //Setando variaveis para captura no mês referência
                //setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
                //date_default_timezone_set('America/Fortaleza');
                $mesFechamento = mktime(0, 0, 0, date("n"), 1, date("Y")-1);
                
                //RPS EPP ADM
                $sNFeADM = "";
                $data = date("Ymd");
                $sNFeADM .= gera_cabecalho_administradora($data);
                $total_geral_adm=0;
                $cont_nota_adm=0;

                //Contantes no RPS
                $varTipoRPS = "RPS";
                $varSerieRPS = "EPP";
                $varNumLote = ""; //str_pad($loteid, 4, "0", STR_PAD_LEFT);
                $varDataEmissaoRPS = $data;
                $varCodigoServicoRPS = "05820"; 
                $varISSRetido = "2";

                //Alicota de IRRF para fins de cálculos
                $alicotaIRRF = 1.5;

                //Limite para discriminação do IRRF da Nota Fiscal
                $limiteInformeIRRF = 10;

                //Capturando o número sequencial
                $varNumeroRPSaux = BuscaMaiorSequencialAdmnistradora();
                $varLoteAuxADM = $varNumeroRPSaux;
                while ($rsRow = pg_fetch_array($rs)) {
                    
                        //Reset no laço as variaveis de Situação RPS e alíquota de ISS
                        $varSituacaoRPS = "T"; //Operação Normal
                        $varAliquotaRPS = "0200";
                        
                        if(empty($rsRow['nfes_seq'])) {
                                $varNumeroRPS = str_pad($varNumeroRPSaux, 12, "0", STR_PAD_LEFT);
                                $sql = "UPDATE tb_pag_taxa_anual SET nfes_seq = ".$varNumeroRPSaux." WHERE pta_id = ".$rsRow['pta_id'].";";
                                //echo $sql."<br>";
                                $rsUpdate = SQLexecuteQuery($sql);
                                if(!$rsUpdate) {
                                        $msg .= "ERRO: Erro ao salvar o número sequencial de RPS na tabela de taxas.<br>";
                                }//end if(!$rsUpdate)
                                else {
                                        //Incrementando o serial
                                        $varNumeroRPSaux++;
                                }//end else do if(!$rsUpdate)
                        }//end if(empty($rsRow['nfes_seq']))
                        else {
                                // Número de RPS já salvo em execuções anteriores
                                $varNumeroRPS = str_pad($rsRow['nfes_seq'], 12, "0", STR_PAD_LEFT);
                        }//end else do if(empty($rsRow['nfes_seq']))
                        
                        $valor_reg_nfe = $rsRow['pta_valor']*100;
                        $varValorRPS = str_pad($valor_reg_nfe, 15, "0", STR_PAD_LEFT);
                        $varDeducaoRPS = str_pad(0, 15, "0", STR_PAD_LEFT);

                        //Limpando pontuação
                        $CPF_aux = str_replace(".", "", $rsRow['ug_cpf']);
                        $CPF_aux = str_replace("-", "", $CPF_aux);
                        if(empty($CPF_aux) || strlen($CPF_aux)<11) {
                                $varIndicadorCPF = "3"; 
                                $CPF_aux = "";
                        }
                        else {
                                $varIndicadorCPF = "1"; 
                        }

                        //Limpando CEP
                        $CEP_aux = str_replace(".", "", $rsRow['ug_cep']);
                        $CEP_aux = str_replace("-", "", $CEP_aux);

                        $varCPF		=	$CPF_aux;
                        $varIM		=	str_pad("", 8, "0", STR_PAD_LEFT);
                        $varIE		=	str_pad("", 12, "0", STR_PAD_LEFT);
                        $varNome	=	str_pad(substr($rsRow['ug_nome_cpf'],0,75), 75, " ", STR_PAD_RIGHT);	
                        $varTipoEndereco=	str_pad(substr($rsRow['ug_endereco'],0,3), 3, " ", STR_PAD_RIGHT);
                        $varEndereco	=	str_pad(substr($rsRow['ug_endereco'],3,50), 50, " ", STR_PAD_RIGHT);
                        $varNumero	=	str_pad(substr($rsRow['ug_numero'],0,10), 10, " ", STR_PAD_RIGHT);
                        $varComplemento =	str_pad(substr($rsRow['ug_complemento'],0,30), 30, " ", STR_PAD_RIGHT);
                        $varBairro	=	str_pad(substr($rsRow['ug_bairro'],0,30), 30, " ", STR_PAD_RIGHT);
                        $varCidade	=	str_pad(substr($rsRow['ug_cidade'],0,50), 50, " ", STR_PAD_RIGHT);
                        $varUF		=	str_pad(substr($rsRow['ug_estado'],0,2), 2, " ", STR_PAD_RIGHT);
                        $varCEP		=	str_pad(substr($CEP_aux,0,8), 8, " ", STR_PAD_RIGHT);
                        $varEmail	=	""; //str_pad($rsRow['ug_email'], 75, " ", STR_PAD_RIGHT); 

                        $varDiscriminacao = "Tarifa de manutenção anual ".date("Y",$mesFechamento).".";
                        $totalIRRFaux = $rsRow['pta_valor']*$alicotaIRRF/100;
                        if($totalIRRFaux >= $limiteInformeIRRF) {
                                $varDiscriminacao .=  "|"."|"."Valor Total NF: R$ ".  number_format($rsRow['pta_valor'], 2, ",", ".")."|"."IRRF (".$alicotaIRRF."%)...: R$ ".  number_format($totalIRRFaux, 2, ",", ".")."|"."Valor Líquido.: R$ ".number_format(($rsRow['pta_valor']-$totalIRRFaux), 2, ",", ".");
                        }//end if($totalIRRFaux>=10) 

                        if(!validaAlgoritimoCPF($rsRow['ug_cpf']) || trim($rsRow['ug_nome_cpf']) == "" || trim($rsRow['ug_cidade']) == "" || trim($rsRow['ug_estado']) == "") {
                                $varCidade	= str_pad(CIDADE_DEFAULT, 50, " ", STR_PAD_RIGHT);
                                $varUF		= str_pad(ESTADO_DEFAULT, 2, " ", STR_PAD_RIGHT);
                                $varCEP		= str_pad("", 8, " ", STR_PAD_RIGHT);
                        }//end if(!validaAlgoritimoCPF($rsRow['ug_cep']) || trim($rsRow['ug_nome_cpf']) == "" || trim($rsRow['ug_cidade']) == "")
                        
                        //Verificando se ISS está configurado por Cidade ou NÃO
                        if(ISS_CIDADE) {
                                /*
                                 * Aplicando regras do ISS conforme fluxograma enviado por email em 07/03/2018
                                 */
                                if(!RetonaISSCidade($rsRow['ug_cidade'],$rsRow['ug_estado'],$varAliquotaRPS,$varSituacaoRPS)) {
                                        $varCidade	= str_pad(CIDADE_DEFAULT, 50, " ", STR_PAD_RIGHT);
                                        $varUF		= str_pad(ESTADO_DEFAULT, 2, " ", STR_PAD_RIGHT);
                                        $varCEP		= str_pad("", 8, " ", STR_PAD_RIGHT);
                                }
                        }//end if(ISS_CIDADE)
                        
                        //Gerando a linha RPS
                        $sNFeADM .= gera_lote($varTipoRPS, $varSerieRPS, $varNumeroRPS, $varNumLote, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao,""); 


                        //Totalizando para o Rodapé do RPS
                        $total_geral_adm += $rsRow['pta_valor'];
                        $cont_nota_adm++;
                        
                }//end while
                
                //Gerando o Rodapé do arquivo RPS
                $sNFeADM .= gera_rodape($cont_nota_adm, number_format($total_geral_adm, 2, ".", ""));
                
                //Salvando o Arquivo
                if($cont_nota_adm > 0) {
                        $varArquivo = $raiz_do_projeto . "arquivos_gerados/rps/rps_lote_".date("Ymd")."_".str_pad($varLoteAuxADM, 7, "0", STR_PAD_LEFT)."_ADMINISTRADORA.txt";
                        $handle = fopen($varArquivo, "w+");
                        if (fwrite($handle, $sNFeADM) === FALSE) {
                                $msg .= "Não foi possível gravar em '$varArquivo'.";
                        } else {
                                $msg .= "<div id='download' class='c-pointer' onClick='gerarArquivo(\"".$varArquivo."\");'>EPP - Administradora => Arquivo de lote Nº ".str_pad($varLoteAuxADM, 7, "0", STR_PAD_LEFT)." gravado com sucesso.</div>";
                        }
                        fclose($handle);
                }
                else {
                        $msg .= "O período selecionado não contém taxas para emissão de RPS.<br>";
                }
        }//end if($rs) 
        else {
                $msg .= "ERRO: Problema na seleção das Taxas Anuais.<br>";
        }//end else do if($rs) 
} // end if($BtnSearch)
/*
    FIM CONTROLLER
 */
?>
<link href="<?php echo $server_url_complete; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_complete; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_complete; ?>/js/global.js"></script>
<script>
function gerarArquivo(varArquivo) {
        window.location.href = '/includes/download/rps_download.php?varArquivo='+varArquivo;
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <div class="col-md-2 text-right">Data inicial:</div>
        <div class="col-md-2">
            <input type="text" value="<?php if(isset($_POST["data_inicial"]))  echo $_POST["data_inicial"]; else echo date('d/m/Y'); ?>" id="data_inicial" name="data_inicial" char="10" class="form-control data w150">
        </div>
        <div class="col-md-2 text-right">Data final:</div>
        <div class="col-md-2">
            <input type="text" value="<?php if(isset($_POST["data_final"])) echo $_POST["data_final"]; else echo date('d/m/Y'); ?>" id="data_final" name="data_final" char="10" class="form-control data w150">
        </div>
        <div class="col-md-2 pull-right">
            <button type="submit" name="BtnSearch" value="Gerar" class="btn pull-right btn-success">Gerar</button>
        </div>
    </form>
</div>
<div class="col-md-12">
<?php
if(isset($msg)) echo $msg;
?>
</div>
<script>
    jQuery(function(e){

        var optDate = new Object();
            optDate.interval = 1000;
            optDate.minDate = "19/01/2016";

        setDateInterval('data_inicial','data_final',optDate);
        
        $("#buscar").click(function(){
            var erro = [];
            
            $(".form-control").each(function(){
                 if($(this).val().length < $(this).attr("char"))
                     erro.push($(this).attr("label"));
            });
            
            if(erro.length > 4)
            {
                var msgErro = "Nenhum campo foi preenchido";
                alert(msgErro);
            }
            else
               $("#"+$(this).get(0).form.id).submit();

       });
   });
</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>