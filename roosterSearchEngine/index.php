<?php include_once 'searchEngine.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Buscador</title>
  <link href='https://fonts.googleapis.com/css?family=Arimo' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="./style.css">

</head>

<body>

  <!-- partial:index.partial.html -->
  <form class="filter-wrapper" method="POST">
    <img class="image-logo" src="../images/logo.png" alt="Rooster">
    <div class="keyword-wrapper">
      <input type="text" id="keyword" name="keyword" autocomplete="off" placeholder="Buscar..." />
      <i id="keyword-button" class="fa fa-search"><input type="submit" style="display: none;"></i>
    </div>
    <br>
    <div class="keyword-wrapper">
      <input type="checkbox" id="relevancy" name="relevancy" value="true">
      <label for="relevancy">R.P. Ascendente</label>
      <input type="checkbox" id="facet" name="facet" value="true">
      <label for="facet">Busqueda facetada: </label>
      <input id="facet-input" name="facet-keyword" type="text" autocomplete="off" placeholder="Ingresa el campo..." />
    </div>
    <ul id="filter-select" class="filter-select no-value"></ul>
  </form>

  <!-- partial -->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
  <script src="./script.js"></script>

  <?php
  if (isset($_POST['keyword']) && !isset($_POST['facet'])) {
    $keyword = $_POST["keyword"];
    echo search($keyword);
  }
  if (isset($_POST['facet'])) {
    $keyword = $_POST["keyword"];
    $facetKeyword = $_POST["facet-keyword"];
    echo search($keyword);
    echo facetSearch($keyword, $facetKeyword);
  }
  ?>

</body>

</html>