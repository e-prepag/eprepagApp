<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

// include do arquivo contendo IPs DEV
require_once $raiz_do_projeto. "includes/configIP.php";

// Constante que define o ambiente de conexão Produção (Live = 1) ou Homologação (Test = 2)
if(checkIP()) {
    define("B2C_LIVE_ENVIRONMET",	2);
    $server_url = $_SERVER['SERVER_NAME'];
    }
else {
    define("B2C_LIVE_ENVIRONMET",	1);
    $server_url = "www.e-prepag.com.br";
    }

// Constante definindo o IP do cliente
define("B2C_CLIENT_IP_ADDR",	"189.38.238.205");

// Identificadores de Login
define("B2C_CLIENT_ID_TEST",	"ijodLRU2vgxLYuQWioDIRwiQytuPLKoL");
define("B2C_CLIENT_ID_LIVE",	"DeigdMwAbFTYeUQlTpamfViuJPSe6yOI");

// test  
define("B2C_SERVICE_URL_TEST",	"https://esbhomolog.b2cexpress.com.br/eservice/service/varejo/VarejoService");
define("B2C_WSDL_URL_TEST",		"https://esbhomolog.b2cexpress.com.br/eservice/service/varejo/VarejoService?wsdl");

// live
define("B2C_SERVICE_URL_LIVE",	"https://esb.b2cexpress.com.br/eservice/service/varejo/VarejoService");
define("B2C_WSDL_URL_LIVE",		"https://esb.b2cexpress.com.br/eservice/service/varejo/VarejoService?wsdl");

// B2C Código dos Produtos
//test
define("B2C_PRODUCT_SERVICE_ANTIVIRUS_TEST",			"5454545454548");
define("B2C_PRODUCT_SERVICE_CURSOSONLINE_TEST",			"1209876543218");
define("B2C_PRODUCT_SERVICE_INTERNETSECURITY_TEST",		"8741687167854");
define("B2C_PRODUCT_SERVICE_UFABACKUP_TEST",			"7898948527856");
define("B2C_PRODUCT_SERVICE_FINANCIALPLAN_TEST",		"7898574700777");
define("B2C_PRODUCT_SERVICE_JOGO_SINE_MORA_TEST",		"7898586970427");
define("B2C_PRODUCT_SERVICE_CURSO_ONLINE_CORREIOS_TEST",	"7898586970663");
define("B2C_PRODUCT_SERVICE_BASICOS_PARA_CURSOS_TEST",		"7898586970694");
define("B2C_PRODUCT_SERVICE_CARREIRA_BANCARIA_TEST",		"7898586970670");
define("B2C_PRODUCT_SERVICE_ASSISTENCIA_AUTO_TEST",		"7898586970021");
define("B2C_PRODUCT_SERVICE_ASSISTENCIA_MOTO_TEST",		"7898586970038");
define("B2C_PRODUCT_SERVICE_ASSISTENCIA_CASA_TEST",		"7898586970045");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_TEST",			"7898586970144");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_3M_TEST",		"7898586970151");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_6M_TEST",		"7898586970236");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_12M_TEST",		"7898586970243");
define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_6M_TEST",		"7898586970212");
define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_12M_TEST",	"7898586970229");
define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_6M_TEST",		"7898586970250");
define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_12M_TEST",		"7898586970267");
define("B2C_PRODUCT_SERVICE_JOGO_SONIC_TEST",			"7898586970434");
define("B2C_PRODUCT_SERVICE_JOGO_SBK_GENERATIONS_TEST",		"7898586970410");
define("B2C_PRODUCT_SERVICE_JOGO_MOTOCROSS_WORLD_TEST",		"7898586970380");
define("B2C_PRODUCT_SERVICE_JOGO_MAN_OF_WAR_TEST",		"7898586970366");
define("B2C_PRODUCT_SERVICE_JOGO_HARD_TRUCK_TEST",		"7898586970359");
define("B2C_PRODUCT_SERVICE_JOGO_RACE_ON_TEST",			"7898586970397");
define("B2C_PRODUCT_SERVICE_JOGO_CRAZY_TAXI_TEST",		"7898586970342");
define("B2C_PRODUCT_SERVICE_JOGO_SANT_ROW_33RD_TEST",		"7898586970403");
define("B2C_PRODUCT_SERVICE_JOGO_METRO_2033_TEST",		"7898586970373");
define("B2C_PRODUCT_SERVICE_SECURITY_PARA_ANDROID_TEST",	"7898586970298");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_PURE_TOTAL_TEST",	"7898574700142");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_TEST",		"7898586970311");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_5P_TEST",	"7898586970328");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_10P_TEST",	"7898586970335");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_3P_TEST",	"7898574700104");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_5P_TEST",	"7898574700111");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_10P_TEST",	"7898574700128");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_TOTAL_SECURUTY_3P_TEST",	"7898586971776");

define("B2C_PRODUCT_JOGOS_DEAD_RISING_2_TEST",			"2134322313215");
define("B2C_PRODUCT_JOGOS_DEAD_RISING_3_TEST",			"7898586971103");
define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_TEST",			"7898586971905");
define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_4_TEST",		"7898586971936");
define("B2C_PRODUCT_JOGOS_LOST_PLANET_TEST",			"7898586971882");
define("B2C_PRODUCT_JOGOS_MEGA_MAN_TEST",			"7898586971592");
define("B2C_PRODUCT_JOGOS_REMEMBER_ME_TEST",			"7898586971929");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_TEST",			"7898586971912");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_2_TEST",		"7898586971196");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_5_TEST",		"7898586971127");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_6_TEST",		"7898586971035");
define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_TEST",		"7898586971561");
define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_UP_TEST",		"7898586971967");
define("B2C_PRODUCT_JOGOS_TOMB_RIDER_TEST",			"7898586971189");


