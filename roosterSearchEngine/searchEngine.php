<?php

function search($keyword)
{
    //Desactivar cors
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
        die();
    }

    //Hacer expansión semantica
    $newWords = makeSemanticExpansion($keyword);

    //Contruir query expandida
    $query = makeExpandedQuery($newWords);
    // $query = "http://localhost:8983/solr/maincore/select?q=google!search";
    // $query = "http://localhost:8983/solr/maincore/select?q=google!valley!adsense!adwords!yahoo!bin";

    //Hacer petición
    // $ch = curl_init("http://localhost:8983/solr/maincore/select?q=" . $keyword);
    $ch = curl_init("" . $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        echo ("No se encontro ningun documento que coincida con su busqueda.");
        return false;
    }

    //Transformar respuesta en array
    $responseArray = json_decode($response, true);
    // echo ("<br>" . $response);

    // Sin resultados
    if ($responseArray["response"]["numFound"] == 0) {
        echo ("No se encontro ningun documento que coincida con su busqueda.");
    }

    foreach ($responseArray["response"]["docs"] as $result) {
        echo 'Result <br>';
        array_key_exists("author", $result) ? var_dump($result["author"][0]) : 'No disponible';
        array_key_exists("description", $result) ? var_dump($result["description"][0]) : 'No disponible';
        array_key_exists("url", $result) ? var_dump($result["url"][0]) : 'No disponible';
    }
}

// Devuelve un arreglo con 10 palabras nuevas para la expansión
function makeSemanticExpansion($keyword)
{
    $dm = curl_init("https://api.datamuse.com/words?ml=" . $keyword);
    curl_setopt($dm, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($dm, CURLOPT_CUSTOMREQUEST, "GET");
    $response2 = curl_exec($dm);

    $responseArray = json_decode($response2, true);
    $words = [];
    array_push($words, $keyword);

    for ($i = 0; $i < 10; $i++) {
        array_push($words, $responseArray[$i]["word"]);
    }
    return $words;
}


function makeExpandedQuery($newWords)
{
    $baseUrl = "http://localhost:8983/solr/maincore/select?q=";

    $buildQuery = "";
    foreach ($newWords as $word) {
        //Si es una oración de dos o más palabras las unimos con "+"
        if (strpos($word, " ")) {
            $composedWords = explode(" ", $word);
            $newWord = "";
            foreach ($composedWords as $wrd) {
                $newWord = $newWord . $wrd . "+";
            }
            $word = substr($newWord, 0, -1);
            $buildQuery = $buildQuery . $word . "!";
        } else {
            $buildQuery = $buildQuery . $word . "!";
        }
    }

    $buildQuery = substr($buildQuery, 0, -1);

    $query = $baseUrl . $buildQuery;

    return $query;
}
