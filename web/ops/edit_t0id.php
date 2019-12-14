<?php 

    // First we execute our common code to connection to the database and start the session 
    require("common.php"); 
     
    // At the top of the page we check to see whether the user is logged in or not 
    if(empty($_SESSION['user'])) 
    { 
        // If they are not, we redirect them to the login page. 
        header("Location: login.php"); 
         
        // Remember that this die statement is absolutely critical.  Without it, 
        // people can view your members-only content without logging in. 
        die("Redirecting to login.php"); 
    } 
    if(($_SESSION['user']['admin']) < '5') {
        die("Operations rights are required to change T0");
    }

     
    // Everything below this point in the file is secured by the login system 
    // We can display the user's username to them by reading it from the session 
    // array.  Remember that because a username is user submitted content we must 
    //use htmlentities on it before displaying it to the user. 
?> 
<?php include "../getName.php";
$id = $_GET['id'];
if (!is_numeric($id)) { die("invalid paramater"); }
if (id < 0 || $id > 255) { die("invalid FoxId"); }
if ($id == "") { $id = "1"; }
$name=getName($id);
?>

<html>
<head>
<title><?php echo $name?> Time 0 for Resets</title>
</head>
<body>
<?php include "header.php"; ?>
<h1 class="entry-title">Time Zero for Resets on <?php echo $name?></h1>
This is the T0 file that FoxTelem downloads when it starts.  Add new rows to the end of the file when
the spacecraft experiences a reset.  The format is <i> reset, T0 in milliseconds</i>
<p>
First <a href=show_t0id.php?id=<?php echo $id?>>calculate the value for T0</a> then enter it as a new row at the end of the file. Save the file so it is available to FoxTelem.  It is written into the same directory as this php script.
<p>
The T0 file must be continuous and without gaps.  If we experience a gap in the resets e.g. the new reset is 899
when the last reset was 500, then you can use the form below to add a set of rows.  Enter the Start Reset, the End Reset and the time you want them to have.  Then hit "Add Rows".  A row will be added for each reset.
<?php
date_default_timezone_set('UTC');
$today = date("YmdHis");
$file_path= "FOX".$id."T0.txt";
// Open the file to get existing content
$text = file_get_contents($file_path);
if($_POST["save"]) {
    file_put_contents($file_path, $_POST['update']); 
}
if($_POST["backup"]) {
    file_put_contents($file_path.$today, $text); 
}
if($_POST["add"]) {
    $start=$_POST['startreset'];
    $end=$_POST['endreset'];
    $time=$_POST['time'];

    if (!empty($end) && !empty($start)) {
        for ($i = $start; $i <= $end; $i++) {
            $data=$data.PHP_EOL.$i.",".$time;
        }
        file_put_contents($file_path, $data, FILE_APPEND); 
    } else 
    if (!empty($start)) {
	$data=$start.",".$time;
        file_put_contents($file_path, PHP_EOL.$data, FILE_APPEND); 
    }
}
// Now get the file again because it was modified
$text = file_get_contents($file_path);
?>

<form action="<?=$PHP_SELF?>" method="post"> 
<textarea Name="update" cols="20" rows="10"><?=$text?></textarea><br/> 
<input type="submit" value="Save" name="save"/>
<input type="submit" value="Backup" name="backup"/> <br>
</form>
<br>
<form action="<?=$PHP_SELF?>" method="post"> 
<table>
<tr><th>Start Reset</th><th>End Reset</th><th>Time</tr>
<tr><td>
<input type="text" name="startreset"/> 
</td><td>
<input type="text" name="endreset"/> 
</td><td>
<input type="text" name="time"/> 
</td></tr>
</table>
<input type="submit" value="Add Rows" name="add"/>
</form>

<p>
<br>
<a href="operations.php">Go Back</a><br />
<a href="logout.php">Logout</a>
