<?php
/**
 * CLASSE MULTI METODOS PARA RESOLVER SITUACOES  COMUNS ENTRE AS CLASSES
 */
class Util {
    public static function jsonVerify($jsonFile){
        if(file_exists($jsonFile)){
            if(is_readable($jsonFile)){
                if($json = file_get_contents($jsonFile)){
                    if(is_object(json_decode($json)) || is_array(json_decode($json))){
                        return json_decode($json);
                    }else{
                        throw new Exception("JSON NAO E VALIDO. ERRO NA VERIFICACAO SE … OBJETO/DECODIFICACAO DO JSON: {$jsonFile};");
                    }
                }else{
                    throw new Exception("JSON NAO E VALIDO. ERRO NA OBTENCAO DO ARQUIVO JSON: {$jsonFile};");
                }
            }else{
                throw new Exception("JSON NAO PODE SER LIDO: {$jsonFile};");
            }
            
        }else{
            throw new Exception("ARQUIVO {$jsonFile} N√O EXISTE;");
        }
        
    }
    
    public static function getFileContents($url){
        $options = array(
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_VERBOSE         => 1,
          
            CURLOPT_SSL_VERIFYPEER  => 0
        );

        ini_set("allow_url_fopen", "On");
        $ch = curl_init($url);//$ch = curl_init($this->_jsonUrl);
        curl_setopt_array($ch, $options);
        $file = curl_exec($ch);

        if($file === FALSE){
            $out = curl_error($ch);
            curl_close($ch);    
            throw new Exception($out);
        }else{
            return $file;
        }
    }
    
    /*
     * metodo estatico que retorna a data formatada;
     * @var $data: data a ser formatada
     * @var $db: se È para ser formatada com destino ao banco de dados. 
     *           padr„o false.
     *           se passado true, a data È transformada para o formato EN
     */
    public static function getData($data,$db = false){
        if(strlen($data) > 10 && !strpos($data, "/")){
            $dataArr = explode(" ",$data);
            $data = substr($dataArr[0],8,2)."/".substr($dataArr[0],5,2)."/".substr($dataArr[0],0,4);
        }else if($db == true &&  strpos($data, "/")){
            $dataArr = explode("/", $data);
            $data = $dataArr[2]."-".$dataArr[1]."-".$dataArr[0];
        }
        return $data;
    }
    
    /*
     * metodo estatico que retorna o n˙mero formatado
     * @var $numero: n˙mero a ser formatado
     * @var $para_banco: se È para ser formatado com destino ao banco de dados. 
     *           se passado true, o n˙mero È transformado para o Banco de Dados (ponto como decimal)
     *           se passado false, o n˙mero È transformado para ExibiÁ„o (virgula como decimal e milhar com ponto)
     */
    public static function getNumero($numero,$para_banco = FALSE){
        if($para_banco){
            $numero = str_replace(",",".",str_replace(".", "", $numero));
        }else {
            $numero = number_format($numero, 2, ",", ".");
        }
        return $numero;
    }//end function getNumero
    
    /*
     * metodo que transforma objeto em array
     */
    public static function object_to_array($obj) {
            if(is_object($obj)) $obj = (array) $obj;
            if(is_array($obj)) {
                $new = array();
                foreach($obj as $key => $val) {
                    $new[$key] = self::object_to_array($val);
                }
            }
            else $new = $obj;
            return $new;       
    } //end function object_to_array
    
    public static function showArrError($arr){
        $msg = implode(PHP_EOL,$arr);
        if(!empty($msg)) {
            echo "<script>alert('{$msg}');</script>";
        }
    }
    
    /*
        Metodo que calcula o intervalo de meses entre duas datas
        @var $CheckIn: menor data
        @var $CheckOut: maior data
        @var $interval: intervalo de meses
     */
    public static function dateDiff($CheckIn, $CheckOut,$interval){
        $CheckInX = explode("/", $CheckIn); 
        $CheckOutX =  explode("/", $CheckOut); 
        $datalimite  = mktime(0, 0, 0, $CheckOutX[1]-$interval, $CheckOutX[0], $CheckOutX[2]);
        $datainicial = mktime(0, 0, 0, $CheckInX[1], $CheckInX[0], $CheckInX[2]);
        
        return ($datainicial >= $datalimite) ? true : false;
    }
    
    /*
        Metodo que limpa caracteres especiais e pontuacao de string
        @var $str: string a ser limpa
    */
    public static function cleanStr($str)
    {
        $arr = array('!',':','?','^','~','`','¥',',','.',";");
        $str = str_replace($arr,"",$str);
        $from = '¿¡√¬… Õ”’‘⁄‹«—‡·„‚ÈÍÌÛıÙ˙¸ÁÒ ';
        $to = 'AAAAEEIOOOUUCNaaaaeeiooouucn-';

        return strtr($str, $from,$to);
    }
    
