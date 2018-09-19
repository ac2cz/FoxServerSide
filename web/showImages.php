<?php

$id = $_GET['id'];
if (!is_numeric($id)) { die("invalid paramater"); }

$startImage = $_GET['start'];
$reverse = $_GET['reverse'];
if ($id == 3)
    $imgDir = "fox1c/images";
if ($id == 4)
    $imgDir = "fox1d/images";
$command ="/usr/bin/python showImages.py $id $imgDir $startImage $reverse";
exec($command, $out, $status);
foreach($out as $result) {
    echo $result;
}
?>



