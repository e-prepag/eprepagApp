<?php
	$chave256bits = "2jTayKz04aGd1Feb2IXTi9DZJJzO9kCX";

	function base64url_encode(string $data): string {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	function base64url_decode(string $data): string {
		return (string) base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	} 

?>