<?php 
header ('Content-type: text/html; charset=iso-8859-1');

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "includes/fpdf/fpdf.php";
require_once $raiz_do_projeto . "includes/main.php";

//=========  Mês/Ano considerado no Elaboração dos Arquivos
$currentmonth = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
$mesAno = date('m/Y',$currentmonth);

//forçando mês 06 de 2019
//$mesAno = '06/2019';

// Variaveis do PDF
$cnpjEPP = '19.037.276/0001-72'; // CNPJ da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$nomeEmissor = 'E-PREPAG ADMINISTRADORA DE CARTOES LTDA'; // Nome da instituição
$inscricaoEstatual = ' ';        // Número da Inscrição Estadual
$municipioEmissor = 'São Paulo'; // Município da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$ufEmissor = 'SP';               // Estado da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$faxEmissor = '1130309101';      // Fax da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$logradouroEmissor = 'Rua Deputado Lacerda Franco';  // Logradouro da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$numeroEmissor = '300';          // Número do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$complementoEmissor = 'Conjunto 26A';  // Complemento do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$bairroEmissor = 'Pinheiros';    // Bairro do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$cepEmissor = '05418000';        // CEP do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$CCM = "4.921.123-4";            // Inscrição Munícipal

//Dados Representante Legal
$nomeRepresentanteLegal = "Glaucia da Costa Gregio";
$cpfRepresentanteLegal = "168.062.898-43";
$rgRepresentanteLegal = "22.612.238-4";
$orgaoEmissorRepresentanteLegal = "SSP/SP";

$webstring = "http://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];

$msg = "";

//instancia a classe.. P=Retrato, mm =tipo de medida utilizada no casso milimetros, tipo de folha =A4
$pdf= new FPDF("P","mm","A4");
//define a fonte a ser usada
$pdf->SetFont('arial','',10);
//define o titulo
$pdf->SetTitle("Compliance");
//assunto
$pdf->SetSubject("Compliance Report");
// posicao vertical no caso -1.. e o limite da margem
$pdf->SetY("-1");
$titulo="DOC - Declaração de Operações de Cartões de Crédito ou Débito";

//endereco da imagem,posicao X(horizontal),posicao Y(vertical), tamanho altura, tamanho largura
$pdf->Cell(0,0,'',0,1,'L');
$pdf->Image($GLOBALS['raiz_do_projeto'] . "backoffice/images/background_anexo3.jpg", 10,10,190,0);

//escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
$pdf->Cell(0,-7,$titulo,0,0,'L');
$pdf->Cell(0,-7,'http://www.e-prepag.com.br',0,1,'R',false,'javascript:history.go(-1);');
$pdf->Ln(12);

//setando a cor de fundo
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFont('arial','',10);

//posiciona verticalmente em mm
$pdf->SetY("73");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$nomeEmissor,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("84");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$logradouroEmissor.", ".$numeroEmissor." - ".$complementoEmissor.", ".$bairroEmissor."  ".$municipioEmissor." - ".$ufEmissor."  CEP: ".$cepEmissor ,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("97");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$cnpjEPP,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("97");
//posiciona horizontalmente em mm
$pdf->SetX("120");
$pdf->Cell(0,4,$inscricaoEstatual,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("111");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$CCM,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("111");
//posiciona horizontalmente em mm
$pdf->SetX("120");
$pdf->Cell(0,4,$mesAno,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("132");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$nomeRepresentanteLegal,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("147");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$cpfRepresentanteLegal,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("147");
//posiciona horizontalmente em mm
$pdf->SetX("95");
$pdf->Cell(0,4,$rgRepresentanteLegal,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("147");
//posiciona horizontalmente em mm
$pdf->SetX("150");
$pdf->Cell(0,4,$orgaoEmissorRepresentanteLegal,0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("189");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$_GET['nomeArq'],0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("200");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,remote_filesize("/bacen/".date('Ymd')."/".$_GET['nomeArq'])." bytes",0,0,'L');


//posiciona verticalmente em mm
$pdf->SetY("211");
//posiciona horizontalmente em mm
$pdf->SetX("15");
$pdf->Cell(0,4,$municipioEmissor." - ".$ufEmissor."  ".date('d/m/Y'),0,0,'L');



/*******definindo o rodapé*************************/
//posiciona verticalmente 270mm
/*
$pdf->SetY(271);
$texto="by E-PREPAG";

//imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
$pdf->Cell(0,0,'',1,1,'L');
//imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
$pdf->Cell(0,5,$texto,0,0,'L');
//imprime uma celula... largura,altura, texto,borda,quebra de linha, alinhamento
$pdf->AliasNbPages();
$pdf->Cell(0,5,$pdf->PageNo().'/{nb}',0,1,'R');
*/
$pdf->SetDisplayMode('fullpage');

//imprime a saida do arquivo..
$pdf->Output("arquivo","I");


// Função que mede o tamanho de um arquivo remoto
function remote_filesize($url) {
    static $regex = '/^Content-Length: *+\K\d++$/im';
    if (!$fp = @fopen($url, 'rb')) {
        return false;
    }
    if (
        isset($http_response_header) &&
        preg_match($regex, implode("\n", $http_response_header), $matches)
    ) {
        return (int)$matches[0];
    }
    return strlen(stream_get_contents($fp));
}//end function remote_filesize()
?>  
