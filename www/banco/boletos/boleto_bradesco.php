<?php
// DADOS DO BOLETO PARA O SEU CLIENTE
//$dias_de_prazo_para_pagamento = 5;
//$taxa_boleto = 2.95;
//$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
//$valor_cobrado = "2950,00"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
//$valor_cobrado = str_replace(",", ".",$valor_cobrado);
//$valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

//PASSAR OS DADOS ABAIXO
//Dados de entrada
// $sacado
// $endereco
// $municipio, $uf, $cep
// $taxa_boleto
// $num_doc
// $data_venc
// $valor_boleto
// $num_doc, $data_venc, $valor_boleto sao obrigatorios para geracao da linha digitavel

// NÃO ALTERAR!
include("include/funcoes_bradesco_fixo.php"); 
include("include/funcoes_bradesco.php"); 
include("include/layout_bradesco.php");
?>
