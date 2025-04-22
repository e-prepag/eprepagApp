<?php  
die("Acesso Negado!");
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
ini_set('memory_limit', '-1');

header("Content-Type: text/html; charset=ISO-8859-1",true);

set_time_limit(18000);

list($usec, $sec) = explode(" ", microtime());
$time_start_stats = ((float)$usec + (float)$sec);

require_once "/www/includes/fpdf/fpdf.php";

// Colocar aqui o nome do arquivo a ser processado
$arquivos_a_serem_processado = array (
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_01_12.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_01_17.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_01_23.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_03_11.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_03_18.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_04_13.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_04_20.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_05_17.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_06_09.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_06_17.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_07_13.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_07_20.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_08_11.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_08_15.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_08_20.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_09_20.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_10_12.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_10_17.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_10_18.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_11_08.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_11_14.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_11_19.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_11_20.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_12_14.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_12_21.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_13_09.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_13_17.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_14_12.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_14_17.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_15_11.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_15_18.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_17_11.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_17_19.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_18_15.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_19_12.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_20_08.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_20_19.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_21_12.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_21_15.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_21_18.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_22_10.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_22_14.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_22_17.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_23_15.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_24_12.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_24_18.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_25_10.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_25_15.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_25_19.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_26_11.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_26_16.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_27_08.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_27_16.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_28_10.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_28_20.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_06_29_16.TXT',
                                      '../arquivos_gerados/csv/logCheckRedeem_2019_07_01_09.TXT');

