<?php
date_default_timezone_set('America/Fortaleza');
/* 
 * ajax para trabalhar com produtos do json
 */

require_once "../../includes/constantes.php";

require_once DIR_CLASS."util/Util.class.php";

if(Util::isAjaxRequest())
{
    require_once DIR_CLASS."util/Busca.class.php";
    
    $qtd = $_POST['qtd']; //12
    $inicio = $_POST['inicio']; //12
    $ate = $_POST['ate']; 
//    $pagina = $_POST['pagina']; //1
    $categoria = $_POST['categoria'];
    $id = (isset($_POST['id'])?$_POST['id']:null);
//var_dump($_POST);
    if($qtd == 8){
        $bootstrapcol = "col-md-3 col-xs-6 col-sm-6 col-lg-3";
    }else if($qtd == 12){
        $bootstrapcol = "col-md-2 col-xs-6 col-sm-4 col-lg-2";
    }
    
    if($categoria == "Gamer"){
        $arrJsonFiles =  unserialize(ARR_PRODUTOS_GAMER);
        $imgDir = DIR_G_IMG_PRODUTOS;
    }else{
        $arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);
        $imgDir = DIR_W_IMG_PRODUTOS;
    }

    $busca = new Busca;
    $busca->setFullPath(DIR_JSON);
    $busca->setArrJsonFiles($arrJsonFiles);
    $busca->setCategoria($categoria);

    $json = $busca->getAllJsonByFilter();

    $produtos = array_values($json);
	
    for($i=$inicio;$i<$ate;$i++){
        if(!empty($produtos[$i])) {
            if($categoria != "Gamer"){  
                if(in_array($id,$ARRAY_INIBI_VENDA_HARDCODE) && in_array((str_replace("/creditos/produto/produto_detalhe.php?prod=","",$produtos[$i]["id"])*1),$ARRAY_INIBI_PRODUTOS_VENDA_TO_ID_HARDCODE)){
                    $ate++;
                    continue;
                }
            }//end if($categoria != "Gamer")
				
			//echo "<script>console.log(".json_encode($produtos[$i]['object']).");</script>";
             
            if(!is_null($id)){

                if($id != 17371){

                    if($produtos[$i]['object']->id != 443 && $categoria != "Gamer"){
						$productResult[] = $produtos[$i];
					}

                }else{
                    $productResult[] = $produtos[$i];
                }

            }else{
                if($_SERVER["REMOTE_ADDR"] != "201.93.162.169"){
					if($produtos[$i]['object']->filtro->ogp_opr_codigo != 162){ //159	
						$productResult[] = $produtos[$i];
					}
				}else{
					$productResult[] = $produtos[$i];
				}
            }
			/*if($id != 17371 || $id == null){
			
					if(($produtos[$i]['object']->id != 443 && $categoria != "Gamer") || ($produtos[$i]['object']->id != 537 && $categoria == "Gamer")){	//$produtos[$i]['object']->filtro->ogp_opr_codigo != 159 && 
						$productResult[] = $produtos[$i];
					}
						
			}else{
				
				$productResult[] = $produtos[$i];
			}*/
			
        }
    }
    
    $maiorAltura = 0;
    $array_imagem = array();
    
    foreach($productResult as $produto){
        if(!isset($produto['object']->imagem)){
            $height = 0;
        }else{
            if($categoria == "Gamer"){
                list($width, $height) = getimagesize(DIR_WEB . DIR_G_IMG_PRODUTOS . $produto['object']->imagem);
            }else{
                list($width, $height) = getimagesize(DIR_WEB . DIR_W_IMG_PRODUTOS . $produto['object']->imagem);
            }
        }
        $array_imagem[$produto['object']->imagem] = $height;
        if($height > $maiorAltura) {
            $maiorAltura = $height;
        }
    }
    
    $html = "";
    if(!empty($productResult)){

        $cont = 0;
        $html .= "<div class='row'>";
        foreach($productResult as $produto){

            $html .= '<div class="'.$bootstrapcol.' txt-azul-claro text-center top20 c-pointer"  onclick="postProduct('.$produto['object']->id.')">
                        <div class="thumbnail">
                            <div class="box-image" style="height: ' . $maiorAltura . 'px">';

                if( $produto['object']->imagem && 
                    $produto['object']->imagem != "" && 
                    file_exists(DIR_WEB . $imgDir . $produto['object']->imagem)){ 
                    $html .= '<img border="0" class="img-produto" style="margin-top: ' . ($maiorAltura - $array_imagem[$produto['object']->imagem])/2 . 'px" src="'.$imgDir . $produto['object']->imagem.'">';
                } 

            $html .= '</div>
                    <div class="caption align-center thumbail-body">      
                        <h4 class="color-blue">
                            <strong>'.$produto['object']->nome.'</strong>
                        </h4>
                        <button type="button" class="btn btn-success btn-block">Comprar</button>
                    </div>
                    </div>
            </div>';
            $cont++;
            if($cont == 4){
                $html .= "</div><div class='row'>";
                $cont = 0;
            }
            if($cont == 2){
                $html .= "<div class='clearfix visible-xs-block'></div>";
            }
        }
    }
    
    print $html;
    
}else{
    die("Chamada não permitida ou banner inválido.");
}