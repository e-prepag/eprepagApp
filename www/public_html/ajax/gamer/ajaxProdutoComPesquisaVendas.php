<?php
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."public_html/sys/includes/topo_sys_inc.php";
require_once __DIR__ . "/../../../db/connect.php"; 
require_once __DIR__ . "/../../../db/ConnectionPDO.php";

$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

if ($id > 0) {
    // Determina a tabela com base no referer
    $tb = (strpos($_SERVER['HTTP_REFERER'], 'dist_commerce') > 0)
        ? "tb_dist_operadora_games_produto"
        : "tb_operadora_games_produto";

    // Valida se a tabela é permitida (evita SQL Injection via nome de tabela)
    $tabelasPermitidas = ['tb_dist_operadora_games_produto', 'tb_operadora_games_produto'];
    if (!in_array($tb, $tabelasPermitidas)) {
        die("Tabela não permitida.");
    }

    // Conexão PDO
    try {
        $pdo = ConnectionPDO::getConnection()->getLink();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepara e executa a consulta com placeholder
        $sql = "SELECT ogp_id, ogp_nome FROM $tb WHERE ogp_opr_codigo = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $rs_oprProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Erro na conexão ou consulta: " . $e->getMessage());
    }
}

// Exibe os checkboxes se houver produtos
if (!empty($rs_oprProdutos)) {
    foreach ($rs_oprProdutos as $row) {
        $checked = '';
        if (isset($tf_produto) && is_array($tf_produto) && in_array($row['ogp_nome'], $tf_produto)) {
            $checked = ' checked';
        } elseif (isset($tf_produto) && $row['ogp_nome'] == $tf_produto) {
            $checked = ' checked';
        }

        echo '<nobr><input type="checkbox" id="tf_produto" name="tf_produto[]" value="' . 
             htmlspecialchars($row['ogp_nome']) . '"' . $checked . '>' . 
             str_replace(" ", "&nbsp;", utf8_encode($row['ogp_nome'])) . "</nobr>\n";
    }
}
?>
<script>
		
/*


		function reload_precos() {
		
		'NOOOOOO';

		var selectedItems = new Array();
	
		$("input[@name='tf_produto[]']:checked").each( function () { 
		selectedItems.push($(this).val());
		

		$.ajax({
				
			type: "POST",
			url: "../commerce/includes/ajaxTipoComPesquisaVendas.php",
		    data: 
				
				{id:<?=$id?>}
			,
beforeSend: function(){
					$('#mostraValores2').html("Aguarde...");
				},
				success: function(html){
					
					$('#mostraValores2').html(html);
				},
				error: function(){
					alert('erro ao carregar valores');
				}

				}); //fim ajax

					
		});

	
		}// fim function reload precos

		
		$("input[@name='tf_produto[]']:unchecked").change(function () { 
			
		reload_precos();

	//	alert('eittaa');
		
		}); 
		//	"input[@name='chkBox']"
		
		//alert(this.value);
		
		*/</script>