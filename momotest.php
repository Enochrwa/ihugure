<?php

require_once 'services/momo.php';

function momo_request_to_withdraw($id, $telephone, $amount){
$curl = curl_init();

$token = momo_request_token();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://proxy.momoapi.mtn.co.rw/collection/v1_0/requesttowithdraw',
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
  "payerMessage": "Test Me",
  "payeeNote": "Test Me"
}',
  CURLOPT_HTTPHEADER => array(
    'Ocp-Apim-Subscription-Key: 56dae52e689a49c8b365bdbd0c7ed568',
    'X-Target-Environment: mtnrwanda',
    'X-Reference-Id: 74ccc349-fb5b-4cf7-8a37-'.date('ymd').str_pad($id, 6, "0", STR_PAD_LEFT),
    'Content-Type: application/json',
	'Authorization: Bearer '.$token,
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

}

//momo_request_to_withdraw(1, '0789306256', 10);
momo_request_to_pay(3, '0789306256', 10);