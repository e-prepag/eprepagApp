<?php

define("PATH_API_ALAWAR", $raiz_do_projeto . "includes/gamer/alawar/");
define("PATH_INCLUDE_GAMER", $raiz_do_projeto . "includes/gamer/");
define("LOG_FILE_ALAWAR", $raiz_do_projeto . "log/logAlawarAPI.log");

define("PATH_LOAD_REMOTE_GAMEID_LIST_ALAWAR", "C:\\Sites\\E-Prepag\\backoffice\\offweb\\tarefas\\");
define("LOG_FILE_AUTOMATIC_TASKS_ALAWAR", "C:\\Sites\\E-Prepag\\backoffice\\offweb\\tarefas\\log\\logAlawar_LoadGameList.log");

define("URL_LOJA", "http://www.e-prepag.com.br/prepag2/commerce/");
define("URL_BASE_ALAWAR", "http://www.e-prepag.com.br/prepag2/commerce/jogos/");
define("URL_SHOWCASE_ALAWAR", "http://vitr.alawar.com/vitr3.0/e-prepag.com.br/");
define("URL_LOAD_GAME_LIST_ALAWAR", "http://eu.export.alawar.com/games_agsn_xml.php?pid=20403&locale=pt");

define("AFFILIATE_PID_ALAWAR", 20403);
define("AFFILIATE_LOCALE_ALAWAR", "pt");
define("AFFILIATE_SECRET_KEY", "bU56vDC3e"); /* Enviado pelo Ely em 31/01/2011 */

define("OPR_ALAWAR_ID", 12345); /* Código da Operadoa Alawar no BackOffice*/
define("OPR_PROD_ALAWAR_ID", 15022012); /* Código da Operadora Alawar no BackOffice*/

define("ALAWAR_PROD", 106); /* Código do produto Alawar no BackOffice*/

/* 
 IMPORTANTE: Na constante ALAWAR_GAME_DISABLE, eh necessario passar o valor Zero (0) como uma string, caso contrario quando o metodo 
             $this->getGamesBy($filtroCasualGame, $orderBy) tratar esta informacao, elel vai entender como se este filtro nao existisse, 
             e registra de forma errada os logs
*/

define("ALAWAR_GAME_ENABLE", '1'); 
define("ALAWAR_GAME_DISABLE", '0');

$ERRORS_ALAWAR[0] = "NO_ERROR"; /* Este Item foi criado pela E-Prepag para identificar qdo não for um erro */
$ERRORS_ALAWAR[1] = "INCORRECT_SMSCODE";
$ERRORS_ALAWAR[2] = "INCORRECT_GID_FOR_PID";
$ERRORS_ALAWAR[3] = "UNKNOWN_ERROR";
$ERRORS_ALAWAR[4] = "INCORRECT_EMAIL";
$ERRORS_ALAWAR[5] = "ID_ALREADY_USED";
$ERRORS_ALAWAR[6] = "INCORRECT_CODE";
$ERRORS_ALAWAR[7] = "CURL_CONNECTION_ERROR"; /* Este Item foi criado pela E-Prepag para tratar erro na conexão cURL via PHP */

$ERRORS_ALAWAR_ID["NO_ERROR"] = 0;
$ERRORS_ALAWAR_ID["INCORRECT_SMSCODE"] = 1;
$ERRORS_ALAWAR_ID["INCORRECT_GID_FOR_PID"] = 2;
$ERRORS_ALAWAR_ID["UNKNOWN_ERROR"] = 3;
$ERRORS_ALAWAR_ID["INCORRECT_EMAIL"] = 4;
$ERRORS_ALAWAR_ID["ID_ALREADY_USED"] = 5;
$ERRORS_ALAWAR_ID["INCORRECT_CODE"] = 6;
$ERRORS_ALAWAR_ID["CURL_CONNECTION_ERROR"] = 7;

?>