<html>
<head>
<title>Fox Spacecraft Health</title>

<!-- JBF 27 JUN 2017 Use style sheet from WordPress upgrade-->
<link rel="stylesheet" type="text/css" media="all" href="https://www.amsat.org/wordpress/wp-content/themes/generatepress/style.css" />

</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>

<?php
   $id = $_GET['id'];
   if (!is_numeric($id)) { die("invalid paramater"); }
   $a = file_get_contents("http://localhost:8080/getWod?sat=$id");
   $b = str_replace('Fox 1F', 'UW HuskySat', $a); 
   echo ($b);
?>
