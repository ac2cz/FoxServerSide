<html>
<head>
<title>Fox Server Field Data</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />

<style>
</style>

</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<h1 class="entry-title">FOX 1A - Oscar 85 </h1>

<?php
   $arg1 = $_GET['sat'];
   $arg2 = $_GET['field'];
   $a = file_get_contents("http://localhost:8080/$arg1/$arg2");
   echo ($a);
?>