// live
define("B2C_PRODUCT_SERVICE_ANTIVIRUS_LIVE",			"7898948527450");
define("B2C_PRODUCT_SERVICE_CURSOSONLINE_LIVE",			"7898948527313");
define("B2C_PRODUCT_SERVICE_INTERNETSECURITY_LIVE",		"7898574700029");
define("B2C_PRODUCT_SERVICE_UFABACKUP_LIVE",			"7898948527856");
define("B2C_PRODUCT_SERVICE_FINANCIALPLAN_LIVE",		"7898574700777");
define("B2C_PRODUCT_SERVICE_JOGO_SINE_MORA_LIVE",		"7898586970427");
define("B2C_PRODUCT_SERVICE_CURSO_ONLINE_CORREIOS_LIVE",	"7898586970663");
define("B2C_PRODUCT_SERVICE_BASICOS_PARA_CURSOS_LIVE",		"7898586970694");
define("B2C_PRODUCT_SERVICE_CARREIRA_BANCARIA_LIVE",		"7898586970670");
define("B2C_PRODUCT_SERVICE_ASSISTENCIA_AUTO_LIVE",		"7898586970021");
define("B2C_PRODUCT_SERVICE_ASSISTENCIA_MOTO_LIVE",		"7898586970038");
define("B2C_PRODUCT_SERVICE_ASSISTENCIA_CASA_LIVE",		"7898586970045");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_LIVE",			"7898586970144");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_3M_LIVE",		"7898586970151");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_6M_LIVE",		"7898586970236");
define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_12M_LIVE",		"7898586970243");
define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_6M_LIVE",		"7898586970212");
define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_12M_LIVE",	"7898586970229");
define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_6M_LIVE",		"7898586970250");
define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_12M_LIVE",		"7898586970267");
define("B2C_PRODUCT_SERVICE_JOGO_SONIC_LIVE",			"7898586970434");
define("B2C_PRODUCT_SERVICE_JOGO_SBK_GENERATIONS_LIVE",		"7898586970410");
define("B2C_PRODUCT_SERVICE_JOGO_MOTOCROSS_WORLD_LIVE",		"7898586970380");
define("B2C_PRODUCT_SERVICE_JOGO_MAN_OF_WAR_LIVE",		"7898586970366");
define("B2C_PRODUCT_SERVICE_JOGO_HARD_TRUCK_LIVE",		"7898586970359");
define("B2C_PRODUCT_SERVICE_JOGO_RACE_ON_LIVE",			"7898586970397");
define("B2C_PRODUCT_SERVICE_JOGO_CRAZY_TAXI_LIVE",		"7898586970342");
define("B2C_PRODUCT_SERVICE_JOGO_SANT_ROW_33RD_LIVE",		"7898586970403");
define("B2C_PRODUCT_SERVICE_JOGO_METRO_2033_LIVE",		"7898586970373");
define("B2C_PRODUCT_SERVICE_SECURITY_PARA_ANDROID_LIVE",	"7898586970298");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_PURE_TOTAL_LIVE",	"7898574700142");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_LIVE",		"7898586970311");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_5P_LIVE",	"7898586970328");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_10P_LIVE",	"7898586970335");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_3P_LIVE",	"7898574700104");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_5P_LIVE",	"7898574700111");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_10P_LIVE",	"7898574700128");
define("B2C_PRODUCT_SERVICE_KARSPERSKY_TOTAL_SECURUTY_3P_LIVE",	"7898586971776");

define("B2C_PRODUCT_JOGOS_DEAD_RISING_2_LIVE",			"7898586971950");
define("B2C_PRODUCT_JOGOS_DEAD_RISING_3_LIVE",			"7898586971103");
define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_LIVE",			"7898586971905");
define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_4_LIVE",		"7898586971936");
define("B2C_PRODUCT_JOGOS_LOST_PLANET_LIVE",			"7898586971882");
define("B2C_PRODUCT_JOGOS_MEGA_MAN_LIVE",			"7898586971592");
define("B2C_PRODUCT_JOGOS_REMEMBER_ME_LIVE",			"7898586971929");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_LIVE",			"7898586971912");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_2_LIVE",		"7898586971196");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_5_LIVE",		"7898586971127");
define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_6_LIVE",		"7898586971035");
define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_LIVE",		"7898586971561");
define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_UP_LIVE",		"7898586971967");
define("B2C_PRODUCT_JOGOS_TOMB_RIDER_LIVE",			"7898586971189");
 
