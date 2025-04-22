<?php
//Alicota EPP Administradora
$alicota_epp_adm = array(6.38);//6.38;

//incluindo o arquivo do fpdf
require_once "../../../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "public_html/sys/includes/fpdf/fpdf.php";
header('Content-type: text/html; charset=UTF-8');

require DIR_INCS . "configuracao.inc";
//require $_SERVER['DOCUMENT_ROOT']."/connections/connect.php";
require_once RAIZ_DO_PROJETO . "db/connect.php";
//include $_SERVER['DOCUMENT_ROOT']."/incs/header.php";
//include $_SERVER['DOCUMENT_ROOT']."/incs/security.php";
require_once DIR_INCS . "functions.php";
//echo "dd_operadora sem POST:".$dd_operadora."<br>";
//echo "dd_operadora com POST:".$_POST['dd_operadora']."<br>";
$msg = "";
if (!empty($_POST['dd_operadora'])) {
        // monta a query apara bsucar dados da operadora
        $sql = "SELECT * FROM operadoras WHERE opr_codigo = " . $_POST['dd_operadora'];
        $rs_operadoras = SQLexecuteQuery($sql);
        //verifica se retornou o registro 
        if (!($rs_operadoras_row = pg_fetch_array($rs_operadoras))) {
                $msg .= "Erro ao consultar informa&ccedil;&otilde;es da Operadora. ($sql)<br>";
                die();
        } else {
                // carrega as informa��es necess�rias para elabora��o do relat�rio
                $opr_codigo = $rs_operadoras_row['opr_codigo'];
                $opr_razao = $rs_operadoras_row['opr_razao'];
                $opr_endereco = $rs_operadoras_row['opr_endereco'];
                $opr_numero = $rs_operadoras_row['opr_numero'];
                $opr_complemento = $rs_operadoras_row['opr_complemento'];
                $opr_bairro = $rs_operadoras_row['opr_bairro'];
                $opr_cidade = $rs_operadoras_row['opr_cidade'];
                $opr_estado = $rs_operadoras_row['opr_estado'];
                $opr_pais = $rs_operadoras_row['opr_pais'];
                $opr_internacional = $rs_operadoras_row['opr_internacional'];
                $opr_numero_conta = $rs_operadoras_row['opr_numero_conta'];
                $opr_tipo_conta = $rs_operadoras_row['opr_tipo_conta'];
                $opr_numero_roteamento = $rs_operadoras_row['opr_numero_roteamento'];
                $opr_banco_nome = $rs_operadoras_row['opr_banco_nome'];
                $opr_banco_endereco = $rs_operadoras_row['opr_banco_endereco'];
                $opr_banco_cidade = $rs_operadoras_row['opr_banco_cidade'];
                $opr_banco_telefone = $rs_operadoras_row['opr_banco_telefone'];
                $opr_moeda_corrente = $rs_operadoras_row['opr_moeda_corrente'];
                $opr_iban = $rs_operadoras_row['opr_iban'];
                $opr_bic_code = $rs_operadoras_row['opr_bic_code'];
                $opr_numero_contrato = $rs_operadoras_row['opr_numero_contrato'];
                $opr_banco_intermediario = $rs_operadoras_row['opr_banco_intermediario'];
                if (!empty($opr_banco_intermediario)) {
                        // monta a query apara bsucar dados da operadora
                        $sql = "SELECT * FROM operadoras_banco_intermediario WHERE opr_codigo = " . $_POST['dd_operadora'];
                        $rs_operadoras = SQLexecuteQuery($sql);
                        //verifica se retornou o registro 
                        if ($rs_operadoras_row = pg_fetch_array($rs_operadoras)) {
                                $obi_bic_code = $rs_operadoras_row['obi_bic_code'];
                                $obi_banco_nome = $rs_operadoras_row['obi_banco_nome'];
                                $obi_numero_conta = $rs_operadoras_row['obi_numero_conta'];
                        } else {
                                $obi_bic_code = null;
                                $obi_banco_nome = null;
                                $obi_numero_conta = null;
                                $msg .= "Erro ao consultar informa&ccedil;&otilde;es do Banco Intermediario da Operadora. ($sql)<br>";
                        }
                }
        }
}//end if dd_operadora n�o est� vazio
//echo $msg."<br>";
//instancia a classe.. P=Retrato, mm =tipo de medida utilizada no casso milimetros, tipo de folha =A4
$pdf = new FPDF("P", "mm", "A4");
//define a fonte a ser usada
$pdf->SetFont('arial', '', 10);
//define o titulo
$pdf->SetTitle("Remittance");
//assunto
$pdf->SetSubject("Remittance Report");
// posicao vertical no caso -1.. e o limite da margem
$pdf->SetY("-1");
$titulo = "Remittance";
//escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
$pdf->Cell(0, 5, $titulo, 0, 0, 'L');
$pdf->Cell(0, 5, 'http://www.e-prepag.com.br', 0, 1, 'R', false, 'javascript:history.go(-1);');
$pdf->Cell(0, 0, '', 1, 1, 'L');
$pdf->Ln(12);

