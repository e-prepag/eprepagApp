function getObj(ObjName){if(document.getElementById)
return document.getElementById(ObjName);else if(document.all)
return document.all(ObjName);else if(document.layers)
return document.layers[ObjName];}

function AJAXRequest(url,parameters,callback_function,object_name,return_xml)
{
	var http_request=false;
	if(window.XMLHttpRequest){
		http_request=new XMLHttpRequest();
		if(http_request.overrideMimeType){
			http_request.overrideMimeType('text/xml');
			}
			}else if(window.ActiveXObject){
				try{http_request=new ActiveXObject("Msxml2.XMLHTTP");
				}catch(e){
					try{
						http_request=new ActiveXObject("Microsoft.XMLHTTP");
						}catch(e){}
						}
						}
if(!http_request){
	alert('Browser doesn\'t support this feature.');
	return false;
	}
http_request.onreadystatechange=function(){
	if(http_request.readyState==1){
		eval(callback_function+'("","'+object_name+'")');
		}else if(http_request.readyState==4){
			if(http_request.status==200){
			if(return_xml){
				eval(callback_function+'(http_request.responseXML,"'+object_name+'", "'+url+'")');
				}else{
					eval(callback_function+'(http_request.responseText,"'+object_name+'")');
					}
					}else{
						alert('There was a problem with the request.(Code: '+http_request.status+')');eval(callback_function+'("&nbsp;","'+object_name+'")');
						}
						}
						}
parameters=URLEncode(parameters);
http_request.open("POST",url+"?"+parameters,true);
http_request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
//http_request.setRequestHeader("Content-length",parameters.length);
http_request.send(parameters);
}

function FillHTML(xmlDom,ObjetoID){if(getObj(ObjetoID)!=null){getObj(ObjetoID).innerHTML=xmlDom;ExtraiScript(xmlDom);}}
function URLEncode(url)
{var SAFECHARS="0123456789"+"ABCDEFGHIJKLMNOPQRSTUVWXYZ"+"abcdefghijklmnopqrstuvwxyz"+"-_.!~*'()=&";var HEX="0123456789ABCDEF";var plaintext=url;var encoded="";for(var i=0;i<plaintext.length;i++){var ch=plaintext.charAt(i);if(ch==" "){encoded+="+";}else if(SAFECHARS.indexOf(ch)!=-1){encoded+=ch;}else{var charCode=ch.charCodeAt(0);if(charCode>255){alert("Char '"
+ch
+"' cannot be encoded.\n");encoded+="+";}else{encoded+="%";encoded+=HEX.charAt((charCode>>4)&0xF);encoded+=HEX.charAt(charCode&0xF);}}}
return encoded;}
function URLDecode(url)
{var HEXCHARS="0123456789ABCDEFabcdef";var encoded=url;var plaintext="";var i=0;while(i<encoded.length){var ch=encoded.charAt(i);if(ch=="+"){plaintext+=" ";i++;}else if(ch=="%"){if(i<(encoded.length-2)&&HEXCHARS.indexOf(encoded.charAt(i+1))!=-1&&HEXCHARS.indexOf(encoded.charAt(i+2))!=-1){plaintext+=unescape(encoded.substr(i,3));i+=3;}else{alert('Bad escape at ...'+encoded.substr(i));plaintext+="%[ERROR]";i++;}}else{plaintext+=ch;i++;}}
return plaintext;}
function GetFormFields(idForm){var elementosFormulario=getObj(idForm).elements;var qtdElementos=elementosFormulario.length;var queryString="";var elemento;this.ConcatenaElemento=function(nome,valor){if(queryString.length>0){queryString+="&";}
queryString+=nome+"="+valor;};for(var i=0;i<qtdElementos;i++){elemento=elementosFormulario[i];if(!elemento.disabled){switch(elemento.type){case'text':case'password':case'hidden':case'textarea':this.ConcatenaElemento(elemento.name,elemento.value);break;case'select-one':if(elemento.selectedIndex>=0){this.ConcatenaElemento(elemento.name,elemento.options[elemento.selectedIndex].value);}
break;case'select-multiple':for(var j=0;j<elemento.options.length;j++){if(elemento.options[j].selected){this.ConcatenaElemento(elemento.name,elemento.options[j].value);}}
break;case'checkbox':case'radio':if(elemento.checked){this.ConcatenaElemento(elemento.name,elemento.value);}
break;}}}
return queryString;}
function ExtraiScript(texto){var ini,pos_src,fim,codigo,texto_pesquisa;var objScript=null;texto_pesquisa=texto.toLowerCase()
ini=texto_pesquisa.indexOf('<script',0)
while(ini!=-1){var objScript=document.createElement("script");pos_src=texto_pesquisa.indexOf(' src',ini)
ini=texto_pesquisa.indexOf('>',ini)+1;if(pos_src<ini&&pos_src>=0){ini=pos_src+4;fim=texto_pesquisa.indexOf('.',ini)+4;codigo=texto.substring(ini,fim);codigo=codigo.replace("=","").replace(" ","").replace("\"","").replace("\"","").replace("\'","").replace("\'","").replace(">","");objScript.src=codigo;}else{fim=texto_pesquisa.indexOf('</script>',ini);codigo=texto.substring(ini,fim);objScript.text=codigo;}
document.body.appendChild(objScript);ini=texto.indexOf('<script',fim);objScript=null;}}