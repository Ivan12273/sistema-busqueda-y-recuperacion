<?php

$url = "http://localhost:8983/solr/maincore/select?";

//Desactivar cors
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

function search($keyword) {
    //Hacer petición
    $ch = curl_init($GLOBALS['url'] . "q=" . $keyword);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $response = curl_exec($ch);
    curl_close($ch);
    if(!$response) {
        return false;
    }

    //Transformar respuesta en array
    $responseArray = json_decode($response, true); 
    foreach($responseArray["response"]["docs"] as $result) {
        echo 'Result';
        array_key_exists("author", $result) ? var_dump($result["author"][0]) : 'No disponible';
        array_key_exists("description", $result) ? var_dump($result["description"][0]) : 'No disponible';
        array_key_exists("url", $result) ? var_dump($result["url"][0]) : 'No disponible';
    }
} 

function facetSearch($keyword, $facetKeyword) {
    $ch = curl_init($GLOBALS['url'] . "facet.field=" . $facetKeyword . "&facet=on&q=" . $keyword);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $response = curl_exec($ch);
    curl_close($ch);
    if(!$response) {
        return false;
    }

    $responseArray = json_decode($response, true); 
    foreach($responseArray["facet_counts"]["facet_fields"][$facetKeyword] as $result) {
        if(is_numeric($result)){
            echo "Número de apariciones: " . $result;
        } else {
            echo $result;
        }
        echo "<br>";
    }
}

?>