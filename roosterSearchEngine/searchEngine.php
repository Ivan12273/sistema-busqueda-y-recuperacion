<?php

//Desactivar cors
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}

$url = "http://localhost:8983/solr/maincore/select?";

function search($keyword)
{
    //Verifica si está seleccionado el checkbox
    if (isset($_POST['relevancy'])) {
        $relevancy = "&sort=score%20asc";
    } else {
        $relevancy = "";
    }

    $keyword = trim($keyword);

    //Se quitan las palabras vacías
    $keyword = removeStopWords($keyword);

    //Sugerencia de corrección
    makeCorrectionSuggestion($keyword);

    //Hacer expansión semantica
    $newWords = makeSemanticExpansion($keyword);

    //Contruir query expandida
    $query = makeExpandedQuery($newWords);

    //Hacer petición
    $ch = curl_init("" . $query . $relevancy);
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

    //Sin resultados
    $numResults = $responseArray["response"]["numFound"];
    if ($numResults == 0) {
        echo ("<div class='container'");
        echo ("<p>No se encontro ningun documento que coincida con su busqueda.</p>");
        echo ("</div>");
        return false;
    }
    echo ("<div id='results' class='container'>");
    echo ("<p>Se han encontrado " . $numResults . " resultados:</p>");
    foreach ($responseArray["response"]["docs"] as $result) {
        array_key_exists("title", $result) ? $author = $result["title"][0] : $author = 'Desconocido';
        array_key_exists("og_description", $result) ? $desc = $result["og_description"][0] : $desc = $result["title_str"][0];
        array_key_exists("description", $result) ? $desc = $result["description"][0] : $desc = $result["title_str"][0];
        array_key_exists("url", $result) ? $url = $result["url"][0] : $url = 'Desconocido';

        echo ("<div class='content'>");
        echo ("<small><strong>" . $url . "</strong></small>");
        echo ("<a class='link' href='" . $url . "'>" . $author . "</a>");
        echo ("<small class='desscription'>" . $desc . "</small>");
        echo ("</div>");
    }
    echo ("</div>");
}

// Devuelve un arreglo con 10 palabras nuevas para la expansión
function makeSemanticExpansion($keyword)
{

    $keyword = str_replace(' ', '+', $keyword);

    // Expansión en español
    $query = "https://api.datamuse.com/words?ml=" . $keyword . "&v=es";
    //Expansión en ingles
    // $query = "https://api.datamuse.com/words?ml=" . $keyword . "&v=en";


    $dm = curl_init($query);
    curl_setopt($dm, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($dm, CURLOPT_CUSTOMREQUEST, "GET");
    $response2 = curl_exec($dm);

    $responseArray = json_decode($response2, true);

    $words = [];
    array_push($words, urlencode($keyword));

    $i = 0;
    foreach ($responseArray as $el) {
        if ($i < 8) {
            array_push($words, urlencode($el["word"]));
            $i++;
        }
    }

    return $words;
}

function makeCorrectionSuggestion($keyword)
{
    $keyword = str_replace(' ', '+', $keyword);
    $query = "https://api.datamuse.com/words?sl=" . $keyword . "&v=es";

    $dm = curl_init($query);
    curl_setopt($dm, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($dm, CURLOPT_CUSTOMREQUEST, "GET");
    $response2 = curl_exec($dm);

    $responseArray = json_decode($response2, true);

    $words = [];

    $i = 0;
    foreach ($responseArray as $el) {
        if ($i < 8) {
            array_push($words, $el["word"]);
            $i++;
        }
    }

    if ($keyword != $words[0]) {
        echo "<form method='POST'class='container'>";
        echo "<p>¿Habrás querido decir ";
        echo "<input type='submit' name='keyword' class='query-correction' name='query' value='$words[0]'/>?</p>";
        echo "<br><br>";
        echo "</form>";
    }
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
    // echo ("<br>" . $query . "<br>");
    return $query;
}

function facetSearch($keyword, $facetKeyword)
{

    //Verifica si está seleccionado el checkbox
    if (isset($_POST['relevancy'])) {
        $relevancy = "&sort=score%20asc";
    } else {
        $relevancy = "";
    }

    $keyword = trim($keyword);

    $ch = curl_init($GLOBALS['url'] . "facet.field=" . $facetKeyword . "&facet=on&q=" . $keyword . $relevancy);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        echo ("No se encontraron resultados en la búsqueda facetada.");
        return false;
    }

    $responseArray = json_decode($response, true);

    //Sin resultados
    $numResults = $responseArray["response"]["numFound"];
    if ($numResults == 0) {
        echo ("No se encontraron resultados en la búsqueda facetada.");
        return false;
    }

    if (array_key_exists("facet_counts", $responseArray)) {
        echo ("<div id='results' class='container'>");
        echo ("<p>Resultados de búsqueda facetada: </p>");
        foreach ($responseArray["facet_counts"]["facet_fields"][$facetKeyword] as $result) {
            if (is_int($result)) {
                echo ("<small class='desscription'>Número de resultados: " . $result . "</small>");
                echo ("</div>");
            } else {
                echo ("<div class='content'>");
                echo ("<a class='link'>" . $result . "</a>");
            }
        }
    } else {
        echo ("No se encontraron resultados en la búsqueda facetada.");
        return false;
    }
    echo ("</div>");
}

function removeStopWords($keyword)
{
    $stopWords = [];
    $handle = fopen("../stop-words.txt", "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            array_push($stopWords, $line);
        }
        fclose($handle);
    }

    $words = explode(' ', $keyword);

    for ($i = 0; $i < sizeof($words); $i++) {

        if (in_array($words[$i], $stopWords)) {
            unset($words[$i]);
        }
    }

    $kword = implode(" ", $words);

    return $kword;
}
