<?php

error_reporting(E_ALL);

$url = "https://dkmtools.fbk.eu/moki/multilingual/organiclingua/OntologyService/RequestManager.php";
 
$ch = curl_init();

$request = array("method" => "getOntology",
    "concept" => "",
    "keyword" => "",
    "langid" => "",
    "newtranslation" => "",
    "conceptlist" => "");

$defaults = array(CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_URL => $url,
    CURLOPT_FRESH_CONNECT => 1,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_FORBID_REUSE => 1,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_POSTFIELDS => http_build_query($request));

$options = array();

curl_setopt_array($ch, ($options + $defaults));
//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
//curl_setopt($ch, CURLOPT_PROXY, "http://proxy-address"); 

if (!$result = curl_exec($ch)) {
    echo "REQUEST FAILED:<br/>";
    echo curl_error($ch) . "<br/><br/>";
}
else
    echo $result;

curl_close($ch);