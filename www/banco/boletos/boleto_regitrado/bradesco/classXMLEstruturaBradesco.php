<?php

class XMLEstruturaBradesco{
    
    public $merchant_id;
    public $boleto;
    
    public function getRequestData($params) {
        $errors = array();
        if($this->validation($params, $errors)){
            $this->merchant_id = BRADESCO_MERCHANTID;
            $this->boleto = new boleto($params);
            return array($this);
        }
        else {
            $msgEmail = $this->getErrors($errors);
            $assunto = (checkIP()?"[DEV] ":"[PROD] ")."E-Prepag - Problema ao Registrar Boleto Bradesco";
            enviaEmail("estagiario1@e-prepag.com, wagner@e-prepag.com.br, suporte@e-prepag.com.br", null, null, $assunto, (isset($params['pagador']['id'])?"ID do Usuário: ".$params['pagador']['id']."<br>":"").$msgEmail);
            return false;
        }
			 
	}
    
    public function getErrors($errors){
        foreach($errors as $er){
            return "ERRO: " .$er . "<br>"; 
        }
    }
    
    private function xml2arrayInterno ( $xmlObject, $out = array () ) {
        foreach ( (array)$xmlObject as $index => $node ) {
            $out[$index] = (is_object($node)) ? self::xml2arrayInterno($node) : $node;
        }

        return $out;
    } //end function xml2arrayInterno

    public function getResponseData($soapResponseData) {
        try{
            $aux = new SimpleXMLElement($soapResponseData);
            $serialsaleRecord = $this->xml2arrayInterno($aux);
            return $serialsaleRecord;
        } catch(Exception $e) {
            return NULL;
        }
    }
    
