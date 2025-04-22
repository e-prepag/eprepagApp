<?php 
set_time_limit ( 30000 ) ;
$run_silently = "OK";

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/pdv/inc_pesquisa_usuarios_sql.php";
require_once $raiz_do_projeto."class/util/classFilePosition.php";

$mensagem = "";
$nomeArquivo = "DESTINATARIOS_".date("dmYhis").".txt";

//1SIGEP DESTINATARIO NACIONAL
$file = new FilePosition($nomeArquivo);
$file->setDir($raiz_do_projeto."public_html/tmp/txt/");
// Cabeçalho
$vetorHeader =  array (
                    0 => array('name' => '1',
                               'size' => 1
                               ),
                    1 => array('name' => "SIGEP DESTINATARIO NACIONAL",
                               'size' => 27
                               )
            );

$file->setVetorHeader($vetorHeader);
$cont = 0;
while($rs_usuario_row = pg_fetch_array($rs_usuario)){

    if( $rs_usuario_row['ug_repr_legal_nome'] == "" || 
        $rs_usuario_row['ug_cep'] == "" ||
        $rs_usuario_row['ug_endereco'] == "" || 
        $rs_usuario_row['ug_cidade'] == "")
    {
        continue;
    }
    $cont++;
    
    $nomeFant       =	$rs_usuario_row['ug_id']." - ".$rs_usuario_row['ug_repr_legal_nome'];
    $cep            =	str_replace("-","",$rs_usuario_row['ug_cep']);
    $cnpjCpf        =	($rs_usuario_row['ug_cnpj']) ? $rs_usuario_row['ug_cnpj'] : $rs_usuario_row['ug_cpf'];
    $endereco       =	($rs_usuario_row['ug_tipo_end'] != "") ? $rs_usuario_row['ug_tipo_end']." ".$rs_usuario_row['ug_endereco'] : "Rua ".$rs_usuario_row['ug_endereco'];//.",".$rs_usuario_row['ug_numero']." ".$rs_usuario_row['ug_complemento'];

    if($rs_usuario_row['ug_numero'])
    {
        $num = strlen($rs_usuario_row['ug_numero']) < 6 ? $rs_usuario_row['ug_numero']." " : $rs_usuario_row['ug_numero'];
    }else
        $num = "0 ";
    
    $complemento    =	$rs_usuario_row['ug_complemento'];
    $bairro         =   $rs_usuario_row['ug_bairro'];
    $cidade         =	$rs_usuario_row['ug_cidade'];
    
    $vetorLines = array (
                0 => array('name' => "2",
                           'size' => 1
                            ),
                1 => array('name' => $cnpjCpf,
                           'size' => 14
                            ),
                2 => array('name' => $nomeFant,
                           'size' => 50
                           ),
                3 => array('name' => ' ',//email
                           'size' => 50
                           ),
                4 => array('name' => ' ',//aos cuidados
                           'size' => 50
                           ),
                5 => array('name' => ' ', //contato
                           'size' => 50
                           ),
                6 => array('name' => $cep,
                           'size' => 8
                           ),
                7 => array('name' => $endereco." ",
                            'size' => 50
                            ),
                8 => array('name' => $num,
                            'size' => 6
                            ),
                9 => array('name' => $complemento." ", //complemento
                            'size' => 30
                            ),
                10 => array('name' => $bairro,
                            'size' => 50
                            ),
                11 => array('name' => $cidade,
                            'size' => 50
                            ),
                12 => array('name' => ' ',//telefone
                            'size' => 18
                            ),
                13 => array('name' => ' ',//celular
                            'size' => 12
                            ),
                14 => array('name' => ' ',//fax
                            'size' => 12
                            )
                );
    $file->setVetorLines($vetorLines);
	
}
unset($vetorLines);

$vetorLines = array (
                0 => array('name' => "9",
                           'size' => 1
                            ),
                1 => array('name' => $cont,
                           'size' => 6
                            )
                );
//$vetorLines
$file->setVetorLines($vetorLines);
unset($vetorLines);

$file->saveFile(true);

if($file->checkFile()){
    $txt = str_replace("\\","/",$file->getDir().date("Ymd")."/".$file->getFileName());
    if($str = file_get_contents($txt)){
        $str = str_replace("\n", "\r\n", $str);
        $handle = fopen($txt, "w+");
        fwrite($handle, $str);
        fclose($handle);
        
    }
?>
<a class="txt-branco" href="/includes/download/dld.php?f=<?php echo $file->getFileName(); ?>&fc=<?php echo $file->getFileName(); ?>">Download (<?php echo $cont?> de <?php echo pg_num_rows($rs_usuario); ?> registros exportados)</a>    
<?php    
    //echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
}
else {
   echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
}
//==================================  Fim do trecho a geração do arquivo CONTATOS.TXT
