<html>
<head>
<title>Fox Spacecraft Health</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>

<?php
   $id = $_GET['id'];
   if (!is_numeric($id)) { die("invalid paramater"); }
   $a = file_get_contents("http://localhost:8080/frame/$id/1");
   echo ($a);
?>