    private function validation($params, &$errors = array()) {
        
     
        //obrigatorio
        if(!$this->checkTypeSize(BRADESCO_MERCHANTID, "TEXTO", 9, 36)){
            $errors[] = "Problema no Merchantid<br>";
            return false;
        }
        
        //obrigatorio
        if(isset($params['nosso_numero'])){
            if (!$this->checkTypeSize($params['nosso_numero'], "NUMERO", 11, 11)) {
                $errors[] = "Problema no Nosso Numero<br>Valor Inserido: [".$params['nosso_numero'] . "]<br>Tamanho do campo: 11 caracteres(somente numeros)";
                return false;
            }
        }else{
            $errors[] = "Campo 'nosso_numero' é OBRIGATÓRIO!";
            return false;
        }
        
        //obrigatorio
        if (!$this->checkTypeSize(BRADESCO_CARTEIRA, "TEXTO", 1, 2)) {
            $errors[] = "Problema no Bradesco Carteira<br>";
            return false;
        }
        
        //obrigatorio
        if(isset($params['numero_documento'])){
            if (!$this->checkTypeSize($params['numero_documento'], "TEXTO", 5, 25)) {
                $errors[] = "Problema no Numero Documento<br>Valor Inserido: [".$params['numero_documento'] . "]<br>Tamanho do campo: 5 a 25 caracteres";
                
                return false;
            }
        } else{
            $errors[] = "Campo 'numero_documento' é OBRIGATÓRIO!";
            return false;
        }
        
        //obrigatorio
        if(isset($params['data_emissao'])){
            if (!$this->checkTypeSize($params['data_emissao'], "DATA", 10, 10)) {
                $errors[] = "Problema na Data de Emissao <br>Valor Inserido: [".$params['data_emissao']."]<br>Formato correto: [AAAA-MM-DD]";
                return false;
            }
        } else{
            $errors[] = "Campo 'data_emissao' é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['data_vencimento'])){
            if (!$this->checkTypeSize($params['data_vencimento'], "DATA", 10, 10)) {
            $errors[] = "Problema na Data de Vencimento <br>Valor Inserido: [".$params['data_vencimento']."]<br>Formato correto: [AAAA-MM-DD]";
            return false;
            }
        } else{
            $errors[] = "Campo 'data_vencimento' é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['valor_titulo'])){
            if (!$this->checkTypeSize($params['valor_titulo'], "NUMERO", 1, 17)) {
                $errors[] = "Problema no Valor Titulo<br>Valor Inserido: [".$params['valor_titulo'] . "]<br>Tamanho do campo: 1 a 17 caracteres(somente numeros)";
                return false;
            }
        } else{
            $errors[] = "Campo 'valor_titulo' é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['nome'])){
            if(!$this->checkTypeSize($params['pagador']['nome'], "TEXTO", 1, 150)){
                $errors[] = "Problema no Nome Pagador<br>Valor Inserido: [".$params['pagador']['nome'] . "]<br>Tamanho do campo: 1 150 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'nome' do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['documento'])){
            if(!$this->checkTypeSize($params['pagador']['documento'], "TEXTO", 11, 14)){
                $errors[] = "Problema no Documento do Pagador<br>Valor Inserido: [".$params['pagador']['documento'] . "]<br>Tamanho do campo: 11 a 14 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'documento' do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['tipo_documento'])){
            if(!$this->checkTypeSize($params['pagador']['tipo_documento'], "TEXTO", 1, 1)){
                $errors[] = "Problema no Tipo Documento do Pagador<br>Valor Inserido: [".$params['pagador']['tipo_documento'] . "]<br>Tamanho do campo: apenas 1";
                return false;
            }
        } else{
            $errors[] = "Campo 'tipo_documento' do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['endereco']['cep'])){
            if(!$this->checkTypeSize($params['pagador']['endereco']['cep'], "TEXTO", 8, 8)){
                $errors[] = "Problema no CEP do Endereco do Pagador<br>Valor Inserido: [".$params['pagador']['endereco']['cep'] . "]<br>Tamanho do campo: apenas 8 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'cep' do Endereco do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['endereco']['logradouro'])){
            if(!$this->checkTypeSize($params['pagador']['endereco']['logradouro'], "TEXTO", 1, 70)){
                $errors[] = "Problema no Logradouro do Endereco do Pagador<br>Valor Inserido: [".$params['pagador']['endereco']['logradouro'] ."]<br>Tamanho do campo: 1 a 70 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'logradouro' do Endereco do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['endereco']['numero'])){
            if(!$this->checkTypeSize($params['pagador']['endereco']['numero'], "TEXTO", 1, 10)){
                $errors[] = "Problema no Numero do Endereco do Pagador<br>Valor Inserido: [".$params['pagador']['endereco'] . "]<br>Tamanho do campo: 1 a 10 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'numero' do Endereco do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        if(isset($params['pagador']['endereco']['complemento']) && !empty($params['pagador']['endereco']['complemento'])){
            if(!$this->checkTypeSize($params['pagador']['endereco']['complemento'], "TEXTO", 1, 20) && !is_null($params['pagador']['endereco']['complemento'])){
                $errors[] = "Problema no Complemento do Endereco do Pagador<br>Valor Inserido: [".$params['pagador']['endereco']['complemento'] . "]<br>Tamanho do campo: 1 a 20 caracteres";
                return false;
            }
        }
        
        //obrigatorio
        if(isset($params['pagador']['endereco']['bairro'])){
            if(!$this->checkTypeSize($params['pagador']['endereco']['bairro'], "TEXTO", 1, 50)){
                $errors[] = "Problema no Bairro do Endereco do Pagador<br>Valor Inserido: [".$params['pagador']['endereco']['bairro'] . "]<br>Tamanho do campo: 1 a 50 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'bairro' do Endereco do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['endereco']['cidade'])){
            if(!$this->checkTypeSize($params['pagador']['endereco']['cidade'], "TEXTO", 1, 100)){
                $errors[] = "Problema na cidade do Endereco do Pagador<br>Valor Inserido: [".$params['pagador']['endereco']['cidade'] . "]<br>Tamanho do campo: 1 a 100 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'cidade' do Endereco do Pagador é OBRIGATÓRIO";
            return false;
        }
        
        //obrigatorio
        if(isset($params['pagador']['endereco']['uf'])){
            if(!$this->checkTypeSize($params['pagador']['endereco']['uf'], "TEXTO", 2, 2)){
                $errors[] = "Problema no UF do Endereco do Pagador<br>Valor Inserido: [".$params['pagador']['endereco']['uf'] . "]<br>Tamanho do campo: apenas 2 caracteres";
                return false;
            }
        } else{
            $errors[] = "Campo 'uf' do Endereco do Pagador é OBRIGATÓRIO";
            return false;
        }
        
//        if(isset($params['informacoes_opcionais']['agencia_pagador'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['agencia_pagador'], "TEXTO", 0, 5) && !is_null($params['informacoes_opcionais']['agencia_pagador'])){
//                $errors[] = "Problema na Agencia do Pagador<br>Valor Inserido: [".$params['informacoes_opcionais']['agencia_pagador'] . "]<br>Tamanho do campo: 0 a 5 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['razao_conta_pagador'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['razao_conta_pagador'], "TEXTO", 0, 5) && !is_null($params['informacoes_opcionais']['razao_conta_pagador'])){
//                $errors[] = "Problema na Razao Conta do Pagador<br>Valor Inserido: [".$params['informacoes_opcionais']['razao_conta_pagador'] . "]<br>Tamanho do campo: 0 a 5 caracteres";    
//                return false;   
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['conta_pagador'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['conta_pagador'], "TEXTO", 0, 8) && !is_null($params['informacoes_opcionais']['conta_pagador'])){
//                $errors[] = "Problema na Conta do Pagador<br>Valor Inserido: [".$params['informacoes_opcionais']['conta_pagador'] . "]<br>Tamanho do campo: 0 a 8 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['controle_participante'])){
//            // $type = "TEXTO_ESP //---> controle_participante (só pode letra/numero)
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['controle_participante'], "TEXTO_ESP", 0, 25) && !is_null($params['informacoes_opcionais']['controle_participante'])){
//                $errors[] = "Problema no Controle Participante<br>Valor Inserido: [".$params['informacoes_opcionais']['controle_participante'] . "]<br>Tamanho do campo: 0 a 25 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['especie'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['especie'], "TEXTO", 1, 1) && !is_null($params['informacoes_opcionais']['especie'])){
//                $errors[] = "Problema na Especie<br>Valor Inserido: [".$params['informacoes_opcionais']['especie'] . "]<br>Tamanho do campo: apenas 1";
//                return false;
//            }
//        }  
//        
//        if(isset($params['informacoes_opcionais']['aceite'])){
//            // aceite =>  (valor padrão: 'S')
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['aceite'], "TEXTO", 1, 1) && !is_null($params['informacoes_opcionais']['aceite'])){
//                $errors[] = "Problema no Aceite<br>Valor Inserido: [".$params['informacoes_opcionais']['aceite'] . "]<br>Tamanho do campo: apenas 1";
//                return false;
//            }
//        } 
//        
//        if(isset($params['informacoes_opcionais']['tipo_protesto_negociacao'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['tipo_protesto_negociacao'], "TEXTO", 1, 1) && !is_null($params['informacoes_opcionais']['tipo_protesto_negociacao'])){
//                $errors[] = "Problema no Tipo Protesto Negociacao<br>Valor Inserido: [".$params['informacoes_opcionais']['tipo_protesto_negociacao'] . "]<br>Tamanho do campo: apenas 1";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['qtde_dias_protesto'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['qtde_dias_protesto'], "NUMERO", 1, 2) && !is_null($params['informacoes_opcionais']['qtde_dias_protesto'])){
//                $errors[] = "Problema na Quantidade Dias Protesto<br>Valor Inserido: [".$params['informacoes_opcionais']['qtde_dias_protesto'] . "]<br>Tamanho do campo: 1 a 2 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['tipo_decurso_prazo'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['tipo_decurso_prazo'], "TEXTO", 1, 1) && !is_null($params['informacoes_opcionais']['tipo_decurso_prazo'])){
//                $errors[] = "Problema no Tipo Decurso Prazo<br>Valor Inserido: [".$params['informacoes_opcionais']['tipo_decurso_prazo'] . "]<br>Tamanho do campo: apenas 1";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['qtde_dias_decurso'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['qtde_dias_decurso'], "NUMERO", 1, 2) && !is_null($params['informacoes_opcionais']['qtde_dias_decurso'])){
//                $errors[] = "Problema na Quantidade Dias Decurso<br>Valor Inserido: [".$params['informacoes_opcionais']['qtde_dias_decurso'] . "]<br>Tamanho do campo: 1 a 2 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['tipo_emissao_papeleta'])){
//            //tipo_emissao_papeleta => No caso do Comércio Eletrônico, o valor deverá ser sempre 2- Cliente emite.
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['tipo_emissao_papeleta'], "TEXTO", 1, 1) && !is_null($params['informacoes_opcionais']['tipo_emissao_papeleta'])){
//                $errors[] = "Problema no Tipo Emissao Papeleta<br>Valor Inserido: [".$params['informacoes_opcionais']['tipo_emissao_papeleta'] . "]<br>Tamanho do campo: apenas 1";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['qtde_parcelas'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['qtde_parcelas'], "NUMERO", 1, 2) && !is_null($params['informacoes_opcionais']['qtde_parcelas'])){
//                $errors[] = "Problema na Quantidade Parcelas<br>Valor Inserido: [".$params['informacoes_opcionais']['qtde_parcelas'] . "]<br>Tamanho do campo: 1 a 2 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['perc_juros'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['perc_juros'], "NUMERO", 1, 8) && !is_null($params['informacoes_opcionais']['perc_juros'])){
//                $errors[] = "Problema no Perc Juros<br>Valor Inserido: [".$params['informacoes_opcionais']['perc_juros'] . "]<br>Tamanho do campo: 1 a 8 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_juros'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_juros'], "NUMERO", 1, 17) && !is_null($params['informacoes_opcionais']['valor_juros'])){
//                $errors[] = "Problema no Valor Juros<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_juros'] . "]<br>Tamanho do campo: 1 a 17 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['qtde_dias_juros'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['qtde_dias_juros'], "NUMERO", 1, 2) && !is_null($params['informacoes_opcionais']['qtde_dias_juros'])){
//                $errors[] = "Problema na Quantidade Dias Juros<br>Valor Inserido: [".$params['informacoes_opcionais']['qtde_dias_juros'] . "]<br>Tamanho do campo: 1 a 2 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['perc_multa_atraso'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['perc_multa_atraso'], "NUMERO", 1, 8) && !is_null($params['informacoes_opcionais']['perc_multa_atraso'])){
//                $errors[] = "Problema no Perc Multa Atraso<br>Valor Inserido: [".$params['informacoes_opcionais']['perc_multa_atraso'] . "]<br>Tamanho do campo: 1 a 8 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_multa_atraso'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_multa_atraso'], "NUMERO", 1, 17) && !is_null($params['informacoes_opcionais']['valor_multa_atraso'])){
//                $errors[] = "Problema no Valor Multa Atraso<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_multa_atraso'] . "]<br>Tamanho do campo: 1 a 17 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['qtde_dias_multa_atraso'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['qtde_dias_multa_atraso'], "NUMERO", 1, 2) && !is_null($params['informacoes_opcionais']['qtde_dias_multa_atraso'])){
//                $errors[] = "Problema na Quantidade Dias Multa Atraso<br>Valor Inserido: [".$params['informacoes_opcionais']['qtde_dias_multa_atraso'] . "]<br>Tamanho do campo: 1 a 2 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['perc_desconto_1'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['perc_desconto_1'], "TEXTO", 1, 8) && !is_null($params['informacoes_opcionais']['perc_desconto_1'])){
//                $errors[] = "Problema no Perc Desconto_1<br>Valor Inserido: [".$params['informacoes_opcionais']['perc_desconto_1'] . "]<br>Tamanho do campo: 1 a 8 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_desconto_1'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_desconto_1'], "TEXTO", 1, 17) && !is_null($params['informacoes_opcionais']['valor_desconto_1'])){
//               $errors[] = "Problema no Valor Desconto_1<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_desconto_1'] . "]<br>Tamanho do campo: 1 a 17 caracteres";
//               return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['data_limite_desconto_1'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['data_limite_desconto_1'], "DATA", 10, 10) && !is_null($params['informacoes_opcionais']['data_limite_desconto_1'])){
//               $errors[] = "Problema na Data Limite Desconto_1 <br>Valor Inserido: [".$params['data_limite_desconto_1']."]<br>Formato correto: [AAAA-MM-DD]<br>";
//               return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['perc_desconto_2'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['perc_desconto_2'], "NUMERO", 1, 8) && !is_null($params['informacoes_opcionais']['perc_desconto_1'])){
//               $errors[] = "Problema no Perc Desconto_2<br>Valor Inserido: [".$params['informacoes_opcionais']['perc_desconto_2'] . "]<br>Tamanho do campo: 1 a 8 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_desconto_2'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_desconto_2'], "NUMERO", 1, 17) && !is_null($params['informacoes_opcionais']['valor_desconto_1'])){
//               $errors[] = "Problema no Valor Desconto_2<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_desconto_2'] . "]<br>Tamanho do campo: 1 a 17 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['data_limite_desconto_2'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['data_limite_desconto_2'], "DATA", 10, 10) && !is_null($params['informacoes_opcionais']['data_limite_desconto_1'])){
//               $errors[] = "Problema na Data Limite Desconto_2 <br>Valor Inserido: [".$params['data_limite_desconto_2']."]<br>Formato correto: [AAAA-MM-DD]";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['perc_desconto_3'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['perc_desconto_3'], "NUMERO", 1, 8) && !is_null($params['informacoes_opcionais']['perc_desconto_3'])){
//               $errors[] = "Problema no Perc Desconto_3<br>Valor Inserido: [".$params['informacoes_opcionais']['perc_desconto_3'] . "]<br>Tamanho do campo: 1 a 8 caracteres(somente numeros)";
//               return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_desconto_3'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_desconto_3'], "NUMERO", 1, 17) && !is_null($params['informacoes_opcionais']['valor_desconto_3'])){
//               $errors[] = "Problema no Valor Desconto_3<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_desconto_3'] . "]<br>Tamanho do campo: 1 a 17 caracteres(somente numeros)";
//               return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['data_limite_desconto_3'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['data_limite_desconto_3'], "DATA", 10, 10) && !is_null($params['informacoes_opcionais']['data_limite_desconto_3'])){
//               $errors[] = "Problema na Data Limite Desconto_3 <br>Valor Inserido: [".$params['data_limite_desconto_3']."]<br>Formato correto: [AAAA-MM-DD]";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['tipo_bonificacao'])){
//            //tipo_bonificacao => Tipo 3 = Valor Bonificação ? Dias Corridos
//            //                    Tipo 4 = Valor Bonificação ? Dias úteis
//            //                    Tipo 5 = Percentual Bonificação ? Dias Corridos
//            //                    Tipo 6 = Percentual Bonificação ? Dias úteis    
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['tipo_bonificacao'], "TEXTO", 1, 1) && !is_null($params['informacoes_opcionais']['tipo_bonificacao'])){
//               $errors[] = "Problema no Tipo Bonificacao<br>Valor Inserido: [".$params['informacoes_opcionais']['tipo_bonificacao'] . "]<br>Tamanho do campo: apenas 1";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['perc_desc_bonificacao'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['perc_desc_bonificacao'], "NUMERO", 1, 8) && !is_null($params['informacoes_opcionais']['perc_desc_bonificacao'])){
//               $errors[] = "Problema no Perc Desconto Bonificacao<br>Valor Inserido: [".$params['informacoes_opcionais']['perc_desconto_bonificacao'] . "]<br>Tamanho do campo: 1 a 8 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_desc_bonificacao'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_desc_bonificacao'], "NUMERO", 1, 17) && !is_null($params['informacoes_opcionais']['valor_desc_bonificacao'])){
//               $errors[] = "Problema no Valor Desconto Bonificacao<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_desconto_bonificacai'] . "]<br>Tamanho do campo: 1 a 17 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['data_limite_desc_bonificacao'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['data_limite_desc_bonificacao'], "DATA", 10, 10) && !is_null($params['informacoes_opcionais']['data_limite_desc_bonificacao'])){
//               $errors[] = "Problema na Data Limite Desc Bonificacao <br>Valor Inserido: [".$params['data_vencimento']."]<br>Formato correto: [AAAA-MM-DD]<br>";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_abatimento'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_abatimento'], "NUMERO", 1, 13) && !is_null($params['informacoes_opcionais']['valor_abatimento'])){
//               $errors[] = "Problema no Valor Abatimento<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_abatimento'] . "]<br>Tamanho do campo: 1 a 13 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['valor_iof'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['valor_iof'], "NUMERO", 1, 13) && !is_null($params['informacoes_opcionais']['valor_iof'])){
//               $errors[] = "Problema no Valor IOF<br>Valor Inserido: [".$params['informacoes_opcionais']['valor_iof'] . "]<br>Tamanho do campo: 1 a 13 caracteres(somente numeros)";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sequencia_registro'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sequencia_registro'], "TEXTO", 1, 6) && !is_null($params['informacoes_opcionais']['sequencia_registro'])){
//               $errors[] = "Problema na Sequencia Registro<br>Valor Inserido: [".$params['informacoes_opcionais']['sequencia_registro'] . "]<br>Tamanho do campo: 1 a 6 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['nome'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['nome'], "TEXTO", 1, 150)){
//               $errors[] = "Problema no Nome Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['nome'] . "]<br>Tamanho do campo: 1 a 150 caracteres";
//                return false;
//            }
//        } 
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['documento'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['documento'], "TEXTO", 11, 14)){
//               $errors[] = "Problema no Documento Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['documento'] . "]<br>Tamanho do campo: 11 a 14 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['tipo_documento'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['tipo_documento'], "TEXTO", 1, 1)){
//               $errors[] = "Problema no Tipo Documento Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['tipo_documento'] . "]<br>Tamanho do campo: apenas 1";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['endereco']['cep'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['endereco']['cep'], "TEXTO", 8, 8)){
//                $errors[] = "Problema no CEP Endereco Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['endereco']['cep'] . "]<br>Tamanho do campo: 8 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['endereco']['logradouro'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['endereco']['logradouro'], "TEXTO", 1, 70)){
//               $errors[] = "Problema no Logradouro Endereco Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['endereco']['logradouro'] . "]<br>Tamanho do campo: 1 a 70 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['endereco']['numero'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['endereco']['numero'], "TEXTO", 1, 10)){
//               $errors[] = "Problema no Numero Endereco Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['endereco']['numero'] . "]<br>Tamanho do campo: 1 a 10 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['endereco']['complemento'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['endereco']['complemento'], "TEXTO", 1, 20)){
//               $errors[] = "Problema no Complemento Endereco Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['endereco']['complemento'] . "]<br>Tamanho do campo: 1 a 20 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['endereco']['bairro'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['endereco']['bairro'], "TEXTO", 1, 50)){
//               $errors[] = "Problema no Bairro Endereco Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['endereco']['bairro'] . "]<br>Tamanho do campo: 1 a 50 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['endereco']['cidade'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['endereco']['cidade'], "TEXTO", 1, 100)){
//               $errors[] = "Problema na Cidade Endereco Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['endereco']['cidade'] . "]<br>Tamanho do campo: 1 a 100 caracteres";
//                return false;
//            }
//        }
//        
//        if(isset($params['informacoes_opcionais']['sacador_avalista']['endereco']['cidade'])){
//            if(!$this->checkTypeSize($params['informacoes_opcionais']['sacador_avalista']['endereco']['uf'], "TEXTO", 2, 2)){
//               $errors[] = "Problema no UF Endereco Sacador<br>Valor Inserido: [".$params['informacoes_opcionais']['sacador_avalista']['endereco']['uf'] . "]<br>Tamanho do campo: 2 caracteres";
//                return false;
//            }
//        }

        return true;
        
    }
    
    private function checkTypeSize($var, $type, $min, $max) {
        
        switch (strtoupper($type)) {
            case "TEXTO":
                if(preg_match('/^[A-Za-zÀ-ú0-9\x21-\xBAü\s]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
                
            case "NUMERO":
                if(preg_match('/^[0-9]+$/u', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
                
            case "DATA":
                if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "TEXTO_ESP":
                if(preg_match('/^[A-Za-z0-9]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;

        }    
        
    }
    
}

?>

