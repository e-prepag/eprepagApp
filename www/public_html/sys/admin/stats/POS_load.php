<?php 
    require_once "../../../../includes/constantes.php";
    require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php"; 
    
//session_start();
	set_time_limit(300); // 5min

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		//redireciona
		$strRedirect = "/sys/admin/commerce/index.php";
		ob_end_clean();
		header("Location: " . $strRedirect);
		exit;
		?><html><body onload="window.location <?php echo "$strRedirect";?>"><?php
		exit;
		
		ob_end_flush();
	}


//	include "../../../prepag2/commerce/includes/connect.php";
//	include "../../../incs/functions.php";

?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> Carrega POS </title>
        <link rel="stylesheet" href="/sys/css/css.css" type="text/css">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
    
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row txt-cinza-claro">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-10 ">
                        <strong><?php echo "Dados de POS - Carrega dados"; ?></strong>
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <div class="row">
                    <?php
                        $print_output = true;

                        require_once "inc_POSLoad.php";
                    ?>
                </div>
            </div>
        </div>
    </div>    

<?php

        require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; 
?>

</body>
</html>
