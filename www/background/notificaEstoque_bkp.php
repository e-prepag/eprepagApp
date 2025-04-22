<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(10000);
ini_set('max_execution_time', 10000); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

     $file = fopen("/www/log/log_notifica_estoque.txt", "a+");
     fwrite($file, "INICIO DE EXECUÇÃO : ".date("m-d-Y H:i:s")."\n");
    /**
     * Início do procedimento
     */
    $days_for_mean = 7;
    $prazo_amarelo_vezes = 2;
    $prazo_vermelho_vezes = 1;
    $fcanal = 's';
    $ncamp = 'opr_nome';
    $quantidade_total = 0;
    
    //Busca Operadoras exceto Brasil Telecom
	$sql = "select t1.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, (CASE WHEN t0.opr_codigo <> 78 THEN sum(t0.pin_valor) ELSE 0 END) as total_face, t0.opr_codigo, t0.pin_status, t1.opr_pedido_estoque_prazo as prazo_pedido ";
	$sql .= "from pins t0, operadoras t1 ";
	$sql .= "where t0.opr_codigo <> 32 and t1.opr_codigo <> 32 and t1.opr_pin_online = 0 ";
	$sql .= "and pin_status='1' ";
	$sql .= "and (t0.pin_canal='".$fcanal."') ";
	$sql .= "and (t0.opr_codigo=t1.opr_codigo) ";
	$sql .= "group by t1.opr_faturamento_ordem, t1.opr_nome, t0.pin_valor, t0.opr_codigo, t0.pin_status, t1.opr_pedido_estoque_prazo ";
	$sql .= "order by opr_nome, ".$ncamp.", pin_valor, pin_status"; 
    
	$resestat = pg_exec($connid, $sql);
    
    $sqlMedia = "select t1.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, (CASE WHEN t0.opr_codigo <> 78 THEN sum(t0.pin_valor) ELSE 0 END) as total_face, t0.opr_codigo, t1.opr_pedido_estoque_prazo as prazo_pedido ";
    $sqlMedia .= "from pins t0, operadoras t1 ";
    $sqlMedia .= "where t0.opr_codigo <> 32 and t1.opr_codigo <> 32 and t1.opr_pin_online = 0 ";
    // Se procurar por pins de POS apresenta apenas o status 7 - 'Vendido - POS', caso contrario apresenta apenas 3 - 'Vendido' e 6 - 'Vendido – Lan House'
    $sqlMedia .= " and (pin_status='3' or pin_status='6' or pin_status='8')";
    $sqlMedia .= " and (pin_datavenda >='" . date("Y-m-d",strtotime("now -6 days")) . "' and pin_datavenda <='".date("Y-m-d",strtotime("now"))."') ";	
    //if($fcanal=='s' || $fcanal=='p' || $fcanal=='r'){
    $sqlMedia .= "and (t0.pin_canal='".$fcanal."') "; 
    //}
    $sqlMedia .= " and (t0.opr_codigo=t1.opr_codigo) ";
    $sqlMedia .= "group by t1.opr_nome, t0.pin_valor, t0.opr_codigo, t1.opr_pedido_estoque_prazo ";
    $sqlMedia .= "order by ".$ncamp.", pin_valor"; 
    
    $rs_Media = pg_exec($connid, $sqlMedia);
    
    //Esgotados
	$sql  = "select distinct operadoras.opr_nome, pins.opr_codigo, pins.pin_valor from pins inner join operadoras ";
	$sql .= "on pins.opr_codigo = operadoras.opr_codigo ";
	$sql .= "where operadoras.opr_status='1' and operadoras.opr_pin_online = 0 ";
	if($fcanal=='s' || $fcanal=='p' || $fcanal=='r'){ $sql .= "and (pins.pin_canal='".$fcanal."') "; }
	$sql .= "and (not (operadoras.opr_codigo=17 and pins.pin_valor=26)) ";	// Não conta Mu Online - 26,00
	$sql .= "except ";
	$sql .= "select distinct operadoras.opr_nome, pins.opr_codigo, pins.pin_valor from pins inner join operadoras ";
	$sql .= "on pins.opr_codigo = operadoras.opr_codigo ";
	$sql .= "where operadoras.opr_status='1' and operadoras.opr_pin_online = 0 and pins.pin_status = '1' ";
	if($fcanal=='s' || $fcanal=='p' || $fcanal=='r'){ $sql .= "and (pins.pin_canal='".$fcanal."') "; }
    $sql .= " order by opr_nome, pin_valor; ";
	
