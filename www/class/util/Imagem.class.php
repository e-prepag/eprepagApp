<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Imagem{
    
    public function resize_img($file, $largura, $altura = NULL, $proporcinal = FALSE){
        
        list($largura_orig, $altura_orig) = getimagesize($file);
        
        if($proporcinal){
            $altura = $largura*$altura_orig/$largura_orig;
        }
        
        $image_p = imagecreatetruecolor($largura, $altura);
        
        if(pathinfo($file, PATHINFO_EXTENSION) == 'jpg' || pathinfo($file, PATHINFO_EXTENSION) == 'jpeg'){
            $image = imagecreatefromjpeg($file);
        } elseif(pathinfo($file, PATHINFO_EXTENSION) == 'png'){
            $image = imagecreatefrompng($file);
        } elseif(pathinfo($file, PATHINFO_EXTENSION) == 'gif'){
            $image = imagecreatefromgif($file);
        }
        
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $largura, $altura, $largura_orig, $altura_orig);

        if(pathinfo($file, PATHINFO_EXTENSION) == 'jpg' || pathinfo($file, PATHINFO_EXTENSION) == 'jpeg'){
            imagejpeg($image_p, $file, 80);
        } elseif(pathinfo($file, PATHINFO_EXTENSION) == 'png'){
            imagepng($image_p, $file);
        } elseif(pathinfo($file, PATHINFO_EXTENSION) == 'gif'){
            imagegif($image_p, $file);
        }
        
    }
    
    public function getImg($url, $endereco_destino, $largura = NULL, $altura = NULL, $proporcional = FALSE) { 

        $imagename= basename($url);
        $caminho = $endereco_destino.$imagename;
        
        copy($url, $caminho);
        
        if(!is_null($largura)){
            $this->resize_img($caminho, $largura, $altura, $proporcional);
        }
    }

}
