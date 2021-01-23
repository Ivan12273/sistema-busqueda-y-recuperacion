<?php include_once 'searchEngine.php'; ?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Buscador</title>
  <link href='https://fonts.googleapis.com/css?family=Arimo' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css"><link rel="stylesheet" href="./style.css">

</head>
<body>
<!-- partial:index.partial.html -->
<form class="filter-wrapper" action="" method="POST">
  <div class="keyword-wrapper">
    <input type="text" id="keyword" name="keyword" autocomplete="off" placeholder="Buscar..." required />
    <i id="keyword-button" class="fa fa-search"></i>  
  </div>
<ul id="filter-select" class="filter-select no-value">
</ul>
</form>
<!-- partial -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script><script  src="./script.js"></script>

<?php
  if(isset($_POST['keyword'])) {
    $keyword = $_POST["keyword"];
    echo search($keyword);
} 
?>

</body>
</html>
