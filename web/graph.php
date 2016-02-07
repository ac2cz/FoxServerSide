<html>
<head>
<title>Fox Server Field Data</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>

<?php
   $arg1 = $_GET['sat'];
   $arg2 = $_GET['field'];
   $raw = $_GET['raw'];
   $reset = $_GET['reset'];
   $uptime = $_GET['uptime'];
   $num = $_GET['rows'];
   $raw='conv';
   #echo ("http://localhost:8080/field/$arg1/$arg2/$raw/$num/$reset/$uptime");
   $a = file_get_contents("http://localhost:8080/field/$arg1/$arg2/$raw/$num/$reset/$uptime");
   echo ($a);
?>
