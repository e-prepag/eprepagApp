<?php
$data_vencimento = date("dmY");
// $total_geral é calculado em venda_e_modelos_calculate.php
$valor_0 = $total_geral;		

//$bbr_idConv		= "306233";     //Id Conv. EPP Pagamentos
$bbr_idConv		= "318525";     //Id Conv. EPP Administradora de Cartões
$bbr_refTran		= str_pad($OrderId, 17, "0", STR_PAD_LEFT);
$bbr_valor		= str_pad(100*$valor_0, 15, "0", STR_PAD_LEFT);
$bbr_qtdPontos		= str_pad(0, 15, "0", STR_PAD_LEFT);
$bbr_dtVenc		= $data_vencimento;
$bbr_tpPagamento	= "3";	//	3 – Débito em Conta Internet
$bbr_urlRetorno		= "/prepag2/pag/bbr/bb_retorno.php?idret=".$bbr_refTran;
$bbr_urlInforma		= "/prepag2/pag/bbr/bb_informa.php?idret=".$bbr_refTran;
$bbr_nome		= "";
$bbr_endereco		= "";
$bbr_cidade		= "";
$bbr_uf			= "";
$bbr_cep		= "";
$bbr_msgLoja		= "Obrigado por comprar na EPP, aguarde confirmação por email para entrega do seu produto.";

$formato		= "03";	//	01 – HTML (Retorno visual em página do Banco para controle manual)
                                                //	02 – XML (Retorno em tag XML)
                                                //	03 – String (Retorno em forma de String)
/*
ver processReturnCodeBancodoBrasil($scode)
$a_situacao = array(
                                        '00' => 'pagamento efetuado', 
                                        '01' => 'pagamento não autorizado', 
                                        '02' => 'erro no processamento da consulta', 
                                        '03' => 'pagamento não localizado', 
                                        '10' => 'campo “idConv” inválido ou nulo', 
                                        '11' => 'valor informado é inválido, nulo ou não confere com o valor registrado', 
                                        '99' => 'Operação cancelada pelo cliente'
                                        );
*/

$link_BBDebito = "https://mpag.bb.com.br/site/mpag/";

$link_BBDebito_Sonda = "https://mpag.bb.com.br/site/mpag/REC3.jsp";
$link_BBDebito_SondaPOST = "idConv=".$bbr_idConv."&refTran=".$bbr_refTran."&valorSonda=".$bbr_valor."&qtdPontos=".$bbr_qtdPontos."&formato=".$formato."";
?> 