//setando a cor de fundo
$pdf->SetFillColor(255, 255, 255);

//hora do conteudo do artigo
$pdf->SetFont('arial', '', 8);

if (!in_array($_POST['tax'], $alicota_epp_adm)) {
        $novo = utf8_decode("E-PREPAG Pagamentos Eletrônicos Ltda
        Rua Dep Lacerda Franco, 300 - cj 26 a 28 - São Paulo - SP - Brasil
        tel 11-3030-9101/ 11-3030-9102
        ");
        //posiciona verticalmente em mm
        $pdf->SetY("31");
        //posiciona horizontalmente em mm
        $pdf->SetX("60");
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(87, 3, $novo, 0, 'C');

        $pdf->SetFont('arial', '', 10);

        //posiciona verticalmente em mm
        $pdf->SetY("63");
        //posiciona horizontalmente em mm
        $pdf->SetX("171");

        //data atual
        $data = date("d/m/Y");
        $conteudo = $data;

        $novo = "REPORT
        " . $data;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(20, 4, $novo, 0, 'R');

        //posiciona verticalmente em mm
        $pdf->SetY("74");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        $pdf->Cell(0, 5, "CLIENT", 0, 0, 'L');

        //posiciona verticalmente em mm
        $pdf->SetY("74");
        //posiciona horizontalmente em mm
        $pdf->SetX("59");

        $novo = $opr_razao . " 
" . $opr_endereco . " " . $opr_numero . " " . $opr_complemento;
        if (!empty($opr_bairro)) {
                $novo .= ", " . $opr_bairro;
        }
        $novo .= "
" . $opr_cidade . " " . $opr_estado . "
" . $opr_pais;

        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(100, 4, $novo, 0, 'L');

        //posiciona verticalmente em mm
        $pdf->SetY("100");
        //posiciona horizontalmente em mm
        $pdf->SetX("8");



        if (strpos($_POST['rperiod'], '-')) {
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(40, 20, 'Period', 1, 1, 'C');
        } else {
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(40, 20, 'Month', 1, 1, 'C');
        }

        //posiciona verticalmente em mm
        $pdf->SetY("100");
        //posiciona horizontalmente em mm
        $pdf->SetX("48");
        $pdf->Cell(40, 20, 'Gross wired Value', 1, 1, 'C');

        if ($_POST['dd_operadora'] != 16) {
                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("88");
                $pdf->Cell(35, 20, 'Tax ' . $_POST['tax'] . '%', 1, 1, 'C');

                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("123");
                $pdf->Cell(40, 20, 'Facilitation Fee', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("163");
                $pdf->Cell(37, 20, 'Net wired Value', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("8");
        } else {

                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("88");
                $pdf->Cell(35, 20, 'Facilitation Fee', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("123");
                $pdf->Cell(40, 20, 'Net wired Value', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("8");
        }


        if (strpos($_POST['rperiod'], '-')) {
                $pdf->Cell(40, 10, '', 1, 1, 'C');
                $rperiod_aux = trim(substr($_POST['rperiod'], 0, strpos($_POST['rperiod'], '-')));
                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("8");
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(40, 6, $rperiod_aux, 0, 1, 'C');
                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("8");
                $rperiod_aux = trim(substr($_POST['rperiod'], strpos($_POST['rperiod'], '-') + 1, strlen($_POST['rperiod'])));
                $pdf->Cell(40, 15, $rperiod_aux, 0, 1, 'C');
        } else {
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(40, 10, $_POST['rperiod'], 1, 1, 'C');
        }

        //posiciona verticalmente em mm
        $pdf->SetY("120");
        //posiciona horizontalmente em mm
        $pdf->SetX("48");
        $pdf->Cell(40, 10, 'R$ ' . $_POST['grosswired'], 1, 1, 'C');

        if ($_POST['dd_operadora'] != 16) {
                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("88");
                $pdf->Cell(35, 10, 'R$ ' . $_POST['witholding'], 1, 1, 'C');

                $valorbruto = str_replace(".", "", $_POST['grosswired']);
                $valorbruto = str_replace(",", ".", $valorbruto);


                $valortaxa = str_replace(".", "", $_POST['witholding']);
                $valortaxa = str_replace(",", ".", $valortaxa);

                $valorliquido = str_replace(".", "", $_POST['netwired']);
                $valorliquido = str_replace(",", ".", $valorliquido);


                $facilitation_fee = ((float) $valorbruto - (float) $valorliquido) - (float) $valortaxa;

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("123");
                $pdf->Cell(40, 10, 'R$ ' . number_format($facilitation_fee, "2", ",", "."), 1, 1, 'C');

                //Negrito
                $pdf->SetFont('arial', 'B', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("163");
                $pdf->Cell(37, 10, 'R$ ' . $_POST['netwired'], 1, 1, 'C');

        }else{

                $valorbruto = str_replace(".", "", $_POST['grosswired']);
                $valorbruto = str_replace(",", ".", $valorbruto);


                $valortaxa = str_replace(".", "", $_POST['witholding']);
                $valortaxa = str_replace(",", ".", $valortaxa);

                $valorliquido = str_replace(".", "", $_POST['netwired']);
                $valorliquido = str_replace(",", ".", $valorliquido);


                $facilitation_fee = ((float) $valorbruto - (float) $valorliquido) - (float) $valortaxa;

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("88");
                $pdf->Cell(35, 10, 'R$ ' . number_format($facilitation_fee, "2", ",", "."), 1, 1, 'C');

                //Negrito
                $pdf->SetFont('arial', 'B', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("123");
                $pdf->Cell(40, 10, 'R$ ' . $_POST['netwired'], 1, 1, 'C');
        }

        //posiciona verticalmente em mm
        $pdf->SetY("143");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        $pdf->Cell(0, 5, 'BANK INFORMATION:', 0, 0, 'L');

        $pdf->SetFont('arial', '', 10);

        //posiciona verticalmente em mm
        $pdf->SetY("148");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        if ($_POST['dd_operadora'] == 16) {
                $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank City:
Currency:";
        } else {
                $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank Address:
Bank City:
Bank Tel. Number:
Currency:
IBAN:
SWIFT / BIC Code:";
        }
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(35, 4, $novo, 0, 'L');

        //posiciona verticalmente em mm
        $pdf->SetY("148");
        //posiciona horizontalmente em mm
        $pdf->SetX("60");

        $novo = $opr_razao . "
" . $opr_numero_conta . " 
" . $opr_tipo_conta . "
" . $opr_numero_roteamento . "
" . $opr_banco_nome . "
" . $opr_banco_endereco . "
" . $opr_banco_cidade . "
" . $opr_banco_telefone . "
" . $opr_moeda_corrente . "
" . $opr_iban . "
" . $opr_bic_code;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(125, 4, $novo, 0, 'L');

        if (!empty($opr_banco_intermediario)) {

                //Negrito
                $pdf->SetFont('arial', 'B', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("195");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $pdf->Cell(0, 5, 'CORRESPONDENT BANK:', 0, 0, 'L');

                $pdf->SetFont('arial', '', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("200");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $novo = "SWIFT / BIC Code:
Bank Name:
Account Number:";
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(35, 4, $novo, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("200");
                //posiciona horizontalmente em mm
                $pdf->SetX("60");

                $novo = $obi_bic_code . "
" . $obi_banco_nome . "
" . $obi_numero_conta;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(125, 4, $novo, 0, 'L');

        }

        //        //posiciona verticalmente em mm
//        $pdf->SetY("229");
//        //posiciona horizontalmente em mm
//        $pdf->SetX("25");
//        $pdf->Cell(162, 13,'', 1, 3, 'C');

        $pdf->SetFont('arial', '', 8);

        //posiciona verticalmente em mm
        $pdf->SetY("229");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        $novo = "This report contains net amount to be wired, according to service agreement number " . $opr_numero_contrato . ", signed by " . $opr_razao . " and E_PREPAG. The amount is related to credits sold by " . $opr_razao . " to its game users in Brazil, collected by E-PREPAG on behalf of " . $opr_razao;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(162, 4, $novo, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("250");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        $novo_traduzido = utf8_decode("Este relatório contém o valor líquido a ser repassado, de acordo com o contrato " . $opr_numero_contrato . ", assinado por " . $opr_razao . " e E_PREPAG. O valor está relacionado a créditos vendidos por " . $opr_razao . " aos usuários do seu(s) game(s) no Brasil e arrecadado pela E-PREPAG em nome de ") . $opr_razao;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(162, 4, $novo_traduzido, 1, 'C');

        //endereco da imagem,posicao X(horizontal),posicao Y(vertical), tamanho altura, tamanho largura
        $pdf->Image(RAIZ_DO_PROJETO . "public_html/sys/imagens/epp_logo.gif", 85, 20, 37, 10);
        /*******definindo o rodap�*************************/
        //posiciona verticalmente 270mm
        $pdf->SetY("270");
        $texto = "by E-PREPAG";

        //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
        $pdf->Cell(0, 0, '', 1, 1, 'L');
        //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
        $pdf->Cell(0, 5, $texto, 0, 0, 'L');
        //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
        $pdf->Cell(0, 5, $conteudo, 0, 1, 'R');
} //end if(!in_array($_POST['tax'], $alicota_epp_adm)) 
else {

        //Tratando valores para ser utilizados nos campos da remessa
        $total_sem_iof = (str_replace(',', '.', str_replace('.', '', $_POST['grosswired'])) / (1 + $_POST['tax'] / 100));
        $total_iof = $total_sem_iof * $_POST['tax'] / 100;
        $total_comissao_sem_iof = str_replace(',', '.', str_replace('.', '', $_POST['grosswired'])) - str_replace(',', '.', str_replace('.', '', $_POST['netwired'])) - $total_iof;

        $novo = utf8_decode("E-PREPAG ADMINISTRADORA DE CARTÕES LTDA
        CNPJ 19.037.276/0001-72
        Rua Dep Lacerda Franco, 300 - cj 26A
        São Paulo - SP - Brasil
        tel 11-3030-9101/ 11-3030-9102
        ");
        //posiciona verticalmente em mm
        $pdf->SetY("31");
        //posiciona horizontalmente em mm
        $pdf->SetX("60");
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(87, 3, $novo, 0, 'C');

        $pdf->SetFont('arial', '', 10);

        //posiciona verticalmente em mm
        $pdf->SetY("63");
        //posiciona horizontalmente em mm
        $pdf->SetX("171");

        //data atual
        $data = date("d/m/Y");
        $conteudo = $data;

        $novo = "REPORT
        " . $data;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(20, 4, $novo, 0, 'R');

        //posiciona verticalmente em mm
        $pdf->SetY("74");
        //posiciona horizontalmente em mm
        $pdf->SetX("15");
        $pdf->Cell(0, 4, "MERCHANT/LOJISTA", 0, 0, 'L');

        //posiciona verticalmente em mm
        $pdf->SetY("74");
        //posiciona horizontalmente em mm
        $pdf->SetX("59");

        $novo = $opr_razao . " 
" . $opr_endereco . " " . $opr_numero . " " . $opr_complemento;
        if (!empty($opr_bairro)) {
                $novo .= ", " . $opr_bairro;
        }
        $novo .= "
" . $opr_cidade . " " . $opr_estado . "
" . $opr_pais;

        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(100, 4, $novo, 0, 'L');

        //posiciona verticalmente em mm
        $pdf->SetY("100");
        //posiciona horizontalmente em mm
        $pdf->SetX("10");

        if (strpos($_POST['rperiod'], '-')) {
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(30, 20, 'PERIOD', 1, 1, 'C');
        } else {
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(30, 20, 'MONTH', 1, 1, 'C');
        }

        //posiciona verticalmente em mm
        $pdf->SetY("100");
        //posiciona horizontalmente em mm
        $pdf->SetX("40");
        $pdf->Cell(30, 20, 'TRANSACTION', 1, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("100");
        //posiciona horizontalmente em mm
        $pdf->SetX("70");
        $pdf->Cell(30, 20, 'IOF/TAX ' . $_POST['tax'] . '%', 1, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("100");
        //posiciona horizontalmente em mm
        $pdf->SetX("100");
        $pdf->Cell(50, 20, 'CARD MANAGEMENT FEE', 1, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("100");
        //posiciona horizontalmente em mm
        $pdf->SetX("150");
        $pdf->Cell(50, 20, 'NET AMOUNT TO BE WIRED', 1, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("120");
        //posiciona horizontalmente em mm
        $pdf->SetX("10");

        if (strpos($_POST['rperiod'], '-')) {
                $pdf->Cell(30, 10, '', 1, 1, 'C');
                $rperiod_aux = trim(substr($_POST['rperiod'], 0, strpos($_POST['rperiod'], '-')));
                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("10");
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(30, 6, $rperiod_aux, 0, 1, 'C');
                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("10");
                $rperiod_aux = trim(substr($_POST['rperiod'], strpos($_POST['rperiod'], '-') + 1, strlen($_POST['rperiod'])));
                $pdf->Cell(30, 15, $rperiod_aux, 0, 1, 'C');
        } else {
                //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                $pdf->Cell(30, 10, $_POST['rperiod'], 1, 1, 'C');
        }

        //posiciona verticalmente em mm
        $pdf->SetY("120");
        //posiciona horizontalmente em mm
        $pdf->SetX("40");
        $pdf->Cell(30, 10, 'R$ ' . $_POST['grosswired'], 1, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("120");
        //posiciona horizontalmente em mm
        $pdf->SetX("70");
        $pdf->Cell(30, 10, 'R$ ' . number_format($total_iof, 2, ',', '.'), 1, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("120");
        //posiciona horizontalmente em mm
        $pdf->SetX("100");
        $pdf->Cell(50, 10, 'R$ ' . number_format($total_comissao_sem_iof, 2, ',', '.'), 1, 1, 'C');

        //Negrito
        $pdf->SetFont('arial', 'B', 10);

        //posiciona verticalmente em mm
        $pdf->SetY("120");
        //posiciona horizontalmente em mm
        $pdf->SetX("150");
        $pdf->Cell(50, 10, 'R$ ' . $_POST['netwired'], 1, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("143");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        $pdf->Cell(0, 5, 'BANK INFORMATION:', 0, 0, 'L');

        $pdf->SetFont('arial', '', 10);

        //posiciona verticalmente em mm
        $pdf->SetY("148");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        if ($_POST['dd_operadora'] == 16) {
                $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank City:
Currency:";
        } else {
                $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank Address:
Bank City:
Bank Tel. Number:
Currency:
IBAN:
SWIFT / BIC Code:";
        }
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(35, 4, $novo, 0, 'L');

        //posiciona verticalmente em mm
        $pdf->SetY("148");
        //posiciona horizontalmente em mm
        $pdf->SetX("60");

        $novo = $opr_razao . "
" . $opr_numero_conta . " 
" . $opr_tipo_conta . "
" . $opr_numero_roteamento . "
" . $opr_banco_nome . "
" . $opr_banco_endereco . "
" . $opr_banco_cidade . "
" . $opr_banco_telefone . "
" . $opr_moeda_corrente . "
" . $opr_iban . "
" . $opr_bic_code;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(125, 4, $novo, 0, 'L');

        if (!empty($opr_banco_intermediario)) {

                //Negrito
                $pdf->SetFont('arial', 'B', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("195");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $pdf->Cell(0, 5, 'CORRESPONDENT BANK:', 0, 0, 'L');

                $pdf->SetFont('arial', '', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("200");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $novo = "SWIFT / BIC Code:
Bank Name:
Account Number:";
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(35, 4, $novo, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("200");
                //posiciona horizontalmente em mm
                $pdf->SetX("60");

                $novo = $obi_bic_code . "
" . $obi_banco_nome . "
" . $obi_numero_conta;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(125, 4, $novo, 0, 'L');

        }

        $pdf->SetFont('arial', '', 8);

        //posiciona verticalmente em mm
        $pdf->SetY("229");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        $novo = "This report contains net amount to be wired, according to service agreement number " . $opr_numero_contrato . ", signed by " . $opr_razao . " and E-PREPAG. The amount is related to credits sold by " . $opr_razao . " to its game users in Brazil, collected by E-PREPAG on behalf of " . $opr_razao;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(162, 4, $novo, 1, 'C');

        //posiciona verticalmente em mm
        $pdf->SetY("250");
        //posiciona horizontalmente em mm
        $pdf->SetX("25");
        $novo_traduzido = utf8_decode("Este relatório contém o valor líquido a ser repassado, de acordo com o contrato " . $opr_numero_contrato . ", assinado por " . $opr_razao . " e E_PREPAG. O valor está relacionado a créditos vendidos por " . $opr_razao . " aos usuários do seu(s) game(s) no Brasil e arrecadado pela E-PREPAG em nome de ") . $opr_razao;
        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
        $pdf->MultiCell(162, 4, $novo_traduzido, 1, 'C');

        //endereco da imagem,posicao X(horizontal),posicao Y(vertical), tamanho altura, tamanho largura
        $pdf->Image(RAIZ_DO_PROJETO . "public_html/sys/imagens/logo-epp-hz.gif", 85, 20, 37, 10);
        /*******definindo o rodap�*************************/
        //posiciona verticalmente 270mm
        $pdf->SetY("270");
        $texto = "by E-PREPAG";

        //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
        $pdf->Cell(0, 0, '', 1, 1, 'L');
        //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
        $pdf->Cell(0, 5, $texto, 0, 0, 'L');
        //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
        $pdf->Cell(0, 5, $conteudo, 0, 1, 'R');
} //end else do if(!in_array($_POST['tax'], $alicota_epp_adm)) 

//Segunda folha para os cart�es
if (intval($_POST['grosswiredcard']) != 0 && intval($_POST['witholdingcard']) != 0 && intval($_POST['netwiredcard']) != 0) {

        //$pdf->SetDisplayMode('fullwidth','two');
        $pdf->SetDisplayMode(75, 'two');

        //define a fonte a ser usada
        $pdf->SetFont('arial', '', 10);

        //define o titulo
        $pdf->SetTitle("Remittance");
        //assunto
        $pdf->SetSubject("Remittance Report");
        // posicao vertical no caso -1.. e o limite da margem
        $pdf->SetY("-1");
        $titulo = "Remittance";
        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
        $pdf->Cell(0, 5, $titulo, 0, 0, 'L');
        $pdf->Cell(0, 5, 'http://www.e-prepag.com.br', 0, 1, 'R', false, 'javascript:history.go(-1);');
        $pdf->Cell(0, 0, '', 1, 1, 'L');
        $pdf->Ln(12);

        //setando a cor de fundo
        $pdf->SetFillColor(255, 255, 255);

        //hora do conteudo do artigo
        $pdf->SetFont('arial', '', 8);

        if (!in_array($_POST['tax'], $alicota_epp_adm)) {
                $novo = utf8_decode("E-PREPAG Pagamentos Eletrônicos Ltda
                Rua Dep Lacerda Franco, 300 - cj 26 a 28 - São Paulo - SP - Brasil
                tel 11-3030-9101/ 11-3030-9102
                ");
                //posiciona verticalmente em mm
                $pdf->SetY("31");
                //posiciona horizontalmente em mm
                $pdf->SetX("60");
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(87, 3, $novo, 0, 'C');

                $pdf->SetFont('arial', '', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("63");
                //posiciona horizontalmente em mm
                $pdf->SetX("171");

                //data atual
                $data = date("d/m/Y");
                $conteudo = $data;

                $novo = "REPORT
        " . $data;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(20, 4, $novo, 0, 'R');

                //posiciona verticalmente em mm
                $pdf->SetY("74");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $pdf->Cell(0, 5, "CLIENT", 0, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("74");
                //posiciona horizontalmente em mm
                $pdf->SetX("59");

                $novo = $opr_razao . " 
" . $opr_endereco . " " . $opr_numero . " " . $opr_complemento;
                if (!empty($opr_bairro)) {
                        $novo .= ", " . $opr_bairro;
                }
                $novo .= "
" . $opr_cidade . " " . $opr_estado . "
" . $opr_pais;

                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(100, 4, $novo, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("33");

                if (strpos($_POST['rperiod'], '-')) {
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(33, 20, 'Period', 1, 1, 'C');
                } else {
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(33, 20, 'Month', 1, 1, 'C');
                }

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("66");
                $pdf->Cell(33, 20, 'Gross wired Value', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("99");
                $pdf->Cell(45, 20, 'Witholding Tax/IRRF ' . $_POST['tax'] . '%', 1, 1, 'C');

                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("123");
                $pdf->Cell(40, 20, 'Facilitation Fee', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("144");
                $pdf->Cell(40, 20, 'Net wired Value', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("33");

                if (strpos($_POST['rperiod'], '-')) {
                        $pdf->Cell(33, 10, '', 1, 1, 'C');
                        $rperiod_aux = trim(substr($_POST['rperiod'], 0, strpos($_POST['rperiod'], '-')));
                        //posiciona verticalmente em mm
                        $pdf->SetY("120");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("33");
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(33, 6, $rperiod_aux, 0, 1, 'C');
                        //posiciona verticalmente em mm
                        $pdf->SetY("120");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("33");
                        $rperiod_aux = trim(substr($_POST['rperiod'], strpos($_POST['rperiod'], '-') + 1, strlen($_POST['rperiod'])));
                        $pdf->Cell(33, 15, $rperiod_aux, 0, 1, 'C');
                } else {
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(33, 10, $_POST['rperiod'], 1, 1, 'C');
                }

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("66");
                $pdf->Cell(33, 10, 'R$ ' . $_POST['grosswiredcard'], 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("99");
                $pdf->Cell(45, 10, 'R$ ' . $_POST['witholdingcard'], 1, 1, 'C');

                //Negrito
                $pdf->SetFont('arial', 'B', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("144");
                $pdf->Cell(40, 10, 'R$ ' . $_POST['netwiredcard'], 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("143");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $pdf->Cell(0, 5, 'BANK INFORMATION:', 0, 0, 'L');

                $pdf->SetFont('arial', '', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("148");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                if ($_POST['dd_operadora'] == 16) {
                        $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank City:
Currency:";
                } else {
                        $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank Address:
Bank City:
Bank Tel. Number:
Currency:
IBAN:
SWIFT / BIC Code:";
                }
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(35, 4, $novo, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("148");
                //posiciona horizontalmente em mm
                $pdf->SetX("60");

                $novo = $opr_razao . "
" . $opr_numero_conta . " 
" . $opr_tipo_conta . "
" . $opr_numero_roteamento . "
" . $opr_banco_nome . "
" . $opr_banco_endereco . "
" . $opr_banco_cidade . "
" . $opr_banco_telefone . "
" . $opr_moeda_corrente . "
" . $opr_iban . "
" . $opr_bic_code;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(125, 4, $novo, 0, 'L');

                if (!empty($opr_banco_intermediario)) {

                        //Negrito
                        $pdf->SetFont('arial', 'B', 10);

                        //posiciona verticalmente em mm
                        $pdf->SetY("195");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("25");
                        $pdf->Cell(0, 5, 'CORRESPONDENT BANK:', 0, 0, 'L');

                        $pdf->SetFont('arial', '', 10);

                        //posiciona verticalmente em mm
                        $pdf->SetY("200");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("25");
                        $novo = "SWIFT / BIC Code:
Bank Name:
Account Number:";
                        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                        $pdf->MultiCell(35, 4, $novo, 0, 'L');

                        //posiciona verticalmente em mm
                        $pdf->SetY("200");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("60");

                        $novo = $obi_bic_code . "
" . $obi_banco_nome . "
" . $obi_numero_conta;
                        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                        $pdf->MultiCell(125, 4, $novo, 0, 'L');

                }

                $pdf->SetFont('arial', '', 8);

                //posiciona verticalmente em mm
                $pdf->SetY("229");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $novo = "This report contains net amount to be wired, according to service agreement number " . $opr_numero_contrato . ", signed by " . $opr_razao . " and E_PREPAG. The amount is related to credits sold by " . $opr_razao . " to its game users in Brazil, collected by E-PREPAG on behalf of " . $opr_razao;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(162, 4, $novo, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("250");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $novo_traduzido = utf8_decode("Este relatório contém o valor líquido a ser repassado, de acordo com o contrato " . $opr_numero_contrato . ", assinado por " . $opr_razao . " e E_PREPAG. O valor está relacionado a créditos vendidos por " . $opr_razao . " aos usuários do seu(s) game(s) no Brasil e arrecadado pela E-PREPAG em nome de ") . $opr_razao;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(162, 4, $novo_traduzido, 1, 'C');

                //endereco da imagem,posicao X(horizontal),posicao Y(vertical), tamanho altura, tamanho largura
                $pdf->Image(RAIZ_DO_PROJETO . "public_html/sys/imagens/logo-epp-hz.gif", 85, 20, 37, 10);
                /*******definindo o rodap�*************************/
                //posiciona verticalmente 270mm
                $pdf->SetY("270");
                $texto = "by E-PREPAG";

                //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
                $pdf->Cell(0, 0, '', 1, 1, 'L');
                //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
                $pdf->Cell(0, 5, $texto, 0, 0, 'L');
                //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
                $pdf->Cell(0, 5, $conteudo, 0, 1, 'R');

                $pdf->Close();

        }//end if(!in_array($_POST['tax'], $alicota_epp_adm)) 
        else {

                //Tratando valores para ser utilizados nos campos da remessa
                $total_sem_iof_card = (str_replace(',', '.', str_replace('.', '', $_POST['grosswiredcard'])) / (1 + $_POST['tax'] / 100));
                $total_iof_card = $total_sem_iof_card * $_POST['tax'] / 100;
                $total_comissao_card_sem_iof = str_replace(',', '.', str_replace('.', '', $_POST['grosswiredcard'])) - str_replace(',', '.', str_replace('.', '', $_POST['netwiredcard'])) - $total_iof_card;

                $novo = utf8_decode("E-PREPAG ADMINISTRADORA DE CARTÕES LTDA
            CNPJ 19.037.276/0001-72
            Rua Dep Lacerda Franco, 300 - cj 26A
            São Paulo - SP - Brasil
            tel 11-3030-9101/ 11-3030-9102
            ");
                //posiciona verticalmente em mm
                $pdf->SetY("31");
                //posiciona horizontalmente em mm
                $pdf->SetX("60");
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(87, 3, $novo, 0, 'C');

                $pdf->SetFont('arial', '', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("63");
                //posiciona horizontalmente em mm
                $pdf->SetX("171");

                //data atual
                $data = date("d/m/Y");
                $conteudo = $data;

                $novo = "REPORT
            " . $data;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(20, 4, $novo, 0, 'R');

                //posiciona verticalmente em mm
                $pdf->SetY("74");
                //posiciona horizontalmente em mm
                $pdf->SetX("15");
                $pdf->Cell(0, 4, "MERCHANT/LOJISTA", 0, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("74");
                //posiciona horizontalmente em mm
                $pdf->SetX("59");

                $novo = $opr_razao . " 
" . $opr_endereco . " " . $opr_numero . " " . $opr_complemento;
                if (!empty($opr_bairro)) {
                        $novo .= ", " . $opr_bairro;
                }
                $novo .= "
" . $opr_cidade . " " . $opr_estado . "
" . $opr_pais;

                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(100, 4, $novo, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("10");

                if (strpos($_POST['rperiod'], '-')) {
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(30, 20, 'PERIOD', 1, 1, 'C');
                } else {
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(30, 20, 'MONTH', 1, 1, 'C');
                }

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("40");
                $pdf->Cell(30, 20, 'TRANSACTION', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("70");
                $pdf->Cell(30, 20, 'IOF/TAX ' . $_POST['tax'] . '%', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("100");
                $pdf->Cell(50, 20, 'CARD MANAGEMENT FEE', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("100");
                //posiciona horizontalmente em mm
                $pdf->SetX("150");
                $pdf->Cell(50, 20, 'NET AMOUNT TO BE WIRED', 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("10");

                if (strpos($_POST['rperiod'], '-')) {
                        $pdf->Cell(30, 10, '', 1, 1, 'C');
                        $rperiod_aux = trim(substr($_POST['rperiod'], 0, strpos($_POST['rperiod'], '-')));
                        //posiciona verticalmente em mm
                        $pdf->SetY("120");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("10");
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(30, 6, $rperiod_aux, 0, 1, 'C');
                        //posiciona verticalmente em mm
                        $pdf->SetY("120");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("10");
                        $rperiod_aux = trim(substr($_POST['rperiod'], strpos($_POST['rperiod'], '-') + 1, strlen($_POST['rperiod'])));
                        $pdf->Cell(30, 15, $rperiod_aux, 0, 1, 'C');
                } else {
                        //escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
                        $pdf->Cell(30, 10, $_POST['rperiod'], 1, 1, 'C');
                }

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("40");
                $pdf->Cell(30, 10, 'R$ ' . $_POST['grosswiredcard'], 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("70");
                $pdf->Cell(30, 10, 'R$ ' . number_format($total_iof_card, 2, ',', '.'), 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("100");
                $pdf->Cell(50, 10, 'R$ ' . number_format($total_comissao_card_sem_iof, 2, ',', '.'), 1, 1, 'C');

                //Negrito
                $pdf->SetFont('arial', 'B', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("120");
                //posiciona horizontalmente em mm
                $pdf->SetX("150");
                $pdf->Cell(50, 10, 'R$ ' . $_POST['netwiredcard'], 1, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("143");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $pdf->Cell(0, 5, 'BANK INFORMATION:', 0, 0, 'L');

                $pdf->SetFont('arial', '', 10);

                //posiciona verticalmente em mm
                $pdf->SetY("148");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                if ($_POST['dd_operadora'] == 16) {
                        $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank City:
Currency:";
                } else {
                        $novo = "Account Owner:
Account Number:
Account Type:
Routing Number:
Bank Name:
Bank Address:
Bank City:
Bank Tel. Number:
Currency:
IBAN:
SWIFT / BIC Code:";
                }
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(35, 4, $novo, 0, 'L');

                //posiciona verticalmente em mm
                $pdf->SetY("148");
                //posiciona horizontalmente em mm
                $pdf->SetX("60");

                $novo = $opr_razao . "
" . $opr_numero_conta . " 
" . $opr_tipo_conta . "
" . $opr_numero_roteamento . "
" . $opr_banco_nome . "
" . $opr_banco_endereco . "
" . $opr_banco_cidade . "
" . $opr_banco_telefone . "
" . $opr_moeda_corrente . "
" . $opr_iban . "
" . $opr_bic_code;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(125, 4, $novo, 0, 'L');

                if (!empty($opr_banco_intermediario)) {

                        //Negrito
                        $pdf->SetFont('arial', 'B', 10);

                        //posiciona verticalmente em mm
                        $pdf->SetY("195");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("25");
                        $pdf->Cell(0, 5, 'CORRESPONDENT BANK:', 0, 0, 'L');

                        $pdf->SetFont('arial', '', 10);

                        //posiciona verticalmente em mm
                        $pdf->SetY("200");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("25");
                        $novo = "SWIFT / BIC Code:
Bank Name:
Account Number:";
                        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                        $pdf->MultiCell(35, 4, $novo, 0, 'L');

                        //posiciona verticalmente em mm
                        $pdf->SetY("200");
                        //posiciona horizontalmente em mm
                        $pdf->SetX("60");

                        $novo = $obi_bic_code . "
" . $obi_banco_nome . "
" . $obi_numero_conta;
                        //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                        $pdf->MultiCell(125, 4, $novo, 0, 'L');

                }

                $pdf->SetFont('arial', '', 8);

                //posiciona verticalmente em mm
                $pdf->SetY("229");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $novo = "This report contains net amount to be wired, according to service agreement number " . $opr_numero_contrato . ", signed by " . $opr_razao . " and E-PREPAG. The amount is related to credits sold by " . $opr_razao . " to its game users in Brazil, collected by E-PREPAG on behalf of " . $opr_razao;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(162, 4, $novo, 1, 'C');

                //posiciona verticalmente em mm
                $pdf->SetY("250");
                //posiciona horizontalmente em mm
                $pdf->SetX("25");
                $novo_traduzido = utf8_encode("Este relatório contém o valor líquido a ser repassado, de acordo com o contrato " . $opr_numero_contrato . ", assinado por " . $opr_razao . " e E_PREPAG. O valor está relacionado a créditos vendidos por " . $opr_razao . " aos usuarios do seu(s) game(s) no Brasil e arrecadado pela E-PREPAG em nome de ") . $opr_razao;
                //escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
                $pdf->MultiCell(162, 4, $novo_traduzido, 1, 'C');

                //endereco da imagem,posicao X(horizontal),posicao Y(vertical), tamanho altura, tamanho largura
                $pdf->Image(RAIZ_DO_PROJETO . "public_html/sys/imagens/logo-epp-hz.gif", 85, 20, 37, 10);
                /*******definindo o rodap�*************************/
                //posiciona verticalmente 270mm
                $pdf->SetY("270");
                $texto = "by E-PREPAG";

                //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
                $pdf->Cell(0, 0, '', 1, 1, 'L');
                //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
                $pdf->Cell(0, 5, $texto, 0, 0, 'L');
                //imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
                $pdf->Cell(0, 5, $conteudo, 0, 1, 'R');
        } //end else do if(!in_array($_POST['tax'], $alicota_epp_adm)) 

}//end if(!empty($_POST['grosswired']) && !empty($_POST['witholding']) && !empty($_POST['netwired']))
else {
        $pdf->SetDisplayMode('fullpage');
}//end else

//imprime a saida do arquivo..
//$pdf->Output();
$pdf->Output("arquivo", "I");
?>