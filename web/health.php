<html>
<head>
<title>Fox Spacecraft Health</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>

<?php
   $id = $_GET['id'];
   $port = $_GET['port'];
   $a = file_get_contents("http://localhost:$port/frame/$id/1");
   echo ($a);
?>