    /*
       Metodo de paginacao
       @var $offset: int; a partir de qual pagina comecar 
       @var $limit: int informando a quantidade maxima de registros por pagina
       @var $total: int; total de registros a serem paginados
       @var $maxPages: int; maximo de paginas que sera exibido em numerico

        @return se houver paginacao, retornara um array com as variaveis totais da paginacao e false se nao houver paginacao
     */
    public static function pagination($offset, $limit, $total, $inputs = array(), $maxPages = 5){
        //8, 5, 80
        //total = total_table
        //limit = max
        if($total > $limit)
        {
            $ponteiros = array();
            $arrPaginacao = array();
            $pages = ceil($total/$limit); //16
            $metade = ceil($maxPages/2); //3
            $qtdPagsAntesEDepois = floor($maxPages/2); //2
            $antes = ($offset-$qtdPagsAntesEDepois < 1) ? 1 : $offset-$qtdPagsAntesEDepois;
            $depois = $offset+$qtdPagsAntesEDepois;
            
            if($maxPages > $pages)
                $maxPages = $pages;
            
            if($depois > $pages){
                while ($depois > $pages){
                    $depois--;
                    if($antes > 1)
                        $antes--;
                }
            }
            
            if($offset >= 3)
                $ponteiros["first"] = "l<";
            if($offset >= 2)
                $ponteiros["prev"] = "<";
            
            for($i=$antes;count($arrPaginacao) < $maxPages;$i++)
            {   
                $arrPaginacao[] = $i;
            }

            if($offset+1 <= $pages)
                $ponteiros["next"] = ">";
            
            if($offset+2 <= $pages)
                $ponteiros["last"] = ">|";
            
            $paginacao['paginas'] = $arrPaginacao;
            $paginacao['ponteiros'] = $ponteiros;
            $paginacao['iptHidden'] = $inputs;
            $paginacao['limit'] = $limit;
            $paginacao['paginaAtual'] = $offset;
            $paginacao['totalTable'] = $total;
            
            
            return $paginacao;
        }
        else
        {
            return false;
        }
        
    }
    
    public static function getIptHidden(array $post)
    {
        if(!empty($post))
        {
            foreach ($post as $ind => $val){
                $temp[str_replace("hidden_","",$ind)] = $val;
            }    
        }
        return $temp;
    }
    
    /*
       Metodo de validacao se a requisicao È ajax ou nao
       
        @return true se for requisicao ajax e false se n„o
     */    
    public static function isAjaxRequest(){
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;
    }
    
    /*
        Metodo que limpa caracteres especiais e pontuacao de string exceto espaco em branco
        @var $str: string a ser limpa
    */
    public static function cleanStr2($str)
    {
        return preg_replace("/[^a-zA -Z0-9_.]/", "", strtr($str, "¡Õ”⁄…ƒœ÷‹À¿Ã“Ÿ»√’¬Œ‘€ ·ÌÛ˙È‰Ôˆ¸Î‡ÏÚ˘Ë„ı‚ÓÙ˚Í«Á", 
                                                                 "AIOUEAIOUEAIOUEAOAIOUEaioueaioueaioueaoaioueCc"));
    }
    
    /*
        Metodo que soma segundos a uma data passada no formato americano Y-m-d h:i:s
        @var $date: data
        @var $seconds: segundos a serem somados
     */
    public static function sumSeconds($date,$seconds){
        
        $date = explode(" ",$date);
        $ydm = explode("-", $date[0]);
        $his = explode(":",$date[1]);
        
        return  date("Y-m-d h:i:s",mktime($his[0], $his[1], $his[2]+$seconds, $ydm[1], $ydm[2], $ydm[0]));
    }
    
    /*
        Metodo que subtrai uma data por outra
        @var $time1
        @var $time2
     */
    public static function timeSub($time1,$time2){
        return  strtotime($time1) - strtotime($time2);
    }
    
    /*
        Metodo que subtrai uma data por outra
        @var $time1
        @var $time2
     */
    public static function redirect($url){
        echo "<script>location.href = '$url'</script>";
        die;
    }    
    
    /*
        Metodo que transforma segundos em tempo
        @var $val: segundos
        @var $showHour: se true, exibe a hora
     */
    public static function secToTime($val){
        if(floor($val / 3600) > 0)
            $time[] = floor($val / 3600)." hora(s)";
        
        if(floor(($val - ($horas * 3600)) / 60) > 0 || isset($time))
            $time[] = floor(($val - ($horas * 3600)) / 60)." minuto(s)";
        
        $time[] = floor($val % 60)." segundo(s)";
        

        return implode(", ",$time);
    }
    
    /*
        MÈtodo que valida data
        @var date: data a ser validada
        @var format: formato de data, sendo aceito somente 2 tipos, dd-mm-yyyy ou yyyy-mm-dd

     */
    public static function checkValidDate($date, $format = "dd-mm-yyyy"){
        if($format === "dd-mm-yyyy"){
            $dia = (int) substr($date,0,2);
            $mes = (int) substr($date, 3,2);
            $ano = (int) substr($date, 6,4);
            
        }else if($format === "yyyy-mm-dd"){
            $dia = (int) substr($date,8,2);
            $mes = (int) substr($date, 5,2);
            $ano = (int) substr($date, 0,4);
        }
        
        return checkdate($mes, $dia, $ano);
    }
    
    /*
        MÈtodo que procura de onde uma determinada funÁ„o est· vindo
        @var function: nome da funÁ„o a ser pesquisa (sem parenteses)
    */
    public static function checkFunctionSource($function){
        $array_includes = get_included_files();
        foreach($array_includes as $include){
            $file = file($include);
            foreach($file as $line_num => $line){
                if(strpos($line, "n $function(")){
                    echo "<pre>";
                    echo "Arquivo: " . $include . "<br>";
                    echo "Linha: " . $line_num . "<br>";
                    echo "</pre>";
                }
            }
        }
    }
    
    /*
     * FunÁ„o que retorna um time adicionando ao time atual a quantidade de meses passada por par‚metro 
     */
    public static function getTimeByMonth($returnMonths){
        return mktime(0, 0, 0, date('n') + $returnMonths, 1, date('Y'));
    }
    

}
