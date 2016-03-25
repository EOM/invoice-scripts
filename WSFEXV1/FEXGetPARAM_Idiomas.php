<?php

/*
curl 
--header "Content-Type: text/xml;charset=UTF-8" 
--header "SOAPAction: http://ar.gov.afip.dif.FEV1/FEDummy" 
--data @ej.xml 
--sslv3 
https://wswhomo.afip.gov.ar/wsfev1/service.asmx
*/

date_default_timezone_set('America/Argentina/Buenos_Aires');

$SERVICIO = "FEXGetPARAM_Idiomas";

$TA = simplexml_load_file("../../WSAA/TA.xml");

$DESTINATION = $TA->header->destination;
$TOKEN = $TA->credentials->token;
$SIGN = $TA->credentials->sign;

$CUIT = explode(' ', 
          explode('=',
            explode(',', $DESTINATION)[2]
          )[1]
        )[1];

$AUTH  =  "<Auth>";
$AUTH .=    "<Token>$TOKEN</Token>";
$AUTH .=    "<Sign>$SIGN</Sign>";
$AUTH .=    "<Cuit>$CUIT</Cuit>";
$AUTH .=  "</Auth>";

$dummyReq  = "<?xml version='1.0' encoding='utf-8'?>";
$dummyReq .= "<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' ";
$dummyReq .= "xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'>";
$dummyReq .= "<soap:Body>";
$dummyReq .=  "<{$SERVICIO} xmlns='http://ar.gov.afip.dif.fexv1/'>";
$dummyReq .=   "{$AUTH}";
$dummyReq .=  "</{$SERVICIO}>";
$dummyReq .= "</soap:Body>";
$dummyReq .= "</soap:Envelope>";

$URL_SERVICE = "https://wswhomo.afip.gov.ar/wsfexv1/service.asmx";

$HEADER = [
  "Content-Type: text/xml; charset=utf-8",
  "SOAPAction: http://ar.gov.afip.dif.fexv1/{$SERVICIO}",
  "Content-Length: ".strlen($dummyReq)
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $URL_SERVICE);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_HTTPHEADER, $HEADER);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dummyReq);
curl_setopt($ch, CURLOPT_SSLVERSION, 3);

$response = curl_exec($ch);

if (curl_errno($ch)) {
  print "Error: " . curl_error($ch);
  exit;
}

curl_close($ch);

echo $response;

$response1 = str_replace("<soap:Body>","",$response);
$response2 = str_replace("</soap:Body>","",$response1);

$parser = simplexml_load_string($response2);

echo print_r($parser, true);