// URLS
if(B2C_LIVE_ENVIRONMET==1) {
	define("B2C_SERVICE_URL",	B2C_SERVICE_URL_LIVE);
	define("B2C_WSDL_URL",		B2C_WSDL_URL_LIVE);
	define("B2C_CLIENT_ID",		B2C_CLIENT_ID_LIVE);
	define("B2C_PRODUCT_SERVICE_ANTIVIRUS",			B2C_PRODUCT_SERVICE_ANTIVIRUS_LIVE);
	define("B2C_PRODUCT_SERVICE_CURSOSONLINE",		B2C_PRODUCT_SERVICE_CURSOSONLINE_LIVE);
	define("B2C_PRODUCT_SERVICE_INTERNETSECURITY",		B2C_PRODUCT_SERVICE_INTERNETSECURITY_LIVE);
	define("B2C_PRODUCT_SERVICE_UFABACKUP",			B2C_PRODUCT_SERVICE_UFABACKUP_LIVE);
	define("B2C_PRODUCT_SERVICE_FINANCIALPLAN",		B2C_PRODUCT_SERVICE_FINANCIALPLAN_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_SINE_MORA",		B2C_PRODUCT_SERVICE_JOGO_SINE_MORA_LIVE);
	define("B2C_PRODUCT_SERVICE_CURSO_ONLINE_CORREIOS",	B2C_PRODUCT_SERVICE_CURSO_ONLINE_CORREIOS_LIVE);
	define("B2C_PRODUCT_SERVICE_BASICOS_PARA_CURSOS",	B2C_PRODUCT_SERVICE_BASICOS_PARA_CURSOS_LIVE);
	define("B2C_PRODUCT_SERVICE_CARREIRA_BANCARIA",		B2C_PRODUCT_SERVICE_CARREIRA_BANCARIA_LIVE);
	define("B2C_PRODUCT_SERVICE_ASSISTENCIA_AUTO",		B2C_PRODUCT_SERVICE_ASSISTENCIA_AUTO_LIVE);
	define("B2C_PRODUCT_SERVICE_ASSISTENCIA_MOTO",		B2C_PRODUCT_SERVICE_ASSISTENCIA_MOTO_LIVE);
	define("B2C_PRODUCT_SERVICE_ASSISTENCIA_CASA",		B2C_PRODUCT_SERVICE_ASSISTENCIA_CASA_LIVE);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_LIVE);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_3M",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_3M_LIVE);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_6M",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_6M_LIVE);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_12M",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_12M_LIVE);
	define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_6M",	B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_6M_LIVE);
	define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_12M",	B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_12M_LIVE);
	define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_6M",	B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_6M_LIVE);
	define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_12M",	B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_12M_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_SONIC",		B2C_PRODUCT_SERVICE_JOGO_SONIC_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_SBK_GENERATIONS",	B2C_PRODUCT_SERVICE_JOGO_SBK_GENERATIONS_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_MOTOCROSS_WORLD",	B2C_PRODUCT_SERVICE_JOGO_MOTOCROSS_WORLD_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_MAN_OF_WAR",		B2C_PRODUCT_SERVICE_JOGO_MAN_OF_WAR_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_HARD_TRUCK",		B2C_PRODUCT_SERVICE_JOGO_HARD_TRUCK_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_RACE_ON",		B2C_PRODUCT_SERVICE_JOGO_RACE_ON_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_CRAZY_TAXI",		B2C_PRODUCT_SERVICE_JOGO_CRAZY_TAXI_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_SANT_ROW_33RD",	B2C_PRODUCT_SERVICE_JOGO_SANT_ROW_33RD_LIVE);
	define("B2C_PRODUCT_SERVICE_JOGO_METRO_2033",		B2C_PRODUCT_SERVICE_JOGO_METRO_2033_LIVE);
	define("B2C_PRODUCT_SERVICE_SECURITY_PARA_ANDROID",	B2C_PRODUCT_SERVICE_SECURITY_PARA_ANDROID_LIVE);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_PURE_TOTAL",	B2C_PRODUCT_SERVICE_KARSPERSKY_PURE_TOTAL_LIVE);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET",	B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_LIVE);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_5P",	B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_5P_LIVE);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_10P",	B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_10P_LIVE);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_3P",	B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_3P_LIVE);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_5P",	B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_5P_LIVE);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_10P",	B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_10P_LIVE);
        define("B2C_PRODUCT_SERVICE_KARSPERSKY_TOTAL_SECURUTY_3P",	B2C_PRODUCT_SERVICE_KARSPERSKY_TOTAL_SECURUTY_3P_LIVE);        
        
        define("B2C_PRODUCT_JOGOS_DEAD_RISING_2",		B2C_PRODUCT_JOGOS_DEAD_RISING_2_LIVE);        
        define("B2C_PRODUCT_JOGOS_DEAD_RISING_3",		B2C_PRODUCT_JOGOS_DEAD_RISING_3_LIVE);        
        define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY",		B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_LIVE);        
        define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_4",		B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_4_LIVE);        
        define("B2C_PRODUCT_JOGOS_LOST_PLANET",			B2C_PRODUCT_JOGOS_LOST_PLANET_LIVE);        
        define("B2C_PRODUCT_JOGOS_MEGA_MAN",			B2C_PRODUCT_JOGOS_MEGA_MAN_LIVE);        
        define("B2C_PRODUCT_JOGOS_REMEMBER_ME",			B2C_PRODUCT_JOGOS_REMEMBER_ME_LIVE);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_LIVE);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_2",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_2_LIVE);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_5",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_5_LIVE);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_6",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_6_LIVE);        
        define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV",		B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_LIVE);        
        define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_UP",	B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_UP_LIVE);        
        define("B2C_PRODUCT_JOGOS_TOMB_RIDER",			B2C_PRODUCT_JOGOS_TOMB_RIDER_LIVE);        
       
} else {
	define("B2C_SERVICE_URL",	B2C_SERVICE_URL_TEST);
	define("B2C_WSDL_URL",		B2C_WSDL_URL_TEST);
	define("B2C_CLIENT_ID",		B2C_CLIENT_ID_TEST);
	define("B2C_PRODUCT_SERVICE_ANTIVIRUS",			B2C_PRODUCT_SERVICE_ANTIVIRUS_TEST);
	define("B2C_PRODUCT_SERVICE_CURSOSONLINE",		B2C_PRODUCT_SERVICE_CURSOSONLINE_TEST);
	define("B2C_PRODUCT_SERVICE_INTERNETSECURITY",		B2C_PRODUCT_SERVICE_INTERNETSECURITY_TEST);
	define("B2C_PRODUCT_SERVICE_UFABACKUP",			B2C_PRODUCT_SERVICE_UFABACKUP_TEST);
	define("B2C_PRODUCT_SERVICE_FINANCIALPLAN",		B2C_PRODUCT_SERVICE_FINANCIALPLAN_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_SINE_MORA",		B2C_PRODUCT_SERVICE_JOGO_SINE_MORA_TEST);
	define("B2C_PRODUCT_SERVICE_CURSO_ONLINE_CORREIOS",	B2C_PRODUCT_SERVICE_CURSO_ONLINE_CORREIOS_TEST);
	define("B2C_PRODUCT_SERVICE_BASICOS_PARA_CURSOS",	B2C_PRODUCT_SERVICE_BASICOS_PARA_CURSOS_TEST);
	define("B2C_PRODUCT_SERVICE_CARREIRA_BANCARIA",		B2C_PRODUCT_SERVICE_CARREIRA_BANCARIA_TEST);
	define("B2C_PRODUCT_SERVICE_ASSISTENCIA_AUTO",		B2C_PRODUCT_SERVICE_ASSISTENCIA_AUTO_TEST);
	define("B2C_PRODUCT_SERVICE_ASSISTENCIA_MOTO",		B2C_PRODUCT_SERVICE_ASSISTENCIA_MOTO_TEST);
	define("B2C_PRODUCT_SERVICE_ASSISTENCIA_CASA",		B2C_PRODUCT_SERVICE_ASSISTENCIA_CASA_TEST);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_TEST);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_3M",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_3M_TEST);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_6M",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_6M_TEST);
	define("B2C_PRODUCT_SERVICE_CPF_CONSULTA_12M",		B2C_PRODUCT_SERVICE_CPF_CONSULTA_12M_TEST);
	define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_6M",	B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_6M_TEST);
	define("B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_12M",	B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_12M_TEST);
	define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_6M",	B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_6M_TEST);
	define("B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_12M",	B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_12M_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_SONIC",		B2C_PRODUCT_SERVICE_JOGO_SONIC_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_SBK_GENERATIONS",	B2C_PRODUCT_SERVICE_JOGO_SBK_GENERATIONS_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_MOTOCROSS_WORLD",	B2C_PRODUCT_SERVICE_JOGO_MOTOCROSS_WORLD_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_MAN_OF_WAR",		B2C_PRODUCT_SERVICE_JOGO_MAN_OF_WAR_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_HARD_TRUCK",		B2C_PRODUCT_SERVICE_JOGO_HARD_TRUCK_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_RACE_ON",		B2C_PRODUCT_SERVICE_JOGO_RACE_ON_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_CRAZY_TAXI",		B2C_PRODUCT_SERVICE_JOGO_CRAZY_TAXI_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_SANT_ROW_33RD",	B2C_PRODUCT_SERVICE_JOGO_SANT_ROW_33RD_TEST);
	define("B2C_PRODUCT_SERVICE_JOGO_METRO_2033",		B2C_PRODUCT_SERVICE_JOGO_METRO_2033_TEST);
	define("B2C_PRODUCT_SERVICE_SECURITY_PARA_ANDROID",	B2C_PRODUCT_SERVICE_SECURITY_PARA_ANDROID_TEST);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_PURE_TOTAL",	B2C_PRODUCT_SERVICE_KARSPERSKY_PURE_TOTAL_TEST);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET",	B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_TEST);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_5P",	B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_5P_TEST);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_10P",	B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_10P_TEST);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_3P",	B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_3P_TEST);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_5P",	B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_5P_TEST);
	define("B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_10P",	B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_10P_TEST);
        define("B2C_PRODUCT_SERVICE_KARSPERSKY_TOTAL_SECURUTY_3P",	B2C_PRODUCT_SERVICE_KARSPERSKY_TOTAL_SECURUTY_3P_TEST);        
        
        define("B2C_PRODUCT_JOGOS_DEAD_RISING_2",		B2C_PRODUCT_JOGOS_DEAD_RISING_2_TEST);        
        define("B2C_PRODUCT_JOGOS_DEAD_RISING_3",		B2C_PRODUCT_JOGOS_DEAD_RISING_3_TEST);        
        define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY",		B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_TEST);        
        define("B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_4",		B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_4_TEST);        
        define("B2C_PRODUCT_JOGOS_LOST_PLANET",			B2C_PRODUCT_JOGOS_LOST_PLANET_TEST);        
        define("B2C_PRODUCT_JOGOS_MEGA_MAN",			B2C_PRODUCT_JOGOS_MEGA_MAN_TEST);        
        define("B2C_PRODUCT_JOGOS_REMEMBER_ME",			B2C_PRODUCT_JOGOS_REMEMBER_ME_TEST);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_TEST);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_2",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_2_TEST);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_5",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_5_TEST);        
        define("B2C_PRODUCT_JOGOS_RESIDENT_EVEL_6",		B2C_PRODUCT_JOGOS_RESIDENT_EVEL_6_TEST);        
        define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV",		B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_TEST); 
        define("B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_UP",	B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_UP_TEST);        
        define("B2C_PRODUCT_JOGOS_TOMB_RIDER",			B2C_PRODUCT_JOGOS_TOMB_RIDER_TEST);        

}

