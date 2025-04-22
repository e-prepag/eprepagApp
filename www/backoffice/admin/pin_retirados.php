<?php 
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

$mailing_number = $_REQUEST['mailing_number'];


$sql = "select (select count(*) from sendpin_email_list where mailing_number = $mailing_number AND email is not null ) as ncadastrados, (select count(*) from sendpin_email_list where mailing_number = $mailing_number AND email is not null AND disparado = 1 ) as ndisparados, (select count(*) from sendpin_email_list where mailing_number = $mailing_number AND email is not null AND lido = 0 AND disparado = 1 ) as nnaolidos  ";

//echo $sql."<br>";
//echo "DOCUMENT_ROOT: ".$_SERVER['DOCUMENT_ROOT']."<br>";
$rss = SQLexecuteQuery($sql);
if($rss && pg_num_rows($rss)>0) {
    $vlr = pg_fetch_array($rss);
    $ncadastrados = $vlr['ncadastrados'];
    $ndisparados = $vlr['ndisparados'];
    $nnaolidos = $vlr['nnaolidos'];
}

$sql = "SELECT
        pin, email, lido, datalido, hash
        FROM
        sendpin_email_list 
        WHERE 
        mailing_number = $mailing_number
        AND 
        email is not null
        AND 
        (not lido = 0)
        AND 
        disparado = 1 
        ORDER BY
        datalido desc
       ";
//echo $sql."<br>";
$rss = SQLexecuteQuery($sql);
$nlidos = pg_num_rows($rss);	
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active">PINS PROMOCIONAIS WEBZEN</b> (<?php echo date("Y-m-d H:i:s")?>)</li>
    </ol>
</div>
<table class="table txt-preto">
	<tr> 
		<td valign="top">
            <p> Cadastrados: <?php echo $ncadastrados;?><br> 
                Enviados: <?php echo $ndisparados." (".number_format((100*$ndisparados/(($ncadastrados>0)?$ncadastrados:1)), 2, '.', '.')."%)";?><br>
                &nbsp;&nbsp;&nbsp;Não Lidos: <?php echo $nnaolidos." (".number_format((100*$nnaolidos/(($ndisparados>0)?$ndisparados:1)), 2, '.', '.')."%)"; ?><br> 
                &nbsp;&nbsp;&nbsp;Acessados: <?php echo $nlidos." (".number_format((100*$nlidos/(($ndisparados>0)?$ndisparados:1)), 2, '.', '.')."%)"; ?></p>
                <?php if($ndisparados<$ncadastrados) { ?>
                    Falta para completar o envio: <?php echo "".number_format((2*($ncadastrados-$ndisparados)/60), 2, '.', '.')."" ?> mins (<?php echo "".number_format((2*($ncadastrados-$ndisparados)/60/60), 2, '.', '.')."" ?> h)<br>
                <?php } ?>
                <table width="100%" border="0" cellspacing="1" cellpadding="1">
                  <tr bgcolor="#CCCCFF">
                    <td><div align="center">HASH</div></td>
                    <td><div align="center">PIN</div></td>
                    <td><div align="center">EMAIL</div></td>
                    <td><div align="center">VEZES LIDO</div></td>
                    <td><div align="center">DATA LIDO</div></td>
                  </tr>
                  <?php
                    while($vlr = pg_fetch_array($rss)) {
                        $hash 	= $vlr['hash'];
                        $pin 	= $vlr['pin'];
                        $email  = $vlr['email'];
                        $lido = $vlr['lido'];
                        $data	= date('Y/m/d H:i:s',strtotime($vlr['datalido']));
                       ?>
                      <tr>
                        <td>--<?php //echo $hash;?></td>
                        <td>--<?php //echo $pin;?></td>
                        <td><?php echo $email;?></td>
                        <td align="center"><?php echo $lido;?></td>
                        <td align="center"><?php echo $data;?></td>
                      </tr>
                      <?php
                    }
                  ?>
                </table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>