<?php

function search($keyword) {
    //Desactivar cors
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    $method = $_SERVER['REQUEST_METHOD'];
    if($method == "OPTIONS") {
        die();
    }
    
    //Hacer petición
    $ch = curl_init("http://localhost:8983/solr/maincore/select?q=" . $keyword);
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

?>