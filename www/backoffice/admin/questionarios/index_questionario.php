<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

// fim trecho de teste

$acao	= isset($_REQUEST['acao']) ? $_REQUEST['acao'] : 'listar';

$msg	= "";

$formatos = array('jpg','jpeg','gif','png');

$tipos = array(
				'1' => "Bloqueio",
				'2' => "Aviso em p&aacute;gina (todas as vezes)",
				'3' => "Aviso em p&aacute;gina uma vez ao dia",
				'4' => "Aviso em banner na p&aacute;gina",
				);

$tipos_usuarios = array(
				'L' => "Usu&aacute;rios Lan House",
				'G' => "Usu&aacute;rios Gamers",
				);

if(isset($_SESSION['userlogin_bko']) && !is_null($_SESSION['userlogin_bko'])){
	$quest_usuario_bko = strtoupper($_SESSION['userlogin_bko']);
}

if($acao == 'inserir')
{
	$ext	= explode('/', isset($_FILES['quest_banner']['type']) ? $_FILES['quest_banner']['type'] : '');

	if(isset($ext[1]) && in_array($ext[1],$formatos)) {
		$pasta = "/www/arquivos_gerados/imagens/questionario/";
		if(file_exists("$pasta".$_FILES["quest_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor, renomear antes.<br>";
			$quest_banner = null;
		}
		else {
			move_uploaded_file($_FILES["quest_banner"]["tmp_name"],"$pasta".$_FILES["quest_banner"]["name"]);
			$quest_banner = $_FILES["quest_banner"]["name"];
		}
	}
	//else $msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
	
/*
tb_questionarios

ql_id_questionario serial NOT NULL, -- Id de identificação do questionário nesta tabela
ql_data_inicio timestamp with time zone NOT NULL, -- Campo contendo a data de inicio da vigência do questionário.
ql_data_fim timestamp with time zone NOT NULL, -- Campo contendo a data final da vigência do questionário.
ql_tipo smallint NOT NULL, -- Campo contendo o tipo de aviso quando o usuário logar no site....
ql_lista_ids_inclusao text, -- Campo contendo os ug_id dos usuários que devem ser considerados para este questionário.
ql_lista_ids_exclusao text, -- Campo contendo os ug_id dos usuários que NÃO devem ser considerados para este questionário.
ql_ativo smallint NOT NULL, -- Campo contendo a ativação do questionário. Onde 0 = Desativado e 1 = Ativado.
ql_usuario_bko_responsavel character varying(15) NOT NULL, -- Campo contendo o usuário responsável pelo questionário, equivalente ao campo shn_login da tabela shn_login.
ql_imagem_banner character varying(256), -- Campo contendo o banner utilizado no questionário como indicador deste.
ql_texto character varying(256) NOT NULL, -- Campo contendo uma descrição para o questionário que será usado como título deste.
ql_tipo_usuario character varying(1) NOT NULL DEFAULT 'L'::character varying, -- Campo contendo o tipo de usuário que responderá o questionário. Legenda: G = Usuários Gamers; L = Usuários Lan House.

*/
	$sql = "INSERT INTO tb_questionarios (
							ql_data_inicio, 
							ql_data_fim, 
							ql_tipo, 
							ql_usuario_bko_responsavel,
							ql_texto,
							ql_tipo_usuario,
							ql_imagem_banner,
							ql_lista_ids_inclusao,
							ql_lista_ids_exclusao,
							ql_ativo
							) 
					VALUES (
							to_date($1,'DD/MM/YYYY'), 
							to_date($2,'DD/MM/YYYY'), 
							$3,
							$4,
							$5, 
							$6,
							$7,
							$8,
							$9,
							$10);";
	$params = array(
		$quest_data_inicio,
		$quest_data_fim,
		$quest_tipo,
		$quest_usuario_bko,
		str_replace("'", '"', $quest_nome_update),
		$quest_tipo_usuario,
		empty($quest_banner) ? null : $quest_banner,
		empty($quest_ids_inclusao) ? null : trim((string)($quest_ids_inclusao ?? '')),
		empty($quest_ids_exclusao) ? null : trim((string)($quest_ids_exclusao ?? '')),
		empty($quest_ativo) ? 0 : 1
	);
	//echo $sql."<br>";
	$rs_questionario = SQLexecuteQueryParams($sql, $params);
	if(!$rs_questionario) {
		$msg .= "Erro ao salvar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
		$acao = 'listar';
	}
	else {
		$sql = "select MAX(ql_id_questionario) as id from tb_questionarios";
		//echo $sql."<br>";
		$rs_max = SQLexecuteQuery($sql);
		if(!($rs_max_row = pg_fetch_array($rs_max))) {
			$msg .= "Erro ao capturar o ultimo inserido. ($sql)<br>";
			$acao = 'listar';
		}
		else{
			$quest_id	= $rs_max_row['id'];
			$acao = 'editar';
		}
	}//end else if(!$rs_questionario) 
}//end if($acao == 'inserir')

if($acao == 'atualizar')
{
	if(!empty($vetor_ordem)) {
		//echo $vetor_ordem."<br>";
		$vetor_ordem = explode(";", $vetor_ordem);
		foreach ($vetor_ordem as $key => $value) {
			list($indice,$pergunta) = explode(":", $value);
			$vetor_ordem_preparado[$indice] = $pergunta; 
		}
		reset($vetor_ordem_preparado);
		//echo "<pre>".print_r($vetor_ordem_preparado,true)."</pre>";
		//buscar pelo id do questionario todas as perguntas 
		$sql = "select qlp_id from tb_questionarios_perguntas where ql_id_questionario=".$quest_id_update." order by qlp_ativo DESC,qlp_ordem";
		//echo $sql."<br>";
		$rs_perguntas = SQLexecuteQuery($sql);
		while($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
			$vetor_ordem_selecao[] = $rs_perguntas_row["qlp_id"];
		}//end while
		reset($vetor_ordem_selecao);
		//echo "<pre>".print_r($vetor_ordem_selecao,true)."</pre>";
		foreach ($vetor_ordem_preparado as $key => $value) {
			$sql = "update tb_questionarios_perguntas set qlp_ordem=".$value." where qlp_id=".$vetor_ordem_selecao[$key];
			//echo $sql."<br>";
			$rs_questionario = SQLexecuteQuery($sql);
			if(!$rs_questionario) {
				$msg .= "Erro ao salvar a ordena&ccedil;&atilde;o da question&aacute;rio. ($sql)<br>";
			}
		}//end foreach
	}//end if(!empty($vetor_ordem))
	
	if(!empty($_FILES["quest_banner"]["name"])) {
		$ext	= explode('/', isset($_FILES['quest_banner']['type']) ? $_FILES['quest_banner']['type'] : '');
		$pasta = "/www/arquivos_gerados/imagens/questionario/";
		if(file_exists("$pasta".$_FILES["quest_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor renomear antes.<br>";
			$quest_banner = null;
		}
		else {
			move_uploaded_file($_FILES["quest_banner"]["tmp_name"],"$pasta".$_FILES["quest_banner"]["name"]);
			$quest_banner = $_FILES["quest_banner"]["name"];
		}
		if(!isset($ext[1]) || !in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
		}
	}
	$sql = "UPDATE tb_questionarios SET
						ql_texto					= $1,
						ql_data_inicio				= to_date($2,'DD/MM/YYYY'),           
						ql_data_fim					= to_date($3,'DD/MM/YYYY'),
						ql_tipo						= $4,
						ql_tipo_usuario				= $5,
						ql_usuario_bko_responsavel	= $6,
						ql_lista_ids_inclusao		= $7,
						ql_lista_ids_exclusao		= $8,";
	$params = array(
		str_replace("'", '"', $quest_nome_update),
		$quest_data_inicio,
		$quest_data_fim,
		$quest_tipo,
		$quest_tipo_usuario,
		$quest_usuario_bko,
		trim((string)($quest_ids_inclusao ?? '')),
		trim((string)($quest_ids_exclusao ?? ''))
	);
	if (!empty($quest_banner)) {
		$params[] = $quest_banner;
		$sql .= "		ql_imagem_banner			= $" . count($params) . ",";
	}
	$params[] = empty($quest_ativo) ? 0 : 1;
	$sql .= "		ql_ativo					= $" . count($params);
	$params[] = $quest_id_update;
	$sql .= "	WHERE	ql_id_questionario			= $" . count($params);
	//echo $sql."<br>:SQL<br>";
	$rs_questionario = SQLexecuteQueryParams($sql, $params);
	if(!$rs_questionario) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es da question&aacute;rio ID:($quest_id_update).<br>";
	}

	//Atualizando as repostas ativas
	if (isset($qlpr_ativo) && (is_countable($qlpr_ativo) ? count($qlpr_ativo) : 0)>0) {
		if ($qlpr_ativo['0']<>0) {
			//removendo todos os ativos
			$sql = "update tb_questionarios_perguntas_respostas qpr set qlpr_ativo=0 from tb_questionarios_perguntas qp where qpr.qlp_id=qp.qlp_id and qp.ql_id_questionario=".$quest_id_update;
			$rs_questionario_perguntas = SQLexecuteQuery($sql);
			if(!$rs_questionario_perguntas) {
				$msg .= "Erro ao remover informa&ccedil;&otilde;es de ativo. ($sql)<br>";
			}
			else {
				//ativando somente os selecionados
				$aux_qlpr_id = "";
				foreach ($qlpr_ativo as $key => $value) {
					if (empty($aux_qlpr_id)) {
						$aux_qlpr_id .= $value;
					}
					else {
						$aux_qlpr_id .= ",".$value;
					}
				}//end foreach
				//echo $aux_qlpr_id." :IDS R<br>";
				$sql = "update tb_questionarios_perguntas_respostas set qlpr_ativo=1 where qlpr_id IN (".$aux_qlpr_id.")";
				$rs_questionario_perguntas = SQLexecuteQuery($sql);
				if(!$rs_questionario_perguntas) {
					$msg .= "Erro ao ativar informa&ccedil;&otilde;es de ativo. ($sql)<br>";
				}
			}//end else if(!$rs_questionario_perguntas) removendo os ativos
		}//end if ($qlpr_ativo[0]<>0) 
	}//end if ((is_countable($qlpr_ativo) ? count($qlpr_ativo) : 0)>0)
	else {
		//removendo todos os ativos
		$sql = "select count(*) as total from tb_questionarios_perguntas_respostas qpr INNER JOIN tb_questionarios_perguntas qp ON (qpr.qlp_id=qp.qlp_id) where qp.ql_id_questionario=".$quest_id_update;
		$rs_questionario_perguntas = SQLexecuteQuery($sql);
		if(!($rs_questionario_perguntas_row = pg_fetch_array($rs_questionario_perguntas))) {
			$msg .= "Erro ao consultar respostas do question&aacute;rio. ($sql)<br>";
		}
		else {
			$total	= $rs_questionario_perguntas_row['total'];
			if ($total > 0) {
					//removendo todos os ativos
					$sql = "update tb_questionarios_perguntas_respostas qpr set qlpr_ativo=0 from tb_questionarios_perguntas qp where qpr.qlp_id=qp.qlp_id and qp.ql_id_questionario=".$quest_id_update;
					$rs_questionario_perguntas = SQLexecuteQuery($sql);
					if(!$rs_questionario_perguntas) {
						$msg .= "Erro ao remover informa&ccedil;&otilde;es de ativo. ($sql)<br>";
					}//end if(!$rs_questionario_perguntas)
			}//end if ($rs_questionario_perguntas_row > 0)
		}//end else if(!($rs_questionario_perguntas_row = pg_fetch_array($rs_questionario_perguntas)))
	}//end else if ((is_countable($qlpr_ativo) ? count($qlpr_ativo) : 0)>0)

	//Atualizando as perguntas ativas
	if (isset($qlp_ativo) && (is_countable($qlp_ativo) ? count($qlp_ativo) : 0)>0) {
		//removendo todos os ativos
		$sql = "update tb_questionarios_perguntas set qlp_ativo=0 where ql_id_questionario=".$quest_id_update;
		$rs_questionario_perguntas = SQLexecuteQuery($sql);
		if(!$rs_questionario_perguntas) {
			$msg .= "Erro ao remover informa&ccedil;&otilde;es de ativo. ($sql)<br>";
		}
		else {
			//ativando somente os selecionados
			$aux_qlp_id = "";
			foreach ($qlp_ativo as $key => $value) {
				if (empty($aux_qlp_id)) {
					$aux_qlp_id .= $value;
				}
				else {
					$aux_qlp_id .= ",".$value;
				}
			}//end foreach
			//echo $aux_qlp_id." :IDS<br>";
			$sql = "update tb_questionarios_perguntas set qlp_ativo=1 where qlp_id IN (".$aux_qlp_id.")";
			$rs_questionario_perguntas = SQLexecuteQuery($sql);
			if(!$rs_questionario_perguntas) {
				$msg .= "Erro ao ativar informa&ccedil;&otilde;es de ativo. ($sql)<br>";
			}
		}//end else if(!$rs_questionario_perguntas) removendo os ativos
	}//end if ((is_countable($qlp_ativo) ? count($qlp_ativo) : 0)>0)
	else {
		//removendo todos os ativos
		$sql = "select count(*) as total from tb_questionarios_perguntas where ql_id_questionario=".$quest_id_update;
		$rs_questionario_perguntas = SQLexecuteQuery($sql);
		if(!($rs_questionario_perguntas_row = pg_fetch_array($rs_questionario_perguntas))) {
			$msg .= "Erro ao consultar respostas do question&aacute;rio. ($sql)<br>";
		}
		else {
			$total	= $rs_questionario_perguntas_row['total'];
			if ($total > 0) {
					//removendo todos os ativos
					$sql = "update tb_questionarios_perguntas set qlp_ativo=0 where ql_id_questionario=".$quest_id_update;
					$rs_questionario_perguntas = SQLexecuteQuery($sql);
					if(!$rs_questionario_perguntas) {
						$msg .= "Erro ao remover informa&ccedil;&otilde;es de ativo. ($sql)<br>";
					}//end if(!$rs_questionario_perguntas)
			}//end if ($rs_questionario_perguntas_row > 0)
		}//end else if(!($rs_questionario_perguntas_row = pg_fetch_array($rs_questionario_perguntas)))
	}//end else if ((is_countable($qlpr_ativo) ? count($qlpr_ativo) : 0)>0)

	
	//isset($_REQUEST['quest_id']);
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT 
					ql_id_questionario,
					to_char(ql_data_inicio,'DD/MM/YYYY') as quest_data_inicio,
					to_char(ql_data_fim,'DD/MM/YYYY') as quest_data_fim,
					ql_tipo,
					ql_lista_ids_inclusao,
					ql_lista_ids_exclusao,
					ql_ativo,
					ql_usuario_bko_responsavel,
					ql_imagem_banner,
					ql_texto,
					ql_tipo_usuario
			FROM tb_questionarios 
			WHERE ql_id_questionario = $quest_id"; 
	//echo $sql."<br>";
	$rs_questionario = SQLexecuteQuery($sql);
	if(!($rs_questionario_row = pg_fetch_array($rs_questionario))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	else {
		$quest_id				= $rs_questionario_row['ql_id_questionario'];
		$quest_nome				= $rs_questionario_row['ql_texto'];
		$quest_data_inicio		= $rs_questionario_row['quest_data_inicio'];
		$quest_data_fim			= $rs_questionario_row['quest_data_fim'];
		$quest_tipo				= $rs_questionario_row['ql_tipo'];
		$quest_ids_inclusao		= $rs_questionario_row['ql_lista_ids_inclusao'];
		$quest_ids_exclusao		= $rs_questionario_row['ql_lista_ids_exclusao'];
		$quest_ativo			= $rs_questionario_row['ql_ativo'];
		$quest_usuario_bko		= $rs_questionario_row['ql_usuario_bko_responsavel'];
		$quest_banner			= $rs_questionario_row['ql_imagem_banner'];
		$quest_tipo_usuario		= $rs_questionario_row['ql_tipo_usuario'];
		if ((($rs_questionario) ? pg_num_rows($rs_questionario) : 0) > 0)
			include 'questionario_edt.php';
		else
			$acao = 'listar';
	}
}

if($acao == 'novo')
{
    include 'questionario_edt.php';
}

if($acao == 'listar')
{
    include 'questionario_lst.php';
}
//echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>