//ID do produto a ser verificado
$pins = array('18065172471485344745',
            '03111290489581417192',
            '35396432432473791303',
            '08435262179485028148',
            '81532625137405676276',
            '58201657099598628441',
            '60308899992390169054',
            '17279524324819933421',
            '34550498073874919386',
            '96692110298995990903',
            '63707562746851149213',
            '26440718554004909304',
            '27569058689991894014',
            '79490767478891009769',
            '27683082009185094283',
            '48014836462135424698',
            '04733021752764478487',
            '53899077572500342518',
            '34446777391249868640',
            '00574601180946643369',
            '99212393774945971307',
            '58658397275568153541',
            '25793144530416367267',
            '13038961857176497840',
            '79383188440423569212',
            '52423854570404925357',
            '33677123356734818396',
            '00526755175335163801',
            '41008070364888222606',
            '94296267441034922097',
            '13910858032461820073',
            '08355004277754612062',
            '58322504127308701375',
            '47677715842032221151',
            '53721814756345950260',
            '18074777170833633924',
            '95179483047937366451',
            '84457756138494143604',
            '61604565117495664054',
            '50569501843641471125',
            '34267957333901713894',
            '85502694203639160417',
            '03692925501157103017',
            '60409008932610482941',
            '75175687214664846194',
            '08384726781951820485',
            '84542210205321341127',
            '10992807543002792014',
            '12478764152661515833',
            '23350540549565905125',
            '25565069716800190716',
            '63730381444618461699',
            '97162258086951239421',
            '37868501547762325871',
            '39132187462401786882',
            '88644060390711090552',
            '67212250928625484929',
            '19745769762464682439',
            '96456011062599137087',
            '82773309700589771074',
            '22016687452282379650',
            '23596991170816671008',
            '73000250282151698614',
            '39857984210350404150',
            '64729646932851232216',
            '62467319899263487526',
            '51087983715645276645',
            '88019065739815547531',
            '20116241344503135622',
            '37298362642111489377',
            '55372553914556744328',
            '47129200670441068858',
            '12023328843279988757',
            '52591745072670684563',
            '17101721591545212054',
            '31062061949721562193',
            '55878997343466364059',
            '41989681839137048736',
            '88754211588879505678',
            '16285061808249516712',
            '63389369036225061224',
            '85165487366474225331',
            '28266425032668662376',
            '73983678494449284889',
            '72763700098847112732',
            '09125878936386156482',
            '43196037466088711569',
            '91615415020961030300',
            '12038333327616614212',
            '09157672740008158561',
            '48511223626948080441',
            '12812159575809862673',
            '30441011148104307422',
            '55343340383067903411',
            '69493115931870104439',
            '64572264014482526448',
            '91917304903889558664',
            '34450052486506684765',
            '48032924128596929420',
            '43527597254286286315',
            '23922429010452388576',
            '25548929064849337148',
            '87145893504901056957',
            '48100616056550315394',
            '73812269811292468505',
            '07406488083248874538',
            '10935466654753024704',
            '63302373484941557368',
            '93671045790169619023',
            '95355681969081155103',
            '74760113176382994162',
            '40087277675295492544',
            '56904492107654671073',
            '96483241721373956366',
            '45373536448860059602',
            '54154117482859725514',
            '53210999428038876644',
            '22832888602168367536',
            '21471074163035846423',
            '29138098841443285156',
            '53093522679735596309',
            '47383963363826586109',
            '41858000405352346203',
            '57492385713927318576',
            '31954940886870222081',
            '79435736821005456957',
            '11056715974490195976',
            '53035097006337073793',
            '83782532377543952460',
            '91359414607239305523',
            '82313349784799590219',
            '41840196888907358553',
            '87268817456365613029',
            '00566987930845830917',
            '31366681345787820175',
            '95196680209943316754',
            '85293879055198086414',
            '19296851721498817611',
            '60221779900650578941',
            '98002952426122616626',
            '37322703141469707457',
            '47147241246274848241',
            '36108096195293823627',
            '19651165231926301268',
            '03334954868342171057',
            '22622336616011176521',
            '71571978200246913138',
            '47697354207389718061',
            '05062908940121811300',
            '57215677035455319999',
            '43873994858014355819',
            '47529587888177797731',
            '76789616033634065688',
            '50863860663006754657',
            '10120791245756307254',
            '12774532926165685560',
            '29509753785115607826',
            '24834987707150234831',
            '49918402948042245880',
            '86980700917624069180',
            '15639696223279584008',
            '38829568374181398382',
            '50578177791394479711',
            '45958331945314176423',
            '89980207295435720210',
            '62283432952058113412',
            '27640133892114276955',
            '14997163764757504000',
            '20534844735019444435',
            '87814282610404022340',
            '18521309144322642869',
            '37819340262621173207',
            '37221903108885402166',
            '82331947988931195555',
            '41010722880529292951',
            '95941688769682493494',
            '47619489753155503104',
            '67615579065452650397',
            '95111754881395937715',
            '57784907029749270192',
            '20040608152385225586',
            '59524175804674729372',
            '15361027274564331665',
            '59942123655002032416',
            '61932471708533895432',
            '33694980879908117251',
            '40635011868104889886',
            '65217256866877595098',
            '51164799118355435315',
            '58698560383738835326',
            '01143608481831642316',
            '49714962167557130666',
            '73260939980715991242',
            '52554489119436436027',
            '61088666398209948650',
            '73205772980163669696',
            '04884045456153708148',
            '99348881379518847324',
            '02171750107800173872',
            '62897012680141407983',
            '11108800793711423220',
            '41726217280667347374',
            '79826566598715732308',
            '26442610260852718800',
            '15688317145434189059',
            '03820952220375054368',
            '70031478149460456746',
            '08567640978438992357',
            '55078174951471846670',
            '99338507207585716955',
            '90586296412021028233',
            '56416178847581587020',
            '64745880989050996186',
            '35485794219001539352',
            '86350064033315037726',
            '66899280283168257557',
            '71441551130342966246',
            '64322108804993100554',
            '70357399353934056724',
            '24825545873700738389',
            '17258047250145056526',
            '68488941626640642779',
            '92741262720756366091',
            '02505020959438228164',
            '26211649003124244407',
            '07090273922246909035',
            '26979491915612105119',
            '31585937844487204289',
            '02310763998130707307',
            '89504892585373085794',
            '98902894987516500950',
            '50459371976096964289',
            '62428807662392213224',
            '63169331364832840639',
            '18850039872697080671',
            '84658386146694236202',
            '76170654439683852903');

//Arquivo que receberá o resultado do processamento
$arquivo_destino = "../arquivos_gerados/csv/arquivo_PINsGarena.csv";

//Procurar POR:
$search = 'PIN [';
$search2 = '[PIN_CODE] =>';

//Rejeitar QUANDO:
$nao_considerar = "Dominio capturando no if";
$nao_considerar2 = "Post parameters:";

$encontrados = array();
$conteudo_arquivo = "PIN;DATE;CHECK REQUEST;RETURN EPP".PHP_EOL;

$total_bytes_arquivos_log = 0;

