<?php
date_default_timezone_set('America/Fortaleza');
$raiz_do_projeto = "/www/";
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."class/business/BannerBO.class.php";

if(Util::isAjaxRequest())
{
    $objBanner = new BannerBO;
    
    if(isset($_POST['idc'])  && ($_POST['idc']!="") && isset($_POST['metodo']) && $_POST['metodo'] == "posicoes"){
        $filtro[] = "bs_data_inicio <= '". date('Y-m-d 00:00:00') ."'";
        $filtro[] = "bs_data_fim >= '". date('Y-m-d 00:00:00') ."'";
        $filtro[] = "bs_status = 1";
        $filtro[] = "bsc_id = ".$_POST['idc'];
        $posicoes = array();
        $html = "";
        $banners = $objBanner->pegaBanner($filtro);

        if(!empty($banners)){
            foreach($banners as $banner){
                $idPosicao = $banner->getPosicao()->getId();
                $posicoes[$idPosicao] = $banner->getPosicao()->getDescricao();
//                $html .= "<option value='".$idPosicao."'>".$banner->getPosicao()->getDescricao()."</option>";
            }
            
            if(!empty($posicoes)){
                $html = '<select class="form-control" onchange="getTabelaBanners(this.value)" name="bsp_id" id="bsp_id">
                            <option>--</option>';
                
                foreach($posicoes as $idPosicao => $posicao){
                    $html .= "<option value='".$idPosicao."'>".$posicao."</option>";
                }
                
                $html .= '</select>';
            }
        }
        
        print ($html != "") ? $html : utf8_encode("<span class='txt-vermelho'>Não foram encontradas posições para essa categoria.</span>");
        
    }
    
    if(isset($_POST['idc']) && ($_POST['idc']!="") && isset($_POST['idp']) && ($_POST['idp']!="") && isset($_POST['metodo']) && $_POST['metodo'] == "banners"){
        
        $filtro[] = "bs_status = 1";
        $filtro[] = "bs_data_inicio <= '". date('Y-m-d 00:00:00') ."'";
        $filtro[] = "bs_data_fim >= '". date('Y-m-d 00:00:00') ."'";
        $filtro[] = "bsp_id = ".$_POST['idp'];
        $filtro[] = "bsc_id = ".$_POST['idc'];

        $banners = $objBanner->pegaBanner($filtro);
        
        $html = '<div class="col-md-7 espacamento  top20">
                    <table class="table table-bordered">
                        <thead class="">
                            <tr>
                                <th class="text-center">Titulo</th>
                            </tr>
                        </thead> 
                        <tbody>';
        
        if(!empty($banners)){
            foreach ($banners as $banner){
                $html .='<tr class="trListagem">
                                <td class="text-center c-move" id="'.$banner->getId().'">'.$banner->getTitulo().'<input type="hidden" name="banners['.$banner->getId().']" value=""></td>
                        </tr>';
            }
                            
            $html .='</tbody>
                        </table>
                    </div>
                    <div class="col-md-5 espacamento  top20">
                        <button type="button" class="btn btn-success" onclick="reordena();">Salvar</button>
                    </div>
                    <script>
                        $("tbody").sortable({
                            appendTo: "parent",
                            helper: "clone"
                        }).disableSelection();
                    </script>';
        }else{
            $html .='<tr class="bannersOpt">
                                <td colspan="2" class="text-center">Não temos banners cadastrados para ordenação, nessa categoria/posição.</td>
                            </tr></tbody>
                    </table>
                </div>';
        }
        
        print utf8_encode($html);
    }
}else
{
    die("Chamada não permitida.");
}