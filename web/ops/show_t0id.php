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
$reset = $_GET['resets'];
$receiver = $_GET['receiver'];
$series = $_GET['series'];
if (!is_numeric($id)) { die("invalid paramater"); }
if ($reset <> "") {
    if (!is_numeric($reset)) { die("invalid paramater"); }
}
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

   <h1 class='entry-title'><?php echo $name ?> Time Zero</h1>
Use this form to retrieve a set of STP Header records from the database and calculate the Time Zero
(T0) for a given reset.  Specify the reset, a ground station name (receiver) and the number of records
to extract.  The latest records are pulled.
<p>
The estimated T0 is based on the STP datetime and the uptime of the satellite.  The ground station should
have an accurate clock and should be reporting frames real time, rather then reprocessing recordings
through FoxTelem. T0 is calculated to the nearest second.  More accuracy is not possible.
<p>
T0 is calculated as miliseconds since the time origin and is displayed after the data records.  Adjust this
value as follows: <BR><i>
Subtract 10000 for the 5 second delay marshalling frames on the spacecraft and the 5 second delay as FoxTelem
decodes the frame.  <br>
Subtract 1000 for each second of delay bewteen reception of the data and presentation of the
audio to FoxTelem.  e.g. A virtual audio cable introduces delay as does a seperate SDR.
</i>
<p>
Save the calcualted value and enter it into the <a href=edit_t0id.php?id=<?php echo $id?>&series=<?php echo $series?>>T0 file</a> 
<p>

<form action="<?=$PHP_SELF?>" method="get"> 
<style> td { border: 5px } th { background-color: lightgray; border: 3px solid lightgray; } td { padding: 5px; vertical-align: top; background-color: darkgray } </style>

<table>
<tr><th>From Reset</th><th>Reset</th><th>Receiver</th><th>Number</th></tr>
<tr><td>
<input type="text" name="from_reset"/> 
</td><td>
<input type="text" name="resets" value="<?php echo htmlspecialchars($reset); ?>"/> 
</td><td>
<input type="text" name="receiver" value="<?php echo htmlspecialchars($receiver); ?>"/> 
</td><td>
<input type="text" name="num"/> 
</td></tr>
</table>
<input type="checkbox" name="queryArchive" value="yes">Also query archive<br>
<br>
<input type="submit" />
<input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>"/>
</form>

<p>
<br>
<?php
    $fromReset = $_GET['from_reset'];
    $number = $_GET['num'];
    $query_archive = $_GET['queryArchive'];
    if ($reset <> "" && $receiver<> "") {
        if ($number == "") $number = 10;
        echo "<strong>For reset: $reset at Groundstation: $receiver</strong><br>";
        $a = file_get_contents("http://localhost:8080/T0/$id/$reset/$receiver/$number");
        echo ($a);
    } else if ($reset <> "" ) {
	$resetQuery = " resets=$reset";
	$orderBy = " order by uptime";
        if ($fromReset <> "") {
            $resetQuery = " resets >= $fromReset and resets <= $reset ";
	    $orderBy = " order by resets, uptime";
        }
        $limit = $number;
        if ($limit == "" || $limit > 100) {
            $limit = 100;
        }
        $dbhost = 'localhost';
        $dbuser = 'foxrpt';
        $dbpass = 'amsatfox';
        $DB = 'FOXDB';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $DB);
        if(mysqli_connect_errno($mysqli)) {
            # Not to be inthe production code
            #echo "Error: Failed to make a MySQL connection, here is why: <br>";
            #echo "Errno: " . mysqli_connect_errno($mysqli) . "<br>";
            #echo "Error: " . mysqli_connect_error($mysqli) . "<br>";
            die("No Connection<br>");
        }

        echo "<p><strong>Ground stations that submitted records for Reset $reset (limit $limit)</strong></p><table>";
        echo "<tr><td><b>Resets</b></td><td><b>Frames</b></td><td><b>Receiver</b></td></tr> ";
        $sql = "select resets, receiver, count(uptime) as count from STP_HEADER where id=$id and $resetQuery group by resets, receiver $orderBy ;";
        if ($result = mysqli_query($conn, $sql )) {
            while($row = mysqli_fetch_assoc($result)) {
                    $rowReset=$row['resets'];
                    $count=$row['count'];
                echo "<tr><td>{$rowReset}</td><td> {$count}</td><td> <a href=show_t0id.php?id=$id&db=$DB&resets=$rowReset&receiver={$row['receiver']}>{$row['receiver']}</a></td></tr> ";
            }
        } else { die('Could not get data: '); }

        echo"</table>";
        if ($query_archive == 'yes') {
            echo "<p><strong>Ground stations in the archive that submitted records for Reset $reset</strong></p><table>";
            echo "<tr><td><b>Resets</b></td><td><b>Frames</b></td><td><b>Receiver</b></td></tr> ";
            $sql = "select resets, receiver, count(uptime) as count from STP_HEADER_ARCHIVE where id=$id and $resetQuery group by resets, receiver $orderBy ;";
            if ($result = mysqli_query($conn, $sql )) {
                while($row = mysqli_fetch_assoc($result)) {
                    $rowReset=$row['resets'];
                    $count=$row['count'];
                    echo "<tr><td>{$rowReset}</td><td> {$count}</td><td> <a href=show_t0id.php?id=$id&db=$DB&resets=$rowReset&receiver={$row['receiver']}>{$row['receiver']}</a></td></tr> ";
                }
            } else { die('Could not get data: '); }

            echo"</table>";
        }
        mysqli_close($conn);

    } else {
        echo "<strong>You need to specify a reset and receiver (ground station name)</strong><br>"; 
        echo "<strong>Enter just a reset to get a list of the receivers that submitted data</strong><br>"; 
        echo "<strong>Enter just a 'from reset' and a reset to get a list of the receivers that submitted data for those resets and any in between </strong><br>"; 
    }
?>

<br>
<a href="operations.php">Go Back</a><br />
<a href="logout.php">Logout</a>
