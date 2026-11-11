<?php

function post_request($url, $headers, $body){
$curl = curl_init();

if(is_array($body)){$body = json_encode($body);}
curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_HTTPHEADER => $headers,
  CURLOPT_POSTFIELDS => $body,
));

$response = curl_exec($curl);

curl_close($curl);
return json_decode($response);

}

function get_request($url, $headers){
	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => $headers,
));

$response = curl_exec($curl);

curl_close($curl);
return json_decode($response);


}

$momo_token = '';

function momo_request_token(){

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://proxy.momoapi.mtn.co.rw/collection/token/',
  //CURLOPT_URL => 'https://webhook.site/f2d26267-deab-4f69-9cd9-e975223b9817',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '',
  CURLOPT_FAILONERROR => false,
  CURLOPT_HTTPHEADER => array(
    'Ocp-Apim-Subscription-Key: 56dae52e689a49c8b365bdbd0c7ed568',
    'Authorization: Basic YTU5N2M2M2ItMWZjMi00MzllLWFiZDktYzBhMTg0MTM0OTJjOjgxMTE3MmI1NjEyOTRlNzY4NDE5NjM4OTcwN2YyZjM4'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

return json_decode($response, true)['access_token'];

}

function momo_request_to_pay($id, $telephone, $amount){
$curl = curl_init();

$token = momo_request_token();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://proxy.momoapi.mtn.co.rw/collection/v1_0/requesttopay',
  //CURLOPT_URL => 'https://webhook.site/f7e67931-f29a-40f1-aa81-d9f5e166296f',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "amount": "'.$amount.'",
  "currency": "RWF",
  "externalId": "'.$id.'",
  "payer": {
    "partyIdType": "MSISDN",
    "partyId": "25'.$telephone.'"
  },
  "payerMessage": "IHUGURE APP '.$id.'",
  "payeeNote": "IHUGURE APP '.$id.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Ocp-Apim-Subscription-Key: 56dae52e689a49c8b365bdbd0c7ed568',
    'X-Target-Environment: mtnrwanda',
    'X-Reference-Id: 74ccc349-fb5b-4cf7-9a37-'.date('ymd').str_pad($id, 6, "0", STR_PAD_LEFT),
    'Content-Type: application/json',
    'X-Callback-Url: https://quiz.site.rw/callback.php',
	'Authorization: Bearer '.$token,
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

}

function momo_check_payment($id){

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://proxy.momoapi.mtn.co.rw/collection/v1_0/requesttopay/74ccc349-fb5b-4cf7-9a37-'.date('ymd').str_pad($id, 6, "0", STR_PAD_LEFT),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Ocp-Apim-Subscription-Key: 56dae52e689a49c8b365bdbd0c7ed568',
    'X-Target-Environment: mtnrwanda',
    'Authorization: Bearer '.momo_request_token(),
  ),
));

$response = curl_exec($curl);


curl_close($curl);
return json_decode($response, true)['status'];

}

function momo_transfer($id, $telephone, $amount){
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://proxy.momoapi.mtn.co.rw//disbursement/v1_0/transfer',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "amount": "'.$amount.'",
  "currency": "RWF",
  "externalId": '.$id.',
  "payee": {
    "partyIdType": "MSISDN",
    "partyId": "25'.$telephone.'"
  },
  "payerMessage": "IHUGURE APP",
  "payeeNote": "IHUGURE APP '.$id.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Ocp-Apim-Subscription-Key: c98cf1d5621749b5a550eb10228861b9',
    'X-Target-Environment: mtnrwanda',
    'X-Reference-Id: 74ccc349-fb5b-4cf7-9a38-'.date('ymd').str_pad($id, 6, "0", STR_PAD_LEFT),
    'Content-Type: application/json',
	'Authorization: Bearer '.momo_request_token(),
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

}

function momo_check_transfer($id){
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://proxy.momoapi.mtn.co.rw/disbursement/v1_0/transfer/74ccc349-fb5b-4cf7-9a38-'.date('ymd').str_pad($id, 6, "0", STR_PAD_LEFT),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_POSTFIELDS =>'{}',
  CURLOPT_HTTPHEADER => array(
    'Ocp-Apim-Subscription-Key: c98cf1d5621749b5a550eb10228861b9',
    'X-Target-Environment: mtnrwanda',
    'Content-Type: application/json',
	'Authorization: Bearer '.momo_request_token(),
  ),
));

$response = curl_exec($curl);

curl_close($curl);
return json_decode($response, true)['status'];

}


function momo_validate_phone($telephone){
	
}

//echo momo_request_token();
//momo_request_to_pay(1,'0789306256', 10);
//echo momo_check_payment(1);
//momo_transfer(1,'0789306256',10);
//echo momo_check_transfer(1);