<html>
<head>
<title>Fox Spacecraft Health</title>

<?php include "head.php"; ?>

</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>

<?php
   $id = $_GET['id'];
   if (!is_numeric($id)) { die("invalid paramater"); }
   $a = file_get_contents("http://localhost:8080/getWod?sat=$id");
   $b = str_replace('Fox 1F', 'UW HuskySat', $a); 
   $b = str_replace('Fox 10', 'MESAT-1', $a);
   echo ($b);
?>
