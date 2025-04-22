<?php 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php"; 
//session_start();

if($_SESSION["tipo_acesso_pub"]=='PU') 
{
        //redireciona
        $strRedirect = "/sys/admin/commerce/index.php";
        ob_end_clean();
        header("Location: " . $strRedirect);
        exit;
        ?><html><body onLoad="window.location='<?=$strRedirect?>'"><?php
        exit;

        ob_end_flush();
}

require_once $raiz_do_projeto . "includes/sys/inc_stats.php";
$time_start_stats = getmicrotime();
?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title> <?php echo LANG_STATISTICS_TOTAL_SALES; ?> </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <link href="/sys/css/css.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_STATISTICS_PAGE_TITLE_8; if(strlen($_SESSION["opr_nome"])>0) echo "<span class='txt-azul-claro'>".$_SESSION["opr_nome"]."</span> ";?>- <?php echo LANG_STATISTICS_PAGE_TITLE_2; ?> (<?php echo get_current_date()?>)</strong>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_POS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6 espacamento">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <?php

                        $bg_col_01 = "#FFFFFF";
                        $bg_col_02 = "#EEEEEE";
                        $bg_col = $bg_col_01;
                        $extra_where = "";

                        if($_SESSION["tipo_acesso_pub"]=='PU') {
                                $dd_operadora = $_SESSION["opr_codigo_pub"];
                                if(($dd_operadora==13) || ($dd_operadora==16) || ($dd_operadora==17) )
                                { 
                                    $where_operadora_pos = " ve_jogo='".(($dd_operadora==13)?"OG":(($dd_operadora==16)?"HB":(($dd_operadora==17)?"MU":"??")))."'";
                                } else {
                                    $where_operadora_pos = "";
                                }
                        }

                        $iday = date("d"); // or any value from 1-12
                        $imonth = date("n"); // or any value from 1-12
                        $iyear	= date("Y"); // or any value >= 1
                        $days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
                        $days_in_month_prev = date("t",mktime(0,0,0,$imonth-1,1,$iyear));
                        $twomonthsago  = mktime(0, 0, 0, date("m")-2, date("d"), date("Y"));

                        // POS ========================================================================================
                        // Totais de Vendas POS
                        $sql_POS = get_sql_query("P", "totais_de_vendas", $extra_where, $smode);
                        $total_vendas_POS = 0;
                        $n_vendas_POS = 0;
                        $vendas_estado_POS = SQLexecuteQuery($sql_POS);
                        if($vendas_estado_POS) 
                        {
                            while ($vendas_estado_POS_row = pg_fetch_array($vendas_estado_POS))
                            {
                                $total_vendas_POS = $vendas_estado_POS_row['vendas'];
                                $n_vendas_POS = $vendas_estado_POS_row['n'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'><strong>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG."</strong></div></div>";
                        }

                        // Totais de Vendas Mês POS
                        $thismonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
                        $extra_where = " (ve_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') | (vg_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') ";
                        $sql = get_sql_query("P", "totais_de_vendas", $extra_where, $smode);
                        $extra_where = "";
                //echo "sql: $sql<br>";
                        $total_vendas_mes_POS = 0;
                        $vendas_estado = SQLexecuteQuery($sql);
                        if($vendas_estado) 
                        {
                            while ($vendas_estado_row = pg_fetch_array($vendas_estado))
                            {
                                $total_vendas_mes_POS = $vendas_estado_row['vendas'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_NOT_FOUND_2."</strong></div></div>";
                        }

                        // Datas Limites no BD POS
                        $sql_POS = get_sql_query("P", "datas_limites_no_bd", $extra_where, $smode);
                        $data_min_POS = date("Y-m-d");
                        $data_max_POS = date("Y-m-d");
                        $vendas_estado_POS = SQLexecuteQuery($sql_POS);
                        if($vendas_estado_POS) 
                        {
                            while ($vendas_estado_POS_row = pg_fetch_array($vendas_estado_POS))
                            {
                                    $data_min_POS = $vendas_estado_POS_row['data_min'];
                                    $data_max_POS = $vendas_estado_POS_row['data_max'];
                            }
                        } else
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_2."</strong></div></div>";
                        }

                        // Por dia POS
                        $sql_POS = get_sql_query("P", "por_dia", $extra_where, $smode);
                //echo "$sql_POS<br>";
                        $vendas_estado_POS = SQLexecuteQuery($sql_POS);
                        $n_dias_POS = pg_num_rows($vendas_estado_POS);

                        // Media de Vendas POS
                        //$extra_where_two_months_ago = " vg_data_inclusao>='".date("Y-m-d",$twomonthsago)."'";
                        $extra_where_two_months_ago = " ve_data_inclusao>='".date("Y-m-d",$twomonthsago)."' |  vg_data_inclusao>='".date("Y-m-d",$twomonthsago)."'";
                        $sql_POS_media = get_sql_query("P", "por_dia", $extra_where_two_months_ago, $smode);
                //echo "$sql_POS_media<br>";
                        $total_vendas_POS_media = 0;
                        $vendas_estado_POS_media = SQLexecuteQuery($sql_POS_media);
                //	$n_dias_POS_media = (pg_num_rows($vendas_estado_POS_media)>0)?pg_num_rows($vendas_estado_POS_media):1;(R$/dia)  
                        $n_dias_POS_media = 60; 
                //echo "n_dias_POS_media: $n_dias_POS_media<br>";
                        if($vendas_estado_POS_media) 
                        {
                            while ($vendas_estado_POS_media_row = pg_fetch_array($vendas_estado_POS_media))
                            {
                                $total_vendas_POS_media += $vendas_estado_POS_media_row['vendas'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG."</strong></div></div>";
                        }

                        // Money ========================================================================================
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            $dd_operadora = $_SESSION["opr_codigo_pub"];
                            if(strlen($dd_operadora)>0) 
                            { 
                                    $where_operadora_Money = " and vgm_opr_codigo='".$dd_operadora."'";
                            } else
                            {
                                    $where_operadora_Money = "";
                            }
                        }

                        // Totais de Vendas Money
                        $sql_Money = get_sql_query("M", "totais_de_vendas", $extra_where, $smode);
                        $total_vendas_Money = 0;
                        $n_vendas_Money = 0;
                        $vendas_estado_Money = SQLexecuteQuery($sql_Money);
                        if($vendas_estado_Money) 
                        {
                            while ($vendas_estado_Money_row = pg_fetch_array($vendas_estado_Money))
                            {
                                    $total_vendas_Money = $vendas_estado_Money_row['vendas'];
                                    $n_vendas_Money = $vendas_estado_Money_row['n'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_3."</strong></div></div>";
                        }

                        // Totais de Vendas Mês Money
                        $thismonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
                        $extra_where = " (vg.vg_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') ";
                        $sql_Money = get_sql_query("M", "totais_de_vendas", $extra_where, $smode);
                        $extra_where = "";
                //echo "sql_Money: $sql_Money<br>";
                        $total_vendas_mes_Money = 0;
                        $vendas_estado_Money = SQLexecuteQuery($sql_Money);
                        if($vendas_estado_Money)
                        {
                            while ($vendas_estado_Money_row = pg_fetch_array($vendas_estado_Money))
                            {
                                $total_vendas_mes_Money = $vendas_estado_Money_row['vendas'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_NOT_FOUND_2."</strong></div></div>";
                        }

                        // Datas Limites no BD Money
                        $sql_Money = get_sql_query("M", "datas_limites_no_bd", $extra_where, $smode);
                        $data_min_Money = date("Y-m-d");
                        $data_max_Money = date("Y-m-d");
                        $vendas_estado_Money = SQLexecuteQuery($sql_Money);
                        if($vendas_estado_Money) 
                        {
                            while ($vendas_estado_Money_row = pg_fetch_array($vendas_estado_Money))
                            {
                                $data_min_Money = $vendas_estado_Money_row['data_min'];
                                $data_max_Money = $vendas_estado_Money_row['data_max'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_4."</strong></div></div>";
                        }

                        // Por dia Money
                        $sql_Money = get_sql_query("M", "por_dia", $extra_where, $smode);
                        $vendas_estado_Money = SQLexecuteQuery($sql_Money);
                        $n_dias_Money = pg_num_rows($vendas_estado_Money);


                        // Media de Vendas Money
                        $extra_where_two_months_ago = " vg.vg_data_inclusao>='".date("Y-m-d",$twomonthsago)."'";
                        $sql_Money_media = get_sql_query("M", "por_dia", $extra_where_two_months_ago, $smode);
                //echo "$sql_Money_media<br>";
                        $total_vendas_Money_media = 0;
                        $vendas_estado_Money_media = SQLexecuteQuery($sql_Money_media);
                //	$n_dias_Money_media = (pg_num_rows($vendas_estado_Money_media)>0)?pg_num_rows($vendas_estado_Money_media):1;
                        $n_dias_Money_media = 60;
                //echo "n_dias_Money_media: $n_dias_Money_media<br>";
                        if($vendas_estado_Money_media) 
                        {
                            while ($vendas_estado_Money_media_row = pg_fetch_array($vendas_estado_Money_media))
                            {
                                    $total_vendas_Money_media += $vendas_estado_Money_media_row['vendas'];
                            }
                        } else 
                        {
                                echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_3."</strong></div></div>";
                        }

                        // atimo ========================================================================================
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            $dd_operadora = $_SESSION["opr_codigo_pub"];
                            if(strlen($dd_operadora)>0) 
                            { 
                                    $where_operadora_Money = " and vgm_opr_codigo='".$dd_operadora."'";
                            } else
                            {
                                    $where_operadora_Money = "";
                            }
                        }

                        // Totais de Vendas atimo
                        $sql_atimo = get_sql_query("A", "totais_de_vendas", $extra_where, $smode);
                        $total_vendas_atimo = 0;
                        $n_vendas_atimo = 0;
                        $vendas_estado_atimo = SQLexecuteQuery($sql_atimo);
                        if($vendas_estado_atimo) 
                        {
                            while ($vendas_estado_atimo_row = pg_fetch_array($vendas_estado_atimo))
                            {
                                    $total_vendas_atimo = $vendas_estado_atimo_row['vendas'];
                                    $n_vendas_atimo = $vendas_estado_atimo_row['n'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_3."</strong></div></div>";
                        }
						
                        // Totais de Vendas Mês atimo
                        $thismonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
                        $extra_where = " (vg.vg_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') ";
                        $sql_atimo = get_sql_query("A", "totais_de_vendas", $extra_where, $smode);
                        $extra_where = "";
                        $total_vendas_mes_atimo = 0;
                        $vendas_estado_atimo = SQLexecuteQuery($sql_atimo);
                        if($vendas_estado_atimo)
                        {
                            while ($vendas_estado_atimo_row = pg_fetch_array($vendas_estado_atimo))
                            {
                                $total_vendas_mes_atimo = $vendas_estado_atimo_row['vendas'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_NOT_FOUND_2."</strong></div></div>";
                        }

                        // Datas Limites no BD atimo
                        $sql_atimo = get_sql_query("A", "datas_limites_no_bd", $extra_where, $smode);
                        $data_min_atimo = date("Y-m-d");
                        $data_max_atimo = date("Y-m-d");
                        $vendas_estado_atimo = SQLexecuteQuery($sql_atimo);
                        if($vendas_estado_atimo) 
                        {
                            while ($vendas_estado_atimo_row = pg_fetch_array($vendas_estado_atimo))
                            {
                                $data_min_atimo = $vendas_estado_atimo_row['data_min'];
                                $data_max_atimo = $vendas_estado_atimo_row['data_max'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_4."</strong></div></div>";
                        }

                        // Por dia atimo
                        $sql_atimo = get_sql_query("A", "por_dia", $extra_where, $smode);
                        $vendas_estado_atimo = SQLexecuteQuery($sql_atimo);
                        $n_dias_atimo = pg_num_rows($vendas_estado_atimo);


                        // Media de Vendas atimo
                        $extra_where_two_months_ago = " vg.vg_data_inclusao>='".date("Y-m-d",$twomonthsago)."'";
                        $sql_atimo_media = get_sql_query("A", "por_dia", $extra_where_two_months_ago, $smode);
                        $total_vendas_atimo_media = 0;
                        $vendas_estado_atimo_media = SQLexecuteQuery($sql_atimo_media);
                        $n_dias_atimo_media = 60;
                        if($vendas_estado_atimo_media) 
                        {
                            while ($vendas_estado_atimo_media_row = pg_fetch_array($vendas_estado_atimo_media))
                            {
                                    $total_vendas_atimo_media += $vendas_estado_atimo_media_row['vendas'];
                            }
                        } else 
                        {
                                echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_3."</strong></div></div>";
                        }

                        // Money Express ========================================================================================
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            $dd_operadora = $_SESSION["opr_codigo_pub"];
                            if(strlen($dd_operadora)>0) 
                            { 
                                    $where_operadora_MoneyEx = " and vgm_opr_codigo='".$dd_operadora."'";
                            } else
                            {
                                    $where_operadora_MoneyEx = "";
                            }
                        }

                        // Totais de Vendas MoneyEx
                        $sql_MoneyEx = get_sql_query("E", "totais_de_vendas", $extra_where, $smode);
                //echo "sql_MoneyEx: $sql_MoneyEx<br>";

                        $total_vendas_MoneyEx = 0;
                        $n_vendas_MoneyEx = 0;
                        $vendas_estado_MoneyEx = SQLexecuteQuery($sql_MoneyEx);
                        if($vendas_estado_MoneyEx) 
                        {
                            while ($vendas_estado_MoneyEx_row = pg_fetch_array($vendas_estado_MoneyEx))
                            {
                                    $total_vendas_MoneyEx = $vendas_estado_MoneyEx_row['vendas'];
                                    $n_vendas_MoneyEx = $vendas_estado_MoneyEx_row['n'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_5."</strong></div></div>";
                        }

                        // Totais de Vendas Mês MoneyEx
                        $thismonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
                        $extra_where = " (vg.vg_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') ";
                        $sql_MoneyEx = get_sql_query("E", "totais_de_vendas", $extra_where, $smode);
                        $extra_where = "";
                //echo "sql_MoneyEx: $sql_MoneyEx<br>";
                        $total_vendas_mes_MoneyEx = 0;
                        $vendas_estado_MoneyEx = SQLexecuteQuery($sql_MoneyEx);
                        if($vendas_estado_MoneyEx) 
                        {
                            while ($vendas_estado_MoneyEx_row = pg_fetch_array($vendas_estado_MoneyEx))
                            {
                                    $total_vendas_mes_MoneyEx = $vendas_estado_MoneyEx_row['vendas'];
                            }
                        } else 
                        {
                                echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_NOT_FOUND_2."</strong></div></div>";
                        }


                        // Datas Limites no BD MoneyEx
                        $sql_MoneyEx = get_sql_query("E", "datas_limites_no_bd", $extra_where, $smode);
                        $data_min_MoneyEx = date("Y-m-d");
                        $data_max_MoneyEx = date("Y-m-d");
                        $vendas_estado_MoneyEx = SQLexecuteQuery($sql_MoneyEx);
                        if($vendas_estado_MoneyEx) 
                        {
                            while ($vendas_estado_MoneyEx_row = pg_fetch_array($vendas_estado_MoneyEx))
                            {
                                $data_min_MoneyEx = $vendas_estado_MoneyEx_row['data_min'];
                                $data_max_MoneyEx = $vendas_estado_MoneyEx_row['data_max'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_6."</strong></div></div>";
                        }

                        // Por dia MoneyEx
                        $sql_MoneyEx = get_sql_query("E", "por_dia", $extra_where, $smode);
                        $vendas_estado_MoneyEx = SQLexecuteQuery($sql_MoneyEx);
                        $n_dias_MoneyEx = pg_num_rows($vendas_estado_MoneyEx);

                        // Media de Vendas MoneyEx
                        $extra_where_two_months_ago = " vg.vg_data_inclusao>='".date("Y-m-d",$twomonthsago)."'";
                        $sql_MoneyEx_media = get_sql_query("E", "por_dia", $extra_where_two_months_ago, $smode);
                //echo "sql_MoneyEx_media: $sql_MoneyEx_media<br>";
                        $total_vendas_MoneyEx_media = 0;
                        $vendas_estado_MoneyEx_media = SQLexecuteQuery($sql_MoneyEx_media);
                        $n_dias_MoneyEx_media = (pg_num_rows($vendas_estado_MoneyEx_media)>0)?pg_num_rows($vendas_estado_MoneyEx_media):1;
                        $n_dias_MoneyEx_media = 60; 
                //echo "n_dias_MoneyEx_media: $n_dias_MoneyEx_media<br>";
                        if($vendas_estado_MoneyEx_media) 
                        {
                            while ($vendas_estado_MoneyEx_media_row = pg_fetch_array($vendas_estado_MoneyEx_media))
                            {
                                $total_vendas_MoneyEx_media += $vendas_estado_MoneyEx_media_row['vendas'];
                            }
                        } else {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_5."</strong></div></div>";
                        }


                        // LH Money ========================================================================================
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            $dd_operadora = $_SESSION["opr_codigo_pub"];
                            if(strlen($dd_operadora)>0)
                            { 
                                    $where_operadora_LHMoney = " and vgm_opr_codigo='".$dd_operadora."'";
                            } else 
                            {
                                    $where_operadora_LHMoney = "";
                            }
                        }

                        // Totais de Vendas LHMoney
                        $sql_LHMoney = get_sql_query("L", "totais_de_vendas", $extra_where, $smode);
                //echo "sql_LHMoney: $sql_LHMoney<br>";

                        $total_vendas_LHMoney = 0;
                        $n_vendas_LHMoney = 0;
                        $vendas_estado_LHMoney = SQLexecuteQuery($sql_LHMoney);
                        if($vendas_estado_LHMoney) 
                        {
                            while ($vendas_estado_LHMoney_row = pg_fetch_array($vendas_estado_LHMoney))
                            {
                                $total_vendas_LHMoney = $vendas_estado_LHMoney_row['vendas'];
                                $n_vendas_LHMoney = $vendas_estado_LHMoney_row['n'];
                            }
                        } else {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_5."</strong></div></div>";
                        }


                        // Totais de Vendas Mês LHMoney
                        $thismonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
                        $extra_where = " (vg.vg_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') ";
                        $sql_LHMoney = get_sql_query("L", "totais_de_vendas", $extra_where, $smode);
                        $extra_where = "";
                //echo "sql_LHMoney: $sql_LHMoney<br>";
                        $total_vendas_mes_LHMoney = 0;
                        $vendas_estado_LHMoney = SQLexecuteQuery($sql_LHMoney);
                        if($vendas_estado_LHMoney) 
                        {
                            while ($vendas_estado_LHMoney_row = pg_fetch_array($vendas_estado_LHMoney))
                            {
                                $total_vendas_mes_LHMoney = $vendas_estado_LHMoney_row['vendas'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_NOT_FOUND_2."</strong></div></div>";
                        }

                        // Datas Limites no BD LHMoney
                        $sql_LHMoney = get_sql_query("L", "datas_limites_no_bd", $extra_where, $smode);
                //echo "sql_LHMoney: $sql_LHMoney<br>";
                        $data_min_LHMoney = date("Y-m-d");
                        $data_max_LHMoney = date("Y-m-d");
                        $vendas_estado_LHMoney = SQLexecuteQuery($sql_LHMoney);
                        if($vendas_estado_LHMoney)
                        {
                            while ($vendas_estado_LHMoney_row = pg_fetch_array($vendas_estado_LHMoney))
                            {
                                $data_min_LHMoney = $vendas_estado_LHMoney_row['data_min'];
                                $data_max_LHMoney = $vendas_estado_LHMoney_row['data_max'];
                            }
                        } else {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_6."</strong></div></div>";
                        }

                        // Por dia LHMoney
                        $sql_LHMoney = get_sql_query("L", "por_dia", $extra_where, $smode);
                        $vendas_estado_LHMoney = SQLexecuteQuery($sql_LHMoney);
                        $n_dias_LHMoney = pg_num_rows($vendas_estado_LHMoney);


                        // Media de Vendas LHMoney
                        $extra_where_two_months_ago = " vg.vg_data_inclusao>='".date("Y-m-d",$twomonthsago)."'";
                        $sql_LHMoney_media = get_sql_query("L", "por_dia", $extra_where_two_months_ago, $smode);
                        $total_vendas_LHMoney_media = 0;
                        $vendas_estado_LHMoney_media = SQLexecuteQuery($sql_LHMoney_media);
                        $n_dias_LHMoney_media = (pg_num_rows($vendas_estado_LHMoney_media)>0)?pg_num_rows($vendas_estado_LHMoney_media):1;
                        $n_dias_LHMoney_media = 60; 
                        if($vendas_estado_LHMoney_media) 
                        {
                            while ($vendas_estado_LHMoney_media_row = pg_fetch_array($vendas_estado_LHMoney_media))
                            {
                                $total_vendas_LHMoney_media += $vendas_estado_LHMoney_media_row['vendas'];
                            }
                        } else {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_5."</strong></div></div>";
                        }


                        // Cartoes ========================================================================================
                // 17;"";"MU ONLINE" -> vc_total_mu_online>0
                // 13;"";"ONGAME" - vc_valor_ongame>0
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            $dd_operadora = $_SESSION["opr_codigo_pub"];
                            if($dd_operadora==13)
                            { 
                                    $where_operadora_cartoes = " (ve_jogo='OG') ";
                            } else if($dd_operadora==17) 
                            { 
                                    $where_operadora_cartoes = " (ve_jogo='MU') ";
                            } else
                            { 
                                    $where_operadora_cartoes = "";
                            }
                        }

                        // Totais de Vendas Cartoes
                        $sql_Cartoes = get_sql_query("C", "totais_de_vendas", $extra_where, $smode);
                        $total_vendas_Cartoes = 0;
                        $n_vendas_Cartoes = 0;
                        $vendas_estado_Cartoes = SQLexecuteQuery($sql_Cartoes);
                        if($vendas_estado_Cartoes)
                        {
                            while ($vendas_estado_Cartoes_row = pg_fetch_array($vendas_estado_Cartoes))
                            {
                                $total_vendas_Cartoes = $vendas_estado_Cartoes_row['vendas'];
                                $n_vendas_Cartoes = $vendas_estado_Cartoes_row['n'];
                            }
                        } else 
                        {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_5."</strong></div></div>";
                        }

                //echo "n_Cartoes: $n_vendas_Cartoes, vendas_Cartoes: $total_vendas_Cartoes<br>";


                        // Totais de Vendas Mês Cartoes
                        $thismonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
                        $extra_where = " (vc_data>='".date("Y-m-d H:i:s", $thismonth)."') ";
                        $sql_Cartoes = get_sql_query("C", "totais_de_vendas", $extra_where, $smode);
                        $extra_where = "";
                //echo "sql_Cartoes: $sql_Cartoes<br>";
                        $total_vendas_mes_Cartoes = 0;
                        $vendas_estado_Cartoes = SQLexecuteQuery($sql_Cartoes);
                        if($vendas_estado_Cartoes)
                        {
                            while ($vendas_estado_Cartoes_row = pg_fetch_array($vendas_estado_Cartoes))
                            {
                                $total_vendas_mes_Cartoes = $vendas_estado_Cartoes_row['vendas'];
                            }
                        } else {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_NOT_FOUND_2."</strong></div></div>";
                        }

                        // Datas Limites no BD Cartoes
                        $sql_Cartoes = get_sql_query("C", "datas_limites_no_bd", $extra_where, $smode);
                        $data_min_Cartoes = date("Y-m-d");
                        $data_max_Cartoes = date("Y-m-d");
                        $vendas_estado_Cartoes = SQLexecuteQuery($sql_Cartoes);
                        if($vendas_estado_Cartoes)
                        {
                            while ($vendas_estado_Cartoes_row = pg_fetch_array($vendas_estado_Cartoes))
                            {
                                $data_min_Cartoes = $vendas_estado_Cartoes_row['data_min'];
                                $data_max_Cartoes = $vendas_estado_Cartoes_row['data_max'];
                            }
                        } else {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_6."</strong></div></div>";
                        }

                        // Por dia Cartoes
                        $sql_Cartoes = get_sql_query("C", "por_dia", $extra_where, $smode);
                        $vendas_estado_Cartoes = SQLexecuteQuery($sql_Cartoes);
                        $n_dias_Cartoes = pg_num_rows($vendas_estado_Cartoes);

                        // Media de Vendas Cartoes
                        $extra_where_two_months_ago = " vc_data>='".date("Y-m-d",$twomonthsago)."'";
                        $sql_Cartoes_media = get_sql_query("C", "por_dia", $extra_where_two_months_ago, $smode);
                        $total_vendas_Cartoes_media = 0;
                        $vendas_estado_Cartoes_media = SQLexecuteQuery($sql_Cartoes_media);
                //	$n_dias_Cartoes_media = (pg_num_rows($vendas_estado_Cartoes_media)>0)?pg_num_rows($vendas_estado_Cartoes_media):1;
                        $n_dias_Cartoes_media = 60;	// dois meses
                        if($vendas_estado_Cartoes_media) 
                        {
                            while ($vendas_estado_Cartoes_media_row = pg_fetch_array($vendas_estado_Cartoes_media))
                            {
                                $total_vendas_Cartoes_media += $vendas_estado_Cartoes_media_row['vendas'];
                            }
                        } else {
                            echo "<div class='row'><div class='col-md-12 txt-vermelho'>".LANG_STATISTICS_NOT_FOUND_REGISTER_MSG_5."</strong></div></div>";
                        }



                        // TOTAIS ========================================================================================

                        // Evita as colunas EPP
                        $dd_operadora = " ";

                        $total_vendas_TOTAIS = $total_vendas_POS + $total_vendas_Money + $total_vendas_MoneyEx + $total_vendas_LHMoney + $total_vendas_Cartoes + $total_vendas_atimo ; 
                        $n_vendas_TOTAIS = $n_vendas_POS + $n_vendas_Money + $n_vendas_MoneyEx + $n_vendas_LHMoney + $n_vendas_Cartoes + $n_vendas_atimo;
                        $total_vendas_mes_TOTAIS = $total_vendas_mes_POS + $total_vendas_mes_Money + $total_vendas_mes_MoneyEx + $total_vendas_mes_LHMoney + $total_vendas_mes_Cartoes + $total_vendas_mes_atimo ; 

                        $media_TOTAIS = (($total_vendas_POS/(($n_dias_POS>0)?$n_dias_POS:1)) + ($total_vendas_Money/(($n_dias_Money>0)?$n_dias_Money:1)) + ($total_vendas_atimo/(($n_dias_atimo>0)?$n_dias_atimo:1)) + ($total_vendas_MoneyEx/(($n_dias_MoneyEx>0)?$n_dias_MoneyEx:1)) + ($total_vendas_LHMoney/(($n_dias_LHMoney>0)?$n_dias_LHMoney:1)) + ($total_vendas_Cartoes/(($n_dias_Cartoes>0)?$n_dias_Cartoes:1)) ); 

                        $media_mes_TOTAIS = (($total_vendas_mes_POS/(($iday>0)?$iday:1)) + ($total_vendas_mes_Money/(($iday>0)?$iday:1)) + ($total_vendas_mes_atimo/(($iday>0)?$iday:1)) + ($total_vendas_mes_MoneyEx/(($iday>0)?$iday:1)) + ($total_vendas_mes_LHMoney/(($iday>0)?$iday:1)) + ($total_vendas_mes_Cartoes/(($iday>0)?$iday:1)) ); 

                        $media_TOTAIS_media = (($total_vendas_POS_media/(($n_dias_POS_media>0)?$n_dias_POS_media:1)) + ($total_vendas_Money_media/(($n_dias_Money_media>0)?$n_dias_Money_media:1)) + ($total_vendas_atimo_media/(($n_dias_atimo_media>0)?$n_dias_atimo_media:1)) + ($total_vendas_MoneyEx_media/(($n_dias_MoneyEx_media>0)?$n_dias_MoneyEx_media:1)) + ($total_vendas_LHMoney_media/(($n_dias_LHMoney_media>0)?$n_dias_LHMoney_media:1)) + ($total_vendas_Cartoes_media/(($n_dias_Cartoes_media>0)?$n_dias_Cartoes_media:1)) ); 


                        $n_dias_TOTAIS = $n_dias_POS + $n_dias_Money + $n_dias_atimo + $n_dias_MoneyEx + $n_dias_LHMoney + $n_dias_Cartoes; 
                        $n_dias_TOTAIS_media = $n_dias_POS_media + $n_dias_Money_media + $n_dias_atimo_media + $n_dias_MoneyEx_media + $n_dias_LHMoney_media + $n_dias_Cartoes_media; 

                        $cabecalho = LANG_STATISTICS_CHANNEL.";".LANG_STATISTICS_NUMBER_DAYS.";".LANG_STATISTICS_TOTAL."(R\$);".LANG_STATISTICS_NUMBER_FROM." ".LANG_STATISTICS_SALES_1.";";

                        $colspan = (strlen($dd_operadora)==0) ? 10 : 13;
                        echo '<div class="col-md-12 bg-cinza-claro">';
                        echo '<table id="table" class="table bg-branco txt-preto fontsize-pp">';
                        echo "<tr class='bg-cinza-claro'>
                                <th align='center' colspan='{$colspan}'><b>".LANG_STATISTICS_TOTAL_CHANNEL." ";
                        if(strlen($_SESSION["opr_nome"])>0) 
                            echo "".$_SESSION["opr_nome"]." ";
                        echo "($iday / $days_in_month ".LANG_DAYS.")</b>
                                </th>
                            </tr>";

                        // Mostra resultados
                        echo "<tr class='bg-cinza-claro'>
                                <td align='center'><b>".LANG_STATISTICS_CHANNEL."</b></td>
                                <td align='center'><b>".LANG_STATISTICS_NUMBER_DAYS."</b></td>
                                <td align='center'><b>".LANG_STATISTICS_TOTAL."<br>(R\$)</b></td>
                                <td align='center'><b>".LANG_STATISTICS_NUMBER_FROM."<br>&nbsp;".LANG_STATISTICS_SALES_1."&nbsp;</b></td>";

                        if(strlen($dd_operadora)==0) 
                        {
                            echo "<td align='center'><b>Total EPP<br>(R\$)</b></td>";
                            $cabecalho .= "Total EPP (R\$);";
                        }

                        $cabecalho .= LANG_STATISTICS_TOTAL." ".mes_do_ano(date("Y-m-d"))." (R\$);";
                        $cabecalho .= LANG_STATISTICS_AVERAGE_TOTAL."(R\$/".LANG_DAY.");";
                        $cabecalho .= LANG_STATISTICS_AVERAGE." ".mes_do_ano(date("Y-m-d"))." (R\$/dia);";

                        echo "<td align='center'><b>".LANG_STATISTICS_TOTAL." ".mes_do_ano(date("Y-m-d"))."<br> (R\$)</b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'><b>".LANG_STATISTICS_AVERAGE_TOTAL."<br>&nbsp;&nbsp;(R\$/".LANG_DAY.")</b></td>";
                        echo "<td align='center'><b>".LANG_STATISTICS_AVERAGE." ".mes_do_ano(date("Y-m-d"))."<br>(R\$/dia)</b></td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'><b>".LANG_STATISTICS_AVERAGE_EPP."<br>(R\$/".LANG_DAY.")</b></td>";
                            $cabecalho .= LANG_STATISTICS_AVERAGE_EPP." (R\$/".LANG_DAY.");";
                        }

                        $cabecalho .= LANG_STATISTICS_PROJECTION." ".LANG_STATISTICS_IN." $days_in_month ".LANG_DAYS." (R\$);";

                        echo "<td align='center'></td>";
                        echo "<td align='center'><b>".LANG_STATISTICS_PROJECTION."<br>".LANG_STATISTICS_IN." $days_in_month ".LANG_DAYS."<br>(R\$)</b></td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'><b>".LANG_STATISTICS_PROJECTION_EPP."&nbsp;<br>".LANG_STATISTICS_IN." $days_in_month ".LANG_DAYS."<br>(R\$)</b></td>";
                            $cabecalho .= LANG_STATISTICS_PROJECTION_EPP." ".LANG_STATISTICS_IN." $days_in_month ".LANG_DAYS." (R\$);";
                        }

                        $cabecalho .= LANG_STATISTICS_PROJECTION." %;";
                        require_once $raiz_do_projeto."class/util/CSV.class.php";

                        $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."public_html/cache/");
                        $objCsv->setCabecalho();


                        echo "<td align='center'><b>".LANG_STATISTICS_PROJECTION."<br>%</b> </td>";
                        echo "</tr>";
                        $total_percent = 0;
                        $total_proj = 0;

                        // Dados POS
                        $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                        $total_proj_POS = $total_vendas_mes_POS + (($days_in_month-$iday) * $total_vendas_POS_media / (($n_dias_POS_media>0) ? $n_dias_POS_media : 1));

                        $lineCsv = array();
                        $lineCsv[] = "POS";
                        $lineCsv[] = $n_dias_POS;
                        $lineCsv[] = number_format(($total_vendas_POS), 2, ',', '.');
                        $lineCsv[] = $n_vendas_POS;

                        echo "<tr class='trListagem'>"
                                . "<td align='center'>POS</td>"
                                . "<td align='center'>$n_dias_POS</td>"
                                . "<td align='center'>".number_format(($total_vendas_POS), 2, ',', '.')."</td>"
                                . "<td align='center'>".$n_vendas_POS."</td>";
                        if(strlen($dd_operadora)==0) 
                        {
                            echo "<td align='center'>".number_format(($total_vendas_POS*0.04), 2, ',', '.')."</b></td>";
                            $lineCsv[] = number_format(($total_vendas_POS*0.04), 2, ',', '.');
                        }

                        $lineCsv[] = number_format(($total_vendas_mes_POS), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_POS/(($n_dias_POS>0)?$n_dias_POS:1)), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_mes_POS/(($iday>0)?$iday:1)), 2, ',', '.');


                        echo "<td align='center'>".number_format(($total_vendas_mes_POS), 2, ',', '.')."</b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format(($total_vendas_POS/(($n_dias_POS>0)?$n_dias_POS:1)), 2, ',', '.')."</td>";
                        echo "<td align='center'>".number_format(($total_vendas_mes_POS/(($iday>0)?$iday:1)), 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_POS*0.04/(($n_dias_POS>0)?$n_dias_POS:1)), 2, ',', '.')."</td>";
                            $lineCsv[] = number_format(($total_vendas_POS*0.04/(($n_dias_POS>0)?$n_dias_POS:1)), 2, ',', '.');
                        }

                        $lineCsv[] = number_format($total_proj_POS, 2, ',', '.');
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format($total_proj_POS, 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format($total_proj_POS*0.04, 2, ',', '.')."</td>";
                            $lineCsv[] = number_format($total_proj_POS*0.04, 2, ',', '.');
                        }

                        $calc_result = ($media_TOTAOS_media) ? number_format((100*$total_proj_POS/($days_in_month*$media_TOTAIS_media)), 2, ',', '.') : 0;
                        
                        $lineCsv[] = $calc_result;
                        echo "<td align='center'>".$calc_result."</td>";
                        echo "</tr>";
                        $total_percent += ($media_TOTAOS_media) ? (100*$total_proj_POS/($days_in_month*$media_TOTAIS_media)) : 0;
                        $total_proj += $total_proj_POS;

                        if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));
                        $lineCsv = array();

                        $lineCsv[] = "Money";
                        $lineCsv[] = $n_dias_Money;
                        $lineCsv[] = number_format(($total_vendas_Money), 2, ',', '.');
                        $lineCsv[] = $n_vendas_Money;
                        // Dados Money
                        $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                        $total_proj_Money = $total_vendas_mes_Money + (($days_in_month-$iday) * $total_vendas_Money_media / (($n_dias_Money_media>0) ? $n_dias_Money_media : 1));
                        echo "<tr class='trListagem'>"
                                . "<td align='center'>Money</td>"
                                . "<td align='center'>$n_dias_Money</td>"
                                . "<td align='center'>".number_format(($total_vendas_Money), 2, ',', '.')."</td>"
                                . "<td align='center'>".$n_vendas_Money."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_Money*0.04), 2, ',', '.')."</b></td>";
                            $lineCsv[] = number_format(($total_vendas_Money*0.04), 2, ',', '.');
                        }

                        $lineCsv[] = number_format(($total_vendas_mes_Money), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_Money/(($n_dias_Money>0)?$n_dias_Money:1)), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_mes_Money/(($iday>0)?$iday:1)), 2, ',', '.');

                        echo "<td align='center'>".number_format(($total_vendas_mes_Money), 2, ',', '.')."</b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format(($total_vendas_Money/(($n_dias_Money>0)?$n_dias_Money:1)), 2, ',', '.')."</td>";
                        echo "<td align='center'>".number_format(($total_vendas_mes_Money/(($iday>0)?$iday:1)), 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_Money*0.04/(($n_dias_Money>0)?$n_dias_Money:1)), 2, ',', '.')."</td>";
                            $lineCsv[] = number_format(($total_vendas_Money*0.04/(($n_dias_Money>0)?$n_dias_Money:1)), 2, ',', '.');
                        }

                        $lineCsv[] = number_format($total_proj_Money, 2, ',', '.');
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format($total_proj_Money, 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format($total_proj_Money*0.04, 2, ',', '.')."</td>";
                            $lineCsv[] = number_format($total_proj_Money*0.04, 2, ',', '.');
                        }
                        
                        $calc_result = ($media_TOTAOS_media) ? number_format((100*$total_proj_Money/($days_in_month*$media_TOTAIS_media)), 2, ',', '.') : 0;
                        
                        $lineCsv[] = $calc_result;
                        echo "<td align='center'>".$calc_result."</td>";
                        echo "</tr>";
                        $total_percent += ($media_TOTAOS_media) ? (100*$total_proj_Money/($days_in_month*$media_TOTAIS_media)) : 0;
                        $total_proj += $total_proj_Money;

                        if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));
                        $lineCsv = array();
						
						$lineCsv[] = "Atimo";
                        $lineCsv[] = $n_dias_atimo;
                        $lineCsv[] = number_format(($total_vendas_atimo), 2, ',', '.');
                        $lineCsv[] = $n_vendas_atimo;
                        // Dados atimo
                        $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                        $total_proj_atimo = $total_vendas_mes_atimo + (($days_in_month-$iday) * $total_vendas_atimo_media / (($n_dias_atimo_media>0) ? $n_dias_atimo_media : 1));
                        echo "<tr class='trListagem'>"
                                . "<td align='center'>ATIMO</td>"
                                . "<td align='center'>$n_dias_atimo</td>"
                                . "<td align='center'>".number_format(($total_vendas_atimo), 2, ',', '.')."</td>"
                                . "<td align='center'>".$n_vendas_atimo."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_atimo*0.04), 2, ',', '.')."</b></td>";
                            $lineCsv[] = number_format(($total_vendas_atimo*0.04), 2, ',', '.');
                        }

                        $lineCsv[] = number_format(($total_vendas_mes_atimo), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_atimo/(($n_dias_atimo>0)?$n_dias_atimo:1)), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_mes_atimo/(($iday>0)?$iday:1)), 2, ',', '.');

                        echo "<td align='center'>".number_format(($total_vendas_mes_atimo), 2, ',', '.')."</b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format(($total_vendas_atimo/(($n_dias_atimo>0)?$n_dias_atimo:1)), 2, ',', '.')."</td>";
                        echo "<td align='center'>".number_format(($total_vendas_mes_atimo/(($iday>0)?$iday:1)), 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_atimo*0.04/(($n_dias_atimo>0)?$n_dias_atimo:1)), 2, ',', '.')."</td>";
                            $lineCsv[] = number_format(($total_vendas_atimo*0.04/(($n_dias_atimo>0)?$n_dias_atimo:1)), 2, ',', '.');
                        }

                        $lineCsv[] = number_format($total_proj_atimo, 2, ',', '.');
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format($total_proj_atimo, 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format($total_proj_atimo*0.04, 2, ',', '.')."</td>";
                            $lineCsv[] = number_format($total_proj_atimo*0.04, 2, ',', '.');
                        }
                        
                        $calc_result = ($media_TOTAOS_media) ? number_format((100*$total_proj_atimo/($days_in_month*$media_TOTAIS_media)), 2, ',', '.') : 0;
                        
                        $lineCsv[] = $calc_result;
                        echo "<td align='center'>".$calc_result."</td>";
                        echo "</tr>";
                        $total_percent += ($media_TOTAOS_media) ? (100*$total_proj_atimo/($days_in_month*$media_TOTAIS_media)) : 0;
                        $total_proj += $total_proj_atimo;

                        if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));
                        $lineCsv = array();
						//fim atimo

                        $lineCsv[] = "MoneyEx";
                        $lineCsv[] = $n_dias_MoneyEx;
                        $lineCsv[] = number_format(($total_vendas_MoneyEx), 2, ',', '.');
                        $lineCsv[] = $n_vendas_MoneyEx;
                        // Dados MoneyEx
                        $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                        $total_proj_MoneyEx = $total_vendas_mes_MoneyEx + (($days_in_month-$iday) * $total_vendas_MoneyEx_media / (($n_dias_MoneyEx_media>0) ? $n_dias_MoneyEx_media : 1));
                        echo "<tr class='trListagem'>"
                                . "<td align='center'>MoneyEx</td>"
                                . "<td align='center'>$n_dias_MoneyEx</td>"
                                . "<td align='center'>".number_format(($total_vendas_MoneyEx), 2, ',', '.')."</td>"
                                . "<td align='center'>".$n_vendas_MoneyEx."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_MoneyEx*0.04), 2, ',', '.')."</b></td>";
                            $lineCsv[] = number_format(($total_vendas_MoneyEx*0.04), 2, ',', '.');
                        }

                        $lineCsv[] = number_format(($total_vendas_mes_MoneyEx), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_MoneyEx/(($n_dias_MoneyEx>0)?$n_dias_MoneyEx:1)), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_mes_MoneyEx/(($iday>0)?$iday:1)), 2, ',', '.');

                        echo "<td align='center'>".number_format(($total_vendas_mes_MoneyEx), 2, ',', '.')."</b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format(($total_vendas_MoneyEx/(($n_dias_MoneyEx>0)?$n_dias_MoneyEx:1)), 2, ',', '.')."</td>";
                        echo "<td align='center'>".number_format(($total_vendas_mes_MoneyEx/(($iday>0)?$iday:1)), 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_MoneyEx*0.04/(($n_dias_MoneyEx>0)?$n_dias_MoneyEx:1)), 2, ',', '.')."</td>";
                            $lineCsv[] = number_format(($total_vendas_MoneyEx*0.04/(($n_dias_MoneyEx>0)?$n_dias_MoneyEx:1)), 2, ',', '.');
                        }

                        $lineCsv[] = number_format($total_proj_MoneyEx, 2, ',', '.');
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format($total_proj_MoneyEx, 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format($total_proj_MoneyEx*0.04, 2, ',', '.')."</td>";
                            $lineCsv[] = number_format($total_proj_MoneyEx*0.04, 2, ',', '.');
                        }
                        
                        $calc_result = ($media_TOTAIS_media) ? number_format((100*$total_proj_MoneyEx/($days_in_month*$media_TOTAIS_media)), 2, ',', '.') : 0;

                        $lineCsv[] = $calc_result;
                        echo "<td align='center'>".$calc_result."</td>";
                        echo "</tr>";
                        $total_percent += ($media_TOTAIS_media) ? (100*$total_proj_MoneyEx/($days_in_month*$media_TOTAIS_media)) : 0;
                        $total_proj += $total_proj_MoneyEx;

                        if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));

                        $lineCsv = array();
                        $lineCsv[] = "LHMoney";
                        $lineCsv[] = $n_dias_LHMoney;
                        $lineCsv[] = number_format(($total_vendas_LHMoney), 2, ',', '.');
                        $lineCsv[] = $n_vendas_LHMoney;

                        // Dados LHMoney
                        $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                        $total_proj_LHMoney = $total_vendas_mes_LHMoney + (($days_in_month-$iday) * $total_vendas_LHMoney_media / (($n_dias_LHMoney_media>0) ? $n_dias_LHMoney_media : 1));
                        echo "<tr class='trListagem'>"
                                . "<td align='center'>LHMoney</td>"
                                . "<td align='center'>$n_dias_LHMoney</td>"
                                . "<td align='center'>".number_format(($total_vendas_LHMoney), 2, ',', '.')."</td>"
                                . "<td align='center'>".$n_vendas_LHMoney."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_LHMoney*0.04), 2, ',', '.')."</b></td>";
                            $lineCsv[] = number_format(($total_vendas_LHMoney*0.04), 2, ',', '.');
                        }

                        $lineCsv[] = number_format(($total_vendas_mes_LHMoney), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_LHMoney/(($n_dias_LHMoney>0)?$n_dias_LHMoney:1)), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_mes_LHMoney/(($iday>0)?$iday:1)), 2, ',', '.');

                        echo "<td align='center'>".number_format(($total_vendas_mes_LHMoney), 2, ',', '.')."</b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format(($total_vendas_LHMoney/(($n_dias_LHMoney>0)?$n_dias_LHMoney:1)), 2, ',', '.')."</td>";
                        echo "<td align='center'>".number_format(($total_vendas_mes_LHMoney/(($iday>0)?$iday:1)), 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_LHMoney*0.04/(($n_dias_LHMoney>0)?$n_dias_LHMoney:1)), 2, ',', '.')."</td>";
                            $lineCsv[] = number_format(($total_vendas_LHMoney*0.04/(($n_dias_LHMoney>0)?$n_dias_LHMoney:1)), 2, ',', '.');
                        }

                        $lineCsv[] = number_format($total_proj_LHMoney, 2, ',', '.');
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format($total_proj_LHMoney, 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format($total_proj_LHMoney*0.04, 2, ',', '.')."</td>";
                            $lineCsv[] = number_format($total_proj_LHMoney*0.04, 2, ',', '.');
                        }
                        
                        $calc_result = ($media_TOTAIS_media) ? number_format((100*$total_proj_LHMoney/($days_in_month*$media_TOTAIS_media)), 2, ',', '.') : 0;

                        $lineCsv[] = $calc_result;
                        echo "<td align='center'>".$calc_result."</td>";
                        echo "</tr>";
                        $total_percent += ($media_TOTAIS_media) ? (100*$total_proj_LHMoney/($days_in_month*$media_TOTAIS_media)) : 0;
                        $total_proj += $total_proj_LHMoney;

                        // Dados Cartoes
                        $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                //echo "days_in_month: $days_in_month, iday: $iday, n_dias_Cartoes_media: $n_dias_Cartoes_media<br>";
                //echo "total_vendas_Cartoes: $total_vendas_Cartoes<br>";
                //echo "total_vendas_Cartoes_media: $total_vendas_Cartoes_media<br>";

                        if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));

                        $lineCsv = array();
                        $lineCsv[] = "Cartoes";
                        $lineCsv[] = $n_dias_Cartoes;
                        $lineCsv[] = number_format(($total_vendas_Cartoes), 2, ',', '.');
                        $lineCsv[] = $n_vendas_Cartoes;

                        $total_proj_Cartoes = $total_vendas_mes_Cartoes + (($days_in_month-$iday) * $total_vendas_Cartoes_media / (($n_dias_Cartoes_media>0) ? $n_dias_Cartoes_media : 1));
                        echo "<tr class='trListagem'>"
                                . "<td align='center'>Cartoes</td>"
                                . "<td align='center'>$n_dias_Cartoes</td>"
                                . "<td align='center'>".number_format(($total_vendas_Cartoes), 2, ',', '.')."</td>"
                                . "<td align='center'>".$n_vendas_Cartoes."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_Cartoes*0.04), 2, ',', '.')."</b></td>";
                            $lineCsv[] = number_format(($total_vendas_Cartoes*0.04), 2, ',', '.');
                        }

                        $lineCsv[] = number_format(($total_vendas_mes_Cartoes), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_Cartoes/(($n_dias_Cartoes>0)?$n_dias_Cartoes:1)), 2, ',', '.');
                        $lineCsv[] = number_format(($total_vendas_mes_Cartoes/(($iday>0)?$iday:1)), 2, ',', '.');

                        echo "<td align='center'>".number_format(($total_vendas_mes_Cartoes), 2, ',', '.')."</b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format(($total_vendas_Cartoes/(($n_dias_Cartoes>0)?$n_dias_Cartoes:1)), 2, ',', '.')."</td>";
                        echo "<td align='center'>".number_format(($total_vendas_mes_Cartoes/(($iday>0)?$iday:1)), 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format(($total_vendas_Cartoes*0.04/(($n_dias_Cartoes>0)?$n_dias_Cartoes:1)), 2, ',', '.')."</td>";
                            $lineCsv[] = number_format(($total_vendas_Cartoes*0.04/(($n_dias_Cartoes>0)?$n_dias_Cartoes:1)), 2, ',', '.');
                        }

                        $lineCsv[] = number_format($total_proj_Cartoes, 2, ',', '.');
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'>".number_format($total_proj_Cartoes, 2, ',', '.')."</td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'>".number_format($total_proj_Cartoes*0.04, 2, ',', '.')."</td>";
                            $lineCsv[] = number_format($total_proj_Cartoes*0.04, 2, ',', '.');
                        }
                        
                        $calc_result = ($media_TOTAIS_media) ? number_format((100*$total_proj_Cartoes/($days_in_month*$media_TOTAIS_media)), 2, ',', '.') : 0;
                        
                        $lineCsv[] = $calc_result;
                        echo "<td align='center'>".$calc_result."</td>";
                        echo "</tr>";
                        $total_percent += ($media_TOTAIS_media) ? (100*$total_proj_Cartoes/($days_in_month*$media_TOTAIS_media)) : 0;
                        $total_proj += $total_proj_Cartoes;

                        if(is_array($lineCsv)) $objCsv->setLine(implode(";",$lineCsv));

                        $csv = $objCsv->export();

                        
                        // Dados TOTAIS
                        $bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
                        echo "<tr><td colspan='{$colspan}'>&nbsp;</td></tr>";
                        echo "<tr><td align='center'><b>".LANG_STATISTICS_TOTALS." ";
                        if(strlen($_SESSION["opr_nome"])>0) echo "".$_SESSION["opr_nome"]." ";
                        echo "&nbsp;</b></td><td align='center'> &nbsp;&nbsp; </td><td align='center'><b> ".number_format(($total_vendas_TOTAIS), 2, ',', '.')." </b></td><td align='center'> <b>$n_vendas_TOTAIS</b> </td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'><b> ".number_format(($total_vendas_TOTAIS*0.04), 2, ',', '.')." </b></td>";
                        }
                        echo "<td align='center'><b> ".number_format(($total_vendas_mes_TOTAIS), 2, ',', '.')." </b></td>";
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                        echo "<td align='center'><b> ".number_format($media_TOTAIS, 2, ',', '.')." </b></td>";
                        echo "<td align='center'><b> ".number_format($media_mes_TOTAIS, 2, ',', '.')." </b></td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'><b> ".number_format($media_TOTAIS*0.04, 2, ',', '.')." </b></td>";
                        }
                        echo "<td align='center'> &nbsp;&nbsp; </td>";
                //	$total_proj = $days_in_month*$media_TOTAIS_media; ???
                        echo "<td align='center'><b> ".number_format($total_proj, 2, ',', '.')." </b></td>";
                        if(strlen($dd_operadora)==0)
                        {
                            echo "<td align='center'><b> ".number_format($total_proj*0.04, 2, ',', '.')." </b></td>";
                        }

                        $bColor = ($total_percent>=100)?"#0000FF":"#FF0000";
                        echo "<td align='center'><b><font color=\"$bColor\">".number_format($total_percent, 2, ',', '.')."</font></b></td>";
                        echo "</tr>";
                        
                        if(isset($csv))
                        {
                            echo "<tr>";
                            echo    '<td colspan="'.$colspan.'" class="text-center"><a href="/includes/downloadCsv.php?csv='.$csv.'&dir=cache"><input class="btn downloadCsv btn-info " type="button" value="Download CSV"></a></td>';
                            echo '</tr>';
                        } 
                        // Fim ========================================================================================
                        echo "</table></div>";

                ?>
                <table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
                  <tr> 
                        <td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_INFO_MSG; ?> <?php if(strlen($dd_operadora)==0) { ?><?php echo LANG_STATISTICS_INFO_MSG_1; ?> <?php } else { ?><?php echo LANG_STATISTICS_INFO_MSG_2; ?> <?php } ?><?php echo LANG_STATISTICS_INFO_MSG_3; ?>  <br>&nbsp; </font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
                  </tr>
                  <tr align="center"> 
                        <td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
                  </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body>
</html>

