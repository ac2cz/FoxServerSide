<?php

# MUST make sure no paramaters can be hijacked to execute something in the shell
$id = $_GET['id'];
if (!is_numeric($id)) { die("invalid paramater"); }

$image = escapeshellarg($_GET['image']);
$image = escapeshellarg($image);
$pc = $_GET['pc'];
if (!is_numeric($pc)) { die("invalid paramater"); }
$reset = $_GET['reset'];
if (!is_numeric($reset)) { die("invalid paramater"); }
$uptime = $_GET['uptime'];
if (!is_numeric($uptime)) { die("invalid paramater"); }
$zoom = $_GET['zoom'];
if (!is_numeric($zoom)) { die("invalid paramater"); }
$mapzoom = $_GET['mapZoom'];
if (!is_numeric($mapzoom)) { die("invalid paramater:".$mapzoom); }

$command ="/usr/bin/python showSubPage.py $id $image $pc $reset $uptime $zoom $mapzoom";
exec($command, $out, $status);
foreach($out as $result) {
    echo $result;
}
?>