$html = "<html>".PHP_EOL;
foreach ($arquivos_a_serem_processado as $chave => $arquivo_a_ser_processado) {
    
    $tamanho_arquivo = filesize($arquivo_a_ser_processado);
    //$html .= str_repeat('-',200)."<br>Size File [".$arquivo_a_ser_processado."] : ".number_format($tamanho_arquivo,0,".",".")." bytes<br>";
    $total_bytes_arquivos_log += $tamanho_arquivo;

    $string = file_get_contents($arquivo_a_ser_processado);

    $vetor_nivel1 = explode("--------------------------------------------------------------------------------",$string);

    //$html .= "Number registers: ". count($vetor_nivel1)."<br>";

    foreach ($vetor_nivel1 as $key => $value) {

        $vetor_nivel2 = explode(PHP_EOL,$value);

        foreach ($vetor_nivel2 as $key2 => $value2) {
            if(strpos($value2, $search) !== false || strpos($value2, $search2) !== false){
                $pin = str_replace($search, "", $value2);
                $pin = str_replace($search2, "", $pin);
                $pin = str_replace(" ", "", $pin);
                $pin = str_replace("\t", "", $pin);
                $pin = str_replace("]", "", $pin);
                if(in_array($pin, $pins)) {
                    $encontrados[$pin][]=$vetor_nivel2;
                }
            }
        }//end foreach nivel 2
     }//end foreach nivel 1
}//end foreach relação de arquivos de LOGs
unset($arquivo_a_ser_processado);

$html .= str_repeat('-',200)."<br>Count PINs Found in LOGs : ".count($encontrados)."<br>";

foreach ($encontrados as $pin_encontrado => $vetor_request) {
    $html .= "<br><br><br><br>".str_repeat('-',200)." <br>PIN [".$pin_encontrado."]<br><br>Registers LOG:<br><br><div style='background-color:lightblue'><font size='2'>".str_repeat('_',40)."<br>"; 
    foreach ($vetor_request as $indice => $each_vetor_request) {
        if(!array_search_partial($nao_considerar, $each_vetor_request) && !array_search_partial($nao_considerar2, $each_vetor_request)) {
            foreach ($each_vetor_request as $sub_indice => $line) {
                if(!empty($line)) {
                    $html .= $line."<br>";
                }
            }
            $html .= str_repeat('_',40)."<br>";
        }
    }
    $html .= "</font></div>";
}

//Grava mensagem no arquivo
if ($handle = fopen($arquivo_destino, 'w+')) {
    fwrite($handle, $conteudo_arquivo);
    fclose($handle);
}

list($usec, $sec) = explode(" ", microtime());
$time_stop_stats = ((float)$usec + (float)$sec);
$html .= "<br><br><br><br><br>Elapsed time: ".number_format($time_stop_stats - $time_start_stats, 2, '.', '.')." seconds.<br>";
$html .= "Number LOGs Files: ".count($arquivos_a_serem_processado)." files.<br>";
$html .= "Size all Files: ".number_format($total_bytes_arquivos_log,0,".",".")." bytes<br><br><br><br><br><br></html>";

//echo $html;

//instancia a classe.. P=Retrato, mm =tipo de medida utilizada no casso milimetros, tipo de folha =A4
$pdf= new FPDF("P","mm","A4");
//define a fonte a ser usada
$pdf->SetFont('arial','',10);
//define o titulo
$pdf->SetTitle("Garena LOGs");
//assunto
$pdf->SetSubject("Garena LOGs Report");
// posicao vertical no caso -1.. e o limite da margem
$pdf->SetY("-1");
$titulo="Registers LOG";
//escreve no pdf largura,altura,conteudo,borda,quebra de linha,alinhamento
$pdf->Cell(0,5,$titulo,0,0,'L');
$pdf->Cell(0,5,'http://www.e-prepag.com.br',0,1,'R',false,'javascript:history.go(-1);');
$pdf->Cell(0,0,'',1,1,'L');
$pdf->Ln(4);

//setando a cor de fundo
$pdf->SetFillColor(255, 255, 255);

//hora do conteudo do artigo
$pdf->SetFont('arial','',8);

//escreve o conteudo de novo.. parametros posicao inicial,altura,conteudo(*texto),borda,quebra de linha,alinhamento
$pdf->MultiCell(0,3,  strip_tags(str_replace("<br>",PHP_EOL,$html)),0,'J');

//imprime a saida do arquivo..
$pdf->Output("arquivo","I");


function array_search_partial($keyword, $arr) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
}

?>