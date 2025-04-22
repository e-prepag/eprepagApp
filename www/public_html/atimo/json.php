<?php

ob_start(); 
set_time_limit(3600);
ini_set('max_execution_time', 3600); 
// include do arquivo contendo IPs DEV
$raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
$time_start_stats = getmicrotime();

//$arquivoLog = new ManipulacaoArquivosLog($argv);

//if(!$arquivoLog->haveFile()) {
   // $arquivoLog->createLockedFile();
    //$nome_arquivo = $arquivoLog->getNomeArquivo();

    //ob_start('callbackLog');
    echo PHP_EOL."Data execuчуo : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;
    
    //INICIO DO BLOCO
    require_once DIR_CLASS."util/Busca.class.php";
    //require_once $raiz_do_projeto."/www/web/prepag2/b2c/config.inc.b2c.php";
    //para voltar com os produtos b2c, descomente a linha acima
    
    //array com todos os produtos listados
    $arrProduto = array();

    $rs = null;
    $filtro['opr'] = 1;
    $filtro['opr_status'] = '1';
    $filtro['ogp_codigo_negado'] = 39;
    $filtro['ogp_mostra_integracao_gamer_com_loja'] = '1'; // Wagner
    $filtro['ogp_ativo'] = 1;
    $arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);
    
    $busca = new Busca;
    $busca->setFullPath(DIR_JSON);
    $busca->setArrJsonFiles($arrJsonFiles);

    $produto = new Produto();
    $ret = $produto->obterMelhorado($filtro, null, $rs);

    if($rs && pg_num_rows($rs) > 0)
    {
        for($i=0; $rs_row = pg_fetch_array($rs); $i++)
        {
            if(!empty($rs_row['ogp_nome']))
            {
                $produto                                    = new stdClass();
                $produto->tipo                              = "games";
                $produto->id                                = $rs_row['ogp_id'];
                $produto->nome                              = htmlentities(utf8_encode($rs_row['ogp_nome']));
                $produto->busca                             = htmlentities(strip_tags(Util::cleanStr2($rs_row['ogp_nome']." | ".$rs_row['opr_nome_loja']))); //corrigir traducao dew caracter q nao ta funfando
                $produto->imagem                            = $rs_row['ogp_nome_imagem'];
                $produto->operadora                         = $rs_row['opr_nome_loja'];
                $produto->filtro['ogp_inibi_lojas_online']  = $rs_row['ogp_inibi_lojas_online'];
                $arrTemp['games'][] = $produto;

                unset($produto);
            }
        }
    }
    $busca->setProduto($arrTemp);
    unset($arrTemp);

    /*
    if(is_array($B2C_PRODUCT) && !empty($B2C_PRODUCT))
    {
        //foreach($arrProdutos as $ind => $objProdutos){
        foreach ($B2C_PRODUCT as $key => $value) 
        {
            if(trim($value['name']) != "")
            {
                $produto            = new stdClass();
                $produto->tipo      = "b2c";
                $produto->nome      = Util::cleanStr2($value['name']);
                $produto->busca     = strip_tags(Util::cleanStr2($value['name']." | ".$value['provider']));//strip_tags($value['name']." | ".$value['provider']." | ".$value['instrucoes']); //corrigir traducao dew caracter q nao ta funfando
                $produto->id        = $key;
                $produto->comissao  = $value['comiss_lan'];
                $produto->preco     = $value['price'];
                $produto->imagem    = $value['image'];

                if($value['tipo'] == "servicos"){
                    $arrServicos['servico'][] = $produto;
                }elseif($value['tipo'] == "jogos"){
                    $arrJogos['jogos'][] = $produto;
                }

                unset($produto);
            }

        }
    }
    $busca->setProduto($arrServicos);
    $busca->setProduto($arrJogos);
    unset($arrServicos);
    unset($arrJogos);
    */
    
    ////para voltar com os produtos b2c, descomente o bloco acima
    
    $busca->geraJson();
    
    //FIM DO BLOCO

    echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "Final de execuчуo em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;

    //$arquivoLog->deleteLockedFile();
///}
//else {
  ///  $arquivoLog->showBusy();
//}

//Fechando Conexуo
pg_close($connid);

?>