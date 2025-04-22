<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
set_time_limit(3600);
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
include_once $raiz_do_projeto."includes/gamer/constantes.php";

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="clearfix"></div>
    <script>
        function fcnOnSubmit(){

            if(form1.ctsd_data.value==''){
                alert('Ano a ser pesquisado não especificado.');
                return false;
            }

        }
    </script>
    <style>
        body {
            font-family: Verdana !important;
            font-size: 12px;
        }
        fieldset {
            width: 100%;
            margin-top: 35px;
            height: 260px;
            text-align: justify;
            border-radius: 10px;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            -o-border-radius: 10px;
        }
        fieldset input[type="submit"] {
            margin-left: 80px;
            font-size: 15px;
            color: #FFFFFF;
            background-color: #A6A6A6;
            border: none;
            text-transform: none;
            font-weight: bold;
            padding: 5px 15px 5px 15px;
            border-radius: 10px;
            -webkit-border-radius: 10px;
        }
        fieldset form {
            margin-left: 13%;
            margin-top: 35px;
        }
        fieldset select {
            font-family: Verdana !important;
            font-size: 15px !important;
        }
        .msg {
            font-weight: 500;
            font-size: 17px;
            text-align: center;
        }
        .error {
            color: #ff423e;
        }
        .success {
            color: #29981b;
        }
        .wrapper_tbl {
            margin: 0 auto;
            width: 550px;
            border-collapse: collapse;
            border: 1px solid #b1b1b1;
        }
        .wrapper_tbl thead th {
            border-bottom: 1px solid #b1b1b1;
        }
        .odd{background-color: #ebfff6;}
        .odd:hover{background-color: #c9ded5;}

        .even{background-color: #f4ffe3;}
        .even:hover{background-color: #d3dec3;}
    </style>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<fieldset>
    <legend>Relatório de Maior Valor de Saldo Diário para Compliance</legend>
    <br>
    Selecione o ANO para ser considerado no levantamento*. 
    <form name="form1" action="" enctype="multipart/form-data" method="post" onsubmit="return fcnOnSubmit();">
        ANO BASE: <select id='ctsd_data' name='ctsd_data'>
            <?php  for($i =  date('Y'); $i >= 2015 ; $i--) { ?>
                    <option value="<?php  echo $i ?>" <?php  if(isset($ctsd_data) && $ctsd_data == $i) echo "selected" ?>><?php  echo $i ?></option>
            <?php  } ?>
        </select>
        <input type="submit" name="BtnSubmit" value="Pesquisar" />
    </form>
    <br>
    * O banco central pede na informação anual o maior valor diário observado durante o ano-base definido, calculado a partir da soma diária dos saldos de todas as contas de pagamento na data-base definida. Por exemplo, em 2014 houve apenas três dias cujas somas dos saldos diários das contas de pagamento foram superiores a zero: 18, 19 e 20 de novembro, com saldos diários de R$50.000,00, R$100.000,00 e R$80.000,00, respectivamente; então, o valor informado para o ano-base 2014 deverá ser R$100.000,00.
</fieldset>
</body>
</html>
<?php
if(isset($BtnSubmit) && $BtnSubmit) {

    echo "<br><br>";

    echo '<table class="wrapper_tbl">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Maior Valor Saldo Diário de Gamers</th>
                    </tr>
                </thead>
                <tbody>
            ';
    
    // Capturando o Maior Valor Saldo Diário de Gamers
    $sql = "
    SELECT to_char(ctsd_data,'DD/MM/YYYY') as data, 
           ctsd_saldo_gamer as maior_saldo
    FROM COMPLIANCE_TOTAL_SALDO_DIARIO 
    WHERE ctsd_saldo_gamer = (
                              SELECT max(ctsd_saldo_gamer) 
                              FROM COMPLIANCE_TOTAL_SALDO_DIARIO 
                              WHERE date_trunc('year', ctsd_data) = '".$ctsd_data."-01-01 00:00:00'
                              );
    ";
    //echo $sql.PHP_EOL; die();

    $rsInfoAtivos = SQLexecuteQuery($sql);
    if(!$rsInfoAtivos) echo "Erro ao selecionar o Maior Saldo de Gamer.<br>".PHP_EOL;
    else { 
        //Capturando Dados 
        $rsInfoAtivos_row = pg_fetch_array($rsInfoAtivos); //even
        echo "<tr class='odd'>
                    <td align='center'>".$rsInfoAtivos_row['data']."</td>
                    <td align='right'>R$ ".number_format($rsInfoAtivos_row['maior_saldo'],2,",",".")."</td>
              </tr>".PHP_EOL;
    }//end else do if(!$rsDesvio)

    echo ' </tbody>
        </table>';
    
    echo "<br><br>";

    echo '<table class="wrapper_tbl">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Maior Valor Saldo Diário de LAN</th>
                    </tr>
                </thead>
                <tbody>
            ';
    
    // Capturando o Maior Valor Saldo Diário de LAN
    $sql = "
    SELECT to_char(ctsd_data,'DD/MM/YYYY') as data, 
           ctsd_saldo_lan as maior_saldo
    FROM COMPLIANCE_TOTAL_SALDO_DIARIO 
    WHERE ctsd_saldo_lan = (
                              SELECT max(ctsd_saldo_lan) 
                              FROM COMPLIANCE_TOTAL_SALDO_DIARIO 
                              WHERE date_trunc('year', ctsd_data) = '".$ctsd_data."-01-01 00:00:00'
                              );
    ";
    //echo $sql.PHP_EOL; die();

    $rsInfoAtivos = SQLexecuteQuery($sql);
    if(!$rsInfoAtivos) echo "Erro ao selecionar o Maior Saldo de LAN.<br>".PHP_EOL;
    else { 
        //Capturando Dados 
        $rsInfoAtivos_row = pg_fetch_array($rsInfoAtivos); //even
        echo "<tr class='odd'>
                    <td align='center'>".$rsInfoAtivos_row['data']."</td>
                    <td align='right'>R$ ".number_format($rsInfoAtivos_row['maior_saldo'],2,",",".")."</td>
              </tr>".PHP_EOL;
    }//end else do if(!$rsDesvio)

    echo ' </tbody>
        </table>';


    echo "<br><br>";

    echo '<table class="wrapper_tbl">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Maior Valor Saldo Diário de Gamer + LAN</th>
                    </tr>
                </thead>
                <tbody>
            ';
    
    // Capturando o Maior Valor Saldo Diário de LAN
    $sql = "
    SELECT to_char(ctsd_data,'DD/MM/YYYY') as data, 
           (ctsd_saldo_lan+ctsd_saldo_gamer) as maior_saldo
    FROM COMPLIANCE_TOTAL_SALDO_DIARIO 
    WHERE (ctsd_saldo_lan+ctsd_saldo_gamer) = (
                              SELECT max(ctsd_saldo_lan+ctsd_saldo_gamer) 
                              FROM COMPLIANCE_TOTAL_SALDO_DIARIO 
                              WHERE date_trunc('year', ctsd_data) = '".$ctsd_data."-01-01 00:00:00'
                              );
    ";
    //echo $sql.PHP_EOL; die();

    $rsInfoAtivos = SQLexecuteQuery($sql);
    if(!$rsInfoAtivos) echo "Erro ao selecionar o Maior Saldo de GAMER + LAN.<br>".PHP_EOL;
    else { 
        //Capturando Dados 
        $rsInfoAtivos_row = pg_fetch_array($rsInfoAtivos); //even
        echo "<tr class='odd'>
                    <td align='center'>".$rsInfoAtivos_row['data']."</td>
                    <td align='right'>R$ ".number_format($rsInfoAtivos_row['maior_saldo'],2,",",".")."</td>
              </tr>".PHP_EOL;
    }//end else do if(!$rsDesvio)

    echo ' </tbody>
        </table>';



}//end if($BtnSubmit)

?>
<br>  
<br>  
<br>  
<br>  
