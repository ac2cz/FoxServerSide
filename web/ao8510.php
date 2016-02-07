<html>
<head>
<title>Fox-1A Current Health</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<h1 class="entry-title">FOX 1A - Oscar 85 </h1>

<?php

   $a = file_get_contents("http://localhost:8080/1A");
   echo ($a);
?>