//echo "sqlEsgotados: $sql<br>";
    $rs_esgotados = pg_exec($connid, $sql);
    
    $estoqueBaixo = array();
    
    while ($pgestat = pg_fetch_array($resestat)) 
    {
        $prazo_pedido = $pgestat['prazo_pedido'];
        $executa = false;
        $quantidade_total = 0;
        
        if($rs_Media && pg_num_rows($rs_Media) > 0) 
            pg_fetch_array($rs_Media,0);
        
        $media = 0;
        
        while($pgmediaopr=pg_fetch_array($rs_Media)) 
        {
            if($pgmediaopr['opr_nome']==$pgestat['opr_nome'] && $pgmediaopr['pin_valor']==$pgestat['pin_valor']) 
            {

                $executa=true;
                $quantidade_total += $pgmediaopr['quantidade'];
            }
        }

                            // tem pins vendidos no período
        if($executa) 
        { 
            $media = $quantidade_total/$days_for_mean;
                                
            $dias = floor($pgestat['quantidade']/(($media>0)?$media:1));

            #atribuição da variavel de controle
            $prazo_pedido = $pgestat['prazo_pedido'];

            
            #condição para enviar e-mail
            if($dias<=$prazo_vermelho_vezes*$prazo_pedido || $dias<=($prazo_amarelo_vezes*$prazo_pedido))
            {
                echo "bg-vermelho: {$pgestat['opr_nome']} - {$pgestat['pin_valor']} - {$dias} dia(s) \n";
                $estoqueBaixo[] = "<p style='color:#caca0e'> {$pgestat['opr_nome']} - {$pgestat['pin_valor']} - {$dias} dia(s) </p>";
            }
        }
    }
    
    while ($pgest = pg_fetch_array($rs_esgotados))
    {
        $sql = "select count(*) as total_lan
                from tb_dist_operadora_games_produto dogp 
                inner join tb_dist_operadora_games_produto_modelo dogpm on dogp.ogp_id =dogpm.ogpm_ogp_id
                where dogp.ogp_opr_codigo = ".$pgest['opr_codigo']."
                        and dogpm.ogpm_valor = ".$pgest['pin_valor']." 
                        and dogpm.ogpm_ativo = 1
                        and dogp.ogp_pin_request=0;";
        $rs_count_lan = pg_exec($connid, $sql);
        $rs_count_lan_row = pg_fetch_array($rs_count_lan);
        $sql = "select count(*) as total_gamer
                from tb_operadora_games_produto ogp
                inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id =ogpm.ogpm_ogp_id
                where ogp.ogp_opr_codigo = ".$pgest['opr_codigo']."
                        and ogpm.ogpm_valor = ".$pgest['pin_valor']."
                        and ogpm.ogpm_ativo = 1
                        and ogp.ogp_pin_request=0;";
        $rs_count_gamer = pg_exec($connid, $sql);
        $rs_count_gamer_row = pg_fetch_array($rs_count_gamer);
        
        if($rs_count_gamer_row['total_gamer'] != 0 || $rs_count_lan_row['total_lan'] != 0) {
            echo "sem estoque: {$pgest['opr_nome']} - " .number_format($pgest['pin_valor'], 2, ',', '.')."\n";
            $estoqueBaixo[] =  "<p style='color:#f92a29;'> sem estoque {$pgest['opr_nome']} - " .number_format($pgest['pin_valor'], 2, ',', '.')." </p>";
        }
    }
    
    
    if(!empty($estoqueBaixo))
    {
        if(!isset($_SERVER['SERVER_NAME']))
            $_SERVER['SERVER_NAME'] = '';
        
		if(function_exists('check_ip')) {
			if(!checkIP()) //producao
        {
            $to = "daniela.oliveira@e-prepag.com.br";
			$cc = "suporte@e-prepag.com.br, glaucia@e-prepag.com.br";
            $subject = "[PRODUÇÃO] - ESTOQUE BAIXO";
			}else{
				$to = "estagiario1@e-prepag.com, daniela.oliveira@e-prepag.com.br";
				$subject = "[DEV - HOMOLOGAÇÃO] - ".$_SERVER['SERVER_NAME']." - ESTOQUE BAIXO";
				$cc = "";
			}
		} 
		else {	        
			$to = "daniela.oliveira@e-prepag.com.br";
			$cc = "suporte@e-prepag.com.br,glaucia@e-prepag.com.br";
			$subject = "[PRODUÇÃO] - ESTOQUE BAIXO";
		}	
		
        $body_html = "<strong>Data</strong>: ".date("d/m/Y H:i:s").". <br> ";
        $body_html .= "<div style='background-color:#eee'>".implode("",$estoqueBaixo)."</div>";

        enviaEmail($to, $cc, null, $subject, $body_html, null);
    }
    else
    {
        echo "Todos os estoques estão em dia.";
    } 
    
    echo str_repeat("=", 80).PHP_EOL." Fim - ".date('Y-m-d H:i:s').PHP_EOL.str_repeat("=", 80).PHP_EOL;
    
    /**
     * Fim do Procedimento
     */
	 fwrite($file, "FIM DE EXECUÇÃO : ".date("m-d-Y H:i:s")."\n");
	 fwrite($file, str_repeat("*", 50)."\n");
	 fclose($file);

//Fechando Conexão
pg_close($connid);

?>