// Tipos de Moedas
define("B2C_CURRENCY_BRL", "BRL");

// Tipos de Moedas
define("B2C_CNPJ_EPP", "08221305000135");

//VETOR Contendo os Produtos da B2C
$B2C_PRODUCT = array(	B2C_PRODUCT_SERVICE_ANTIVIRUS		=> array(	'name'		=> 'Kaspersky Anti-Virus 1 PC / 1 ANO',
                                                                                                        'provider'	=> 'ESYWORLD DISTRIBUIDORA',
                                                                                                        'validity'	=> '180 dias',
                                                                                                        'price'		=> '59.90',
                                                                                                        'comiss'	=> '35', //EM PERCENTAGEM
                                                                                                        'comiss_lan'=> '15', //EM PERCENTAGEM
                                                                                                        'image'		=> DIR_WEB . 'imagens/pdv/img_kaspersky.jpg',
                                                                                                        'instrucoes'=> '<div align="justify">1 - Para utilizar este produto, acesse o site <a href="http://www.b2cexpress.com.br/ativar">www.b2cexpress.com.br/ativar</a>;<br>
                                                                                                                2 - Baixe o arquivo de instalação do Kaspersky Anti-Virus;<br>
                                                                                                                3 - Digite o número de ativação contido neste e-mail e siga as instruções para instalação do produto.<br><br>
                                                                                                                Você tem até 180 dias após a data da compra para ativar o seu produto.<br><br>
                                                                                                                Suporte Kaspersky:<br>
                                                                                                                <a href="http://www.kaspersky.com.br/suporte">www.kaspersky.com.br/suporte</a></div>',
													'mais_info'	=> 'https://www.e-prepag.com/antivirus-kaspersky',
                                                                                                        'tipo'          => 'servicos',
                                                                                                ),
						B2C_PRODUCT_SERVICE_INTERNETSECURITY=> array(	'name'		=> 'Kaspersky Internet Security 1 PC / 1 ANO',
                                                                                                        'provider'	=> 'ESYWORLD DISTRIBUIDORA',
                                                                                                        'validity'	=> '180 dias',
                                                                                                        'price'		=> '99.90',
                                                                                                        'comiss'	=> '35', //EM PERCENTAGEM
                                                                                                        'comiss_lan'=> '15', //EM PERCENTAGEM
                                                                                                        'image'		=> DIR_WEB . 'imagens/pdv/img_kaspersky2.jpg',
                                                                                                        'instrucoes'=> '<div align="justify">1 - Para utilizar este produto, acesse o site <a href="http://www.b2cexpress.com.br/ativar">www.b2cexpress.com.br/ativar</a>;<br> 
                                                                                                                2 - Baixe o arquivo de instalação do Kaspersky Internet Security.<br>
                                                                                                                3 - Digite o número de ativação contido neste e-mail e siga as instruções para instalação do produto.<br><br>
                                                                                                                Você tem até 180 dias após a data da compra para ativar o seu produto.<br><br>
                                                                                                                Suporte Kaspersky:<br>
                                                                                                                <a href="http://www.kaspersky.com.br/suporte">www.kaspersky.com.br/suporte</a></div>',
													'mais_info'	=> 'https://www.e-prepag.com/kaspersky-internet-security',
                                                                                                        'tipo'          => 'servicos',
                                                                                                        ),
						B2C_PRODUCT_SERVICE_SECURITY_PARA_ANDROID=> array(	'name'		=> 'Kaspersky Security para Android',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '29.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_Android.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> 'https://www.e-prepag.com/internet-security-para-android',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_5P=> array(	'name'		=> 'Kaspersky Anti-Virus - 5 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '149.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_5PCs.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> 'https://www.e-prepag.com/antivirus-kaspersky',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_10P=> array(	'name'		=> 'Kaspersky Anti-Virus - 10 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '299.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_10PCs.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> 'https://www.e-prepag.com/antivirus-kaspersky',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET=> array(	'name'		=> 'Kaspersky Internet Security MD - 3 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '149.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_Internet_3PCs.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> 'https://www.e-prepag.com/kaspersky-internet-security',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_5P=> array(	'name'		=> 'Kaspersky Internet Security MD - 5 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '199.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_Internet_5PCs.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> 'https://www.e-prepag.com/kaspersky-internet-security',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_INTERNET_10P=> array(	'name'		=> 'Kaspersky Internet Security MD - 10 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '419.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_Internet_10PCs.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> 'https://www.e-prepag.com/kaspersky-internet-security',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_TOTAL_SECURUTY_3P=> array(	'name'		=> 'Kaspersky Total Security Multidispositivos - 3 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '199.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_TotalSecurity2016.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> 'https://www.e-prepag.com/kaspersky-total-security',
                                                                                                        'tipo'          => 'servicos',
													),
                                                B2C_PRODUCT_SERVICE_CPF_CONSULTA	=> array(	'name'		=> 'Acompanhe o seu CPF - Plano Mensal',
													'provider'	=> 'SERASA S. A. (Serasa)',
													'validity'	=> '1 mês',
													'price'		=> '19.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/CPF_Mensal.jpg',
													'instrucoes'	=> '<div align="justify">Acesse o site www.nuvemdeservicos.com.br/ativar e insira o seu código PIN. Siga as instruções para realizar o cadastramento de suas informações pessoais e utilizar o serviço. 
																Você tem até 90 dias (noventa) a partir da data da compra para ativar o seu serviço.</div>',
													'mais_info'	=> 'https://www.e-prepag.com/acompanhe-seu-cpf',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_CPF_CONSULTA_3M	=> array(	'name'		=> 'Acompanhe o seu CPF - Plano 3 meses',
													'provider'	=> 'SERASA S. A. (Serasa)',
													'validity'	=> '03 meses',
													'price'		=> '39.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/CPF_3meses.jpg',
													'instrucoes'	=> '<div align="justify">Acesse o site www.nuvemdeservicos.com.br/ativar e insira o seu código PIN. Siga as instruções para realizar o cadastramento de suas informações pessoais e utilizar o serviço. 
																Você tem até 90 dias (noventa) a partir da data da compra para ativar o seu serviço.</div>',
													'mais_info'	=> 'https://www.e-prepag.com/acompanhe-seu-cpf',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_CPF_CONSULTA_6M	=> array(	'name'		=> 'Acompanhe o seu CPF - Plano 6 meses',
													'provider'	=> 'SERASA S. A. (Serasa)',
													'validity'	=> '06 meses',
													'price'		=> '69.90',
													'comiss'	=> '32', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/CPF_6meses.jpg',
													'instrucoes'	=> '<div align="justify">Acesse o site www.nuvemdeservicos.com.br/ativar e insira o seu código PIN. Siga as instruções para realizar o cadastramento de suas informações pessoais e utilizar o serviço.
																Você tem até 90 dias (noventa) a partir da data da compra para ativar o seu serviço.</div>',
													'mais_info'	=> 'https://www.e-prepag.com/acompanhe-seu-cpf',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_CPF_CONSULTA_12M	=> array(	'name'		=> 'Acompanhe o seu CPF - Plano 12 meses',
													'provider'	=> 'SERASA S. A. (Serasa)',
													'validity'	=> '1 ano',
													'price'		=> '120.00',
													'comiss'	=> '32', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/CPF_Anual.jpg',
													'instrucoes'	=> '<div align="justify">Acesse o site www.nuvemdeservicos.com.br/ativar e insira o seu código PIN. Siga as instruções para realizar o cadastramento de suas informações pessoais e utilizar o serviço.
																Você tem até 90 dias (noventa) a partir da data da compra para ativar o seu serviço.</div>',
													'mais_info'	=> 'https://www.e-prepag.com/acompanhe-seu-cpf',
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_CURSOSONLINE	=> array(	'name'		=> 'Cursos online internet p/ capacitação profissional',
																		'provider'	=> 'WOLI CONSULTORIA E TREINAMENTO LTDA(WOLI)',
																		'validity'	=> '',
																		'price'		=> '29.90',
																		'comiss'	=> '50', //EM PERCENTAGEM
																		'comiss_lan'=> '20', //EM PERCENTAGEM
																		'image'		=> DIR_WEB . 'imagens/pdv/img_curso.jpg',
																		'instrucoes'=> '<div align="justify">1 - Acesse <a href="http://www.facilaprender.com.br/aluno">www.facilaprender.com.br/aluno</a> e clique em "1º Acesso";<br>
																			2 - Insira o PIN;<br>
																			3 - Preencha seu cadastro e clique em "Próximo Passo";<br>
																			4 - Escolha o curso desejado e clique no botão "Matricular";<br>
																			5 - Confira os dados e clique em "Confirmar Dados";<br>
																			6 - Leia as instruções, finalize seu cadastro e a matrícula do curso.<br><br>
																			Para iniciar o treinamento acesse <a href="http://www.facilaprender.com.br/aluno">www.facilaprender.com.br/aluno</a> e clique em "Já Ativei o Código", digite seu CPF e sua senha (inicialmente a senha para acesso será seu CPF).</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
																		),
						B2C_PRODUCT_SERVICE_UFABACKUP		=> array(	'name'		=> 'UFA BACKUP 250GB',
                                                                                                        'provider'	=> 'SSI - SISTEMAS DE SEGURANÇA DA INFORMAÇÃO LTDA',
                                                                                                        'validity'	=> '',
                                                                                                        'price'		=> '69.00',
                                                                                                        'comiss'	=> '40', //EM PERCENTAGEM
                                                                                                        'comiss_lan'=> '20', //EM PERCENTAGEM
                                                                                                        'image'		=> DIR_WEB . 'imagens/pdv/img_ufabackup.jpg',
                                                                                                        'instrucoes'=> '<div align="justify">1 - Acesse o site <a href="http://www.ufa.com.br">www.ufa.com.br</a><br>
                                                                                                                2 - Clique em "Cartela"<br>
                                                                                                                3 - Insira o PIN, efetue seu cadastro e	baixe o programa para seu computador.</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
																		),
						B2C_PRODUCT_SERVICE_FINANCIALPLAN	=> array(	'name'		=> 'Planejamento Financeiro Virtual',
                                                                                                        'provider'	=> 'OFICINA DAS FINANÇAS CURSOS LTDA ME',
                                                                                                        'validity'	=> '',
                                                                                                        'price'		=> '179.00',
                                                                                                        'comiss'	=> '40', //EM PERCENTAGEM
                                                                                                        'comiss_lan'=> '17.5', //EM PERCENTAGEM
                                                                                                        'image'		=> DIR_WEB . 'imagens/pdv/img_planejamentofinanceiro_site.jpg',
                                                                                                        'instrucoes'=> '<div align="justify">1 - Acesse o site <a href="http://www.oficinadasfinancas.com.br/planejamento">www.oficinadasfinancas.com.br/planejamento</a>;<br>
                                                                                                                2 - Insira o PIN no campo informado e clique em “Verificar”;<br>
                                                                                                                3 - Informe o seu e-mail;<br> 
                                                                                                                4 - Você receberá o login e senha para iniciar o Planejamento. Após receber o login e senha você terá até 30 dias para responder o primeiro questionário e até 20 dias para responder cada questionário subsequente.<br><br>
                                                                                                                O Planejamento ficará disponível para ativação por até um ano a partir da data da compra.</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
																		),
						B2C_PRODUCT_SERVICE_BASICOS_PARA_CURSOS	=> array(	'name'		=> 'Curso Online Mód. Básico para Concursos',
													'provider'	=> 'GRAN CURSOS ONLINE',
													'validity'	=> 'Duração do curso',
													'price'		=> '249.90',
													'comiss'	=> '40', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/CursoOnline_Aprender.jpg',
													'instrucoes'	=> '<div align="justify">
														Processo de Ativação:<br> 
														- Acesse o site: www.grancursosonline.com.br/pin<br> 
														- Insira o PIN<br> 
														- Siga as instruções para utilizar o serviço. </div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
/*
						B2C_PRODUCT_SERVICE_JOGO_SINE_MORA	=> array(	'name'		=> 'Jogo Completo Sine Mora',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '16.90',
													'comiss'	=> '20', //EM PERCENTAGEM
													'comiss_lan'	=> '10', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Sine_Mora.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_CURSO_ONLINE_CORREIOS=> array(	'name'		=> 'Curso Online Correios e Telégrafos 120 dias',
													'provider'	=> 'GRAN CURSOS ONLINE',
													'validity'	=> '120 dias',
													'price'		=> '249.90',
													'comiss'	=> '40', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Curso_Correios.jpg',
													'instrucoes'	=> '<div align="justify">
													Processo de Ativação:
														- Acesse o site: www.grancursosonline.com.br/pin
														- Insira o PIN
														- Siga as instruções para utilizar o serviço. </div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_CARREIRA_BANCARIA	=> array(	'name'		=> 'Curso Online 3 em 1 Carreira Bancária 120 dias',
													'provider'	=> 'GRAN CURSOS ONLINE',
													'validity'	=> '120 dias',
													'price'		=> '399.90',
													'comiss'	=> '40', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Curso_Bancario.jpg',
													'instrucoes'	=> '<div align="justify">
														Processo de Ativação:
														- Acesse o site: www.grancursosonline.com.br/pin
														- Insira o PIN
														- Siga as instruções para utilizar o serviço.</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
  						B2C_PRODUCT_SERVICE_ASSISTENCIA_AUTO	=> array(	'name'		=> 'Assistência Auto Protection 12 Meses',
													'provider'	=> 'MONDIAL ASSISTANCE',
													'validity'	=> '1 ano',
													'price'		=> '179.90',
													'comiss'	=> '25', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Assistencia_Auto.jpg',
													'instrucoes'	=> '<div align="justify">O cliente deverá entrar em contato com a Central de Atendimento(0800 729 2020) para realizar a ativação do produto no prazo máximo de 90 dias após a data da compra.</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_ASSISTENCIA_MOTO	=> array(	'name'		=> 'Assistência Moto Protection 12 Meses',
													'provider'	=> 'MONDIAL ASSISTANCE',
													'validity'	=> '1 ano',
													'price'		=> '179.90',
													'comiss'	=> '25', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Assistencia_Moto.jpg',
													'instrucoes'	=> '<div align="justify">O cliente deverá entrar em contato com a Central de Atendimento(0800 729 2020) para realizar a ativação do produto no prazo máximo de 90 dias após a data da compra.</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_ASSISTENCIA_CASA	=> array(	'name'		=> 'Assistência Casa e Apartamente 12 Meses',
													'provider'	=> 'MONDIAL ASSISTANCE',
													'validity'	=> '1 ano',
													'price'		=> '139.90',
													'comiss'	=> '28', //EM PERCENTAGEM
													'comiss_lan'	=> '14', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Assistencia_Casa.jpg',
													'instrucoes'	=> '<div align="justify">O cliente deverá entrar em contato com a Central de Atendimento(0800 729 2020) para realizar a ativação do produto no prazo máximo de 90 dias após a data da compra.</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
*/
						B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_6M=> array(	'name'		=> 'Nuvem de Livros - Biblioteca Online 6 meses',
													'provider'	=> 'Nuvem de Serviços (Nuvem)',
													'validity'	=> '06 meses',
													'price'		=> '69.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/NuvemLivros_6meses.jpg',
													'instrucoes'	=> '<div align="justify">Passos de Ativação:<br> 
																1. Para ativar seu PIN, acesse www.nuvemdelivros.com.br/ativar;<br> 
																2. Insira o PIN no campo indicado;<br> 
																3. Siga as instruções para utilizar o serviço.
                                                                                                                                </div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_BIBLIOTECA_ONLINE_12M=> array(	'name'		=> 'Nuvem de Livros - Biblioteca Online 12 meses',
													'provider'	=> 'Nuvem de Serviços (Nuvem)',
													'validity'	=> '1 ano',
													'price'		=> '119.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '20', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/NuvemLivros_12meses.jpg',
													'instrucoes'	=> '<div align="justify">Passos de Ativação:<br> 
																1. Para ativar seu PIN, acesse www.nuvemdelivros.com.br/ativar;<br> 
																2. Insira o PIN no campo indicado;<br> 
																3. Siga as instruções para utilizar o serviço.
                                                                                                                                </div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_6M	=> array(	'name'		=> 'Tradução Português - Libras/voz 6 meses',
													'provider'	=> 'Instituto de Ciências e Tecnologias (ICTS)',
													'validity'	=> '06 meses',
													'price'		=> '29.90',
													'comiss'	=> '50', //EM PERCENTAGEM
													'comiss_lan'	=> '25', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Rybena_12meses.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:<br> 
																1. Acesse o site www.rybenapessoal.com.br;<br> 
																2. Insira o PIN contido na cartela;<br> 
																3. Siga as instruções recebidas por e-mail para utilizar o serviço.<br> 
                                                                                                                                Você tem até 90 (noventa) dias a partir da data da compra para ativar o seu serviço.
                                                                                                                                </div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_RYBENA_TRADUCAO_12M	=> array(	'name'		=> 'Tradução Português - Libras/voz 12 meses',
													'provider'	=> 'Instituto de Ciências e Tecnologias (ICTS)',
													'validity'	=> '1 ano',
													'price'		=> '49.90',
													'comiss'	=> '50', //EM PERCENTAGEM
													'comiss_lan'	=> '25', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Rybena_12meses.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:<br> 
																1. Acesse o site www.rybenapessoal.com.br;<br> 
																2. Insira o PIN contido na cartela;<br> 
																3. Siga as instruções recebidas por e-mail para utilizar o serviço.<br> 
                                                                                                                                Você tem até 90 (noventa) dias a partir da data da compra para ativar o seu serviço.
                                                                                                                                </div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
    /*
						B2C_PRODUCT_SERVICE_JOGO_SONIC		=> array(	'name'		=> 'Sonic And Sega All-Stars Racing Transformed',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '34.90',
													'comiss'	=> '15', //EM PERCENTAGEM
													'comiss_lan'	=> '8', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Sonic.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_JOGO_SBK_GENERATIONS=> array(	'name'		=> 'Jogo Completo SBK Generations',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '29.90',
													'comiss'	=> '15', //EM PERCENTAGEM
													'comiss_lan'	=> '8', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/SBK_Generations.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_JOGO_MOTOCROSS_WORLD=> array(	'name'		=> 'Jogo Completo Motocross World Championship',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '39.90',
													'comiss'	=> '15', //EM PERCENTAGEM
													'comiss_lan'	=> '8', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Motocross_Championship.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_JOGO_MAN_OF_WAR	=> array(	'name'		=> 'Jogo Completo Men of War',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '19.90',
													'comiss'	=> '15', //EM PERCENTAGEM
													'comiss_lan'	=> '8', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Men_Of_War.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),   
						B2C_PRODUCT_SERVICE_JOGO_HARD_TRUCK	=> array(	'name'		=> 'Jogo Completo Hard Truck',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '19.90',
													'comiss'	=> '15', //EM PERCENTAGEM
													'comiss_lan'	=> '8', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Hard_Truck.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_JOGO_RACE_ON	=> array(	'name'		=> 'Jogo Completo Race On',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '39.90',
													'comiss'	=> '20', //EM PERCENTAGEM
													'comiss_lan'	=> '10', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Race_On.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_JOGO_CRAZY_TAXI	=> array(	'name'		=> 'Jogo Completo Crazy Taxi',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '17.90',
													'comiss'	=> '20', //EM PERCENTAGEM
													'comiss_lan'	=> '10', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Crazy_Taxi.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_JOGO_SANT_ROW_33RD	=> array(	'name'		=> 'Jogo Completo Saint Row 3rd',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '19.90',
													'comiss'	=> '30', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/SaintsRow_TheThird.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_JOGO_METRO_2033	=> array(	'name'		=> 'Jogo Completo Metro 2033',
													'provider'	=> 'Incomp',
													'validity'	=> 'Permanente',
													'price'		=> '19.90',
													'comiss'	=> '30', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Metro_2033.jpg',
													'instrucoes'	=> '<div align="justify"></div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_PURE_TOTAL=> array(	'name'		=> 'Kaspersky Pure Total Security - 3 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '169.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_PureTotal_3PCs.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
						B2C_PRODUCT_SERVICE_KARSPERSKY_ANTI_VIRUS_3P=> array(	'name'		=> 'Kaspersky Anti-Virus - 3 PCs',
													'provider'	=> 'KASPERSKY LAB',
													'validity'	=> '1 ano',
													'price'		=> '99.90',
													'comiss'	=> '35', //EM PERCENTAGEM
													'comiss_lan'	=> '15', //EM PERCENTAGEM
													'image'		=> DIR_WEB . 'imagens/pdv/Kaspersky_3PCs.jpg',
													'instrucoes'	=> '<div align="justify">Processo de Ativação:
																1. Para ativar o seu PIN, acesse www.nuvemdeservicos.com.br/ativar;
																2. Digite o código PIN contido na cartela;
																3. Siga os passos indicados para realizar a ativação do seu software;
																4. Você ainda receberá por e-mail os dados para a instalação do Kaspersky Anti-Virus;
																Você tem até 180 (cento e oitenta) dias após a data da compra para ativar o seu produto. Após este período a ativação não poderá ser realizada.
																</div>',
													'mais_info'	=> NULL,
                                                                                                        'tipo'          => 'servicos',
													),
 */
                                                B2C_PRODUCT_JOGOS_DEAD_RISING_2 => array(
                                                                                                'name'                  => 'Dead Rising 2: Off The Record',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '39.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/deadrising2.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Dead Rising 2: Off The Record.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_DEAD_RISING_3 => array(	
                                                                                                'name'                  => 'Dead Rising 3 - Apocalypse Edition',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '99.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/deadrising3.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Dead Rising 3 - Apocalypse Edition.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY => array(	
                                                                                                'name'                  => 'DmC: Devil May Cry',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '59.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/devilmaycry.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do DmC: Devil May Cry.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_DEVEL_MAY_CRY_4 => array(	
                                                                                                'name'                  => 'Devil May Cry 4 Special Edition',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '49.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/devilmaycry_special.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Devil May Cry 4 Special Edition.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_LOST_PLANET => array(	
                                                                                                'name'                  => 'Lost Planet 3',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '45.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/lostplanet3.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Lost Planet 3.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_MEGA_MAN => array(	
                                                                                                'name'                  => 'Mega Man Legacy Collection',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '29.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/megaman.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Mega Man Legacy Collection.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_REMEMBER_ME => array(	
                                                                                                'name'                  => 'Remember Me',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '49.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/rememberme.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Remember Me.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_RESIDENT_EVEL => array(	
                                                                                                'name'                  => 'Resident Evil Revelations',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '59.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/resident_evil_rev.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Resident Evil Revelations.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_RESIDENT_EVEL_2 => array(	
                                                                                                'name'                  => 'Resident Evil: Revelations 2 Deluxe Edition',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '74.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/resident_evil_rev2.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Resident Evil: Revelations 2 Deluxe Edition.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_RESIDENT_EVEL_5 => array(	
                                                                                                'name'                  => 'Resident Evil 5 - Gold Edition',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '59.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/resident_evil5.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Resident Evil 5 - Gold Edition.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_RESIDENT_EVEL_6 => array(	
                                                                                                'name'                  => 'Resident Evil 6',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '89.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/resident_evil6.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Resident Evil 6.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV => array(	
                                                                                                'name'                  => 'Ultra Street Fighter IV',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '55.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/streetfighter_4.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Ultra Street Fighter IV.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_STREET_FIGHTER_IV_UP => array(	
                                                                                                'name'                  => 'Ultra Street Fighter IV Upgrade',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '27.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/streetfighter_upgrade.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Ultra Street Fighter IV Upgrade.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
                                                B2C_PRODUCT_JOGOS_TOMB_RIDER => array(	
                                                                                                'name'                  => 'Tomb Raider',
                                                                                                'provider'            => 'Nexway',
                                                                                                'validity'                => 'Indeterminado',
                                                                                                'price'                    => '34.99',
                                                                                                'comiss' => '15', //EM PERCENTAGEM
                                                                                                'comiss_lan'=> '9', //EM PERCENTAGEM
                                                                                                'image'=> DIR_WEB . 'imagens/pdv/tombrider.jpg',
                                                                                                'instrucoes'=> 'Como ativar seu serviço: <br> 
                                                                                                1 - Para ativar seu PIN, acesse www.nuvemdeservicos.com.br/ativar<br> 
                                                                                                2 - Digite o código PIN contido neste e-mail;<br> 
                                                                                                3 - Siga os passos indicados para realizar a ativação do seu Jogo;<br> 
                                                                                                4 - Você ainda receberá por e-mail os dados para a instalação do Tomb Raider.',
                                                                                                'mais_info'=> NULL,
                                                                                                'tipo'     =>'jogos',
                                                                                                ),
					);

// B2C SOAP Action Name
define("B2C_ACTION_SEARCH_RANGE_PIN",			"buscarFaixasPin");
define("B2C_ACTION_SEARCH_RANGE_PIN_FOR_DATE",	"buscarFaixasPinPorData");
define("B2C_ACTION_CALCULATE_SERVICE_CODE",		"calcularServicoCodigo");
define("B2C_ACTION_CALCULATE_PIN_SERVICE",		"calcularServicoPin");
define("B2C_ACTION_CANCEL_SALE",				"cancelarVenda");
define("B2C_ACTION_COMPLETE_SALE_DATA",			"complementarDadosVenda");
define("B2C_ACTION_CONSULT_STATUS_PIN",			"consultarStatusPin");
define("B2C_ACTION_REGISTER_SALE",				"registrarVenda");
define("B2C_ACTION_RESERVE_PIN",				"reservarPin");

// B2C Dados do Tipo de Pagamento
define("B2C_TIPO_PAGAMENTO_CARTAO_CREDITO",	"CARTAO_CREDITO");
define("B2C_TIPO_PAGAMENTO_BOLETO",			"BOLETO");
define("B2C_TIPO_PAGAMENTO_DINHEIRO",		"DINHEIRO");
define("B2C_TIPO_PAGAMENTO_CHEQUE",			"CHEQUE");
define("B2C_TIPO_PAGAMENTO_CARTAO_DEBITO",	"CARTAO_DEBITO");
define("B2C_TIPO_PAGAMENTO_CDC",			"CDC");
define("B2C_TIPO_PAGAMENTO_CARNE",			"CARNE");

// B2C Dados do Tipo de Venda
define("B2C_TIPO_VENDA_NOVO",	"NOVO");
define("B2C_TIPO_VENDA_AVULSO",	"AVULSO");

// B2C Dados do Tipo de Pessoa
define("B2C_TIPO_PESSOA_FISICA",	"FISICA");
define("B2C_TIPO_PESSOA_JURIDICA",	"JURIDICA");

// B2C Dados do Tipo de Sexo
define("B2C_TIPO_FEMININO",	"FEMININO");
define("B2C_TIPO_MASCULINO","MASCULINO");

// B2C Dados do Estado Civil
define("B2C_ESTADO_CIVIL_SOLTEIRO",		"SOLTEIRO");
define("B2C_ESTADO_CIVIL_CASADO",		"CASADO");
define("B2C_ESTADO_CIVIL_VIUVO",		"VIUVO");
define("B2C_ESTADO_CIVIL_DIVORCIADO",	"DIVORCIADO");
define("B2C_ESTADO_CIVIL_DESQUITADO",	"DESQUITADO");
define("B2C_ESTADO_CIVIL_COMPANHEIRO",	"COMPANHEIRO");
define("B2C_ESTADO_CIVIL_OUTROS",		"OUTROS");

// Tipo de Mensagem do Sistema
define("B2C_MSG_ERROR_LOG",				"ERROR_LOG");
define("B2C_MSG_TRANSACTION_LOG",		"TRANSACTION_LOG");

// B2C Dados do Status do PIN
define("B2C_PIN_STATUS_DISPONIVEL",		"DISPONIVEL");
define("B2C_PIN_STATUS_FATURADO",		"FATURADO");
define("B2C_PIN_STATUS_HABILITADO",		"HABILITADO");
define("B2C_PIN_STATUS_CANCELADO",		"CANCELADO");

// mensagens para usuário
define("B2C_MSG_USER_PARSING_WSDL",		"Este código de serviço não foi identificado (ERRO: WS758).<br>Por favor, verifique se o serviço foi selecionado corretamente ou entre em contato com o <a href='mailto:suporte@e-prepag.com.br'>suporte@e-prepag.com.br</a><br>");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_B2C_WS_ERRORS",		$raiz_do_projeto . "log/log_B2C_WS-Errors.log");

// Arquivo de Log onde serao registrados todos os cabecalhos de Request/Response
define("LOG_FILE_B2C_WS_TRANSACTIONS",	$raiz_do_projeto . "log/log_B2C_WS-Transactions.log");

// Arquivo com monitor de contatos ao WebService
define("B2C_MONITOR_FILE", $raiz_do_projeto . "log/monitor_B2C_online.txt");

// Arquivo com funções específicas para o módulo B2C
//include_once("B2C_functions.php");


// Classes do módulo B2C
include_once($raiz_do_projeto . "class/pdv/b2c/classGerais.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classBuscarFaixasPin.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classBuscarFaixasPinPorData.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classCalcularServicoCodigo.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classCalcularServicoPin.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classCancelarVenda.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classComplementarDadosVenda.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classConsultarStatusPin.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classRegistrarVenda.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classReservarPin.php");
include_once($raiz_do_projeto . "class/pdv/b2c/classB2C.php");

?>