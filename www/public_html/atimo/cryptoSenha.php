<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	require_once "/www/class/classEncryption.php"; 
	
	$criptografia = new Encryption();
	
	$lista = [
		'@nikerick231',
		'huDie520',
		'Cyber-Mania1',
		'-ygj6bfHaNML',
		'010184a',
		'ro@250591',
		'123640eg@%%',
		'sena@4228',
		'nkmarcondes13',
		'Natalia2006@',
		'Kallinne@07',
		'sos4656',
		'Garcia@123',
		'GAME@1379',
		'd147741.',
		'@Mariana1011',
		'15091932Mh',
		'connect%0812',
		'bilheteria.com',
		'eumesmo1',
		'ml@!13120107',
		'KM@b620048',
		'hunter4242',
		'bitlok1:cel',
		'@napaula81',
		'@Santos34',
		'f5h2r4th',
		'Dbr6236@',
		'Na054015@',
		'Corolladrh5400',
		'marte2121@#$',
		'Bismillah93',
		'Alokad0101#',
		'$#@!wisley20',
		'fox759%%',
		'a680007',
		'starwars80?',
		'cubalibre$1',
		'@csr230709',
		'Hacker3212@',
		'denison1010@',
		'Agorasim1979Agorasim1979',
		'Senhasegura771',
		'4673759rnd',
		'gamesvideo1234',
		'@@ativa123@@',
		'srzg04',
		'86914551W?',
		'!Wb67156L',
		'power*34',
		'Cyber-Mania1',
		'#LiDiAnE17',
		'H@glailton28',
		'-ygj6bfHaNML',
		'A!s8xL3Q8CWZ',
		'tw08er53.',
		'marcel921900',
		'794613852aA',
		'Tenispolar1@',
		'ak1l3s10*'
	];
	
	foreach ($lista as $senha) {
		
		$senhaCriptografada = $criptografia->encrypt($senha);
		
		echo "'{$senhaCriptografada}',<br>";
		
	}