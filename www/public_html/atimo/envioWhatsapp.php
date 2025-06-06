<?php
/*
$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.chatpro.com.br/painel/ws/endpoint.php?action=token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => '{"email":"andresilva@gokeitecnologia.com.br","senha":"nascimento200218"}',
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "content-type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  //echo $response;
}

//exit;
$token = json_decode($response, true);

*/


$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://v5.chatpro.com.br/chatpro-00e1a0d0cf/api/v1/send_message",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => '{"number":"5511977201837","message":"codigo de verificação: 4654658"}',
  CURLOPT_HTTPHEADER => [
    "Authorization: 0198fca6c24ebec3b0123264f5408ec9",
    "accept: application/json",
    "content-type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

/*
$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.chatpro.com.br/painel/ws/endpoint.php?action=saldo",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer ". $token["token"],
    "accept: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

*/


?>