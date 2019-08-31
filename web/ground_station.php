<html>
<head>
<title>Ground Station Details</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/generatepress/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<?php
    $DB="FOXDB";
    $STATION="";
    $dbhost = 'localhost';
    $dbuser = 'foxrpt';
    $dbpass = 'amsatfox';
    $id = $_GET['id'];
    $DB = $_GET['db'];
    $station = $_GET['station'];
    if (strpos($station, ' ') > 0) {
        $STATION = substr($station, 0, strpos($station, ' '));  
    } else {
        $STATION = $station;
    }

    if (!is_numeric($id)) { die("invalid paramater"); }
    if ($id == "") $id=1;
    if ($DB == "") $DB="FOXDB";
    if ($id == 1) $name="1A";
    if ($id == 2) $name="1B";
    if ($id == 3) $name="1Cliff";
    if ($id == 4) $name="1D";

    if ($id == 0) {
        $name="ALL";
        $idwhere="";
        echo "<h1 class=entry-title>All Fox: Ground Station $STATION - Last 7 days</h1>";
    } else {
        $idwhere="and id=$id";
        echo "<h1 class=entry-title>FOX-$name: Ground Station $STATION - Last 7 days</h1>";
    }
    $mysqli = mysqli_connect($dbhost, $dbuser, $dbpass, $DB);
    #$mysqli = mysqli_connect('localhost', $dbuser, $dbpass, $db);
    if(mysqli_connect_errno($mysqli)) {
       # Not to be inthe production code
       #echo "Error: Failed to make a MySQL connection, here is why: <br>";
       #echo "Errno: " . mysqli_connect_errno($mysqli) . "<br>";
       #echo "Error: " . mysqli_connect_error($mysqli) . "<br>";
        die("No Connection<br>");   
    }
    echo "<table cellspacing='0' cellpadding='0' width=1024 border='0'>";
    echo "<tr><td><strong>Ground station</strong></td>".
         "<td align='center'><strong>DUV Frames</strong></td>".
         "<td align='center'><strong>HighSpeed Frames</strong></td>";
   
    $sql = sprintf("select count(date_time) from STP_HEADER where receiver='%s' %s and timestampdiff(MINUTE,date_time,now()) < 90;",$STATION, $idwhere);
    if ($result = mysqli_query($mysqli, $sql )) {
        $row = mysqli_fetch_assoc($result);
        echo "<td rowspan=20><strong>Frames Received last 90 mins : </strong>{$row['count(date_time)']} </br>";
        mysqli_free_result($retval);
    } else {
        die('Could not get data: ');
        #die('Could not get data: ' . mysqli_error($mysqli));
    }
    $sql = sprintf("select count(date_time) from STP_HEADER where receiver='%s' %s and timestampdiff(HOUR,date_time,now()) < 24",$STATION, $idwhere);;
    if ($result = mysqli_query($mysqli, $sql )) {
        $row = mysqli_fetch_assoc($result);
         echo "<strong>Frames Received last 24 hours:</strong> {$row['count(date_time)']} </br>";
         echo "</td></tr>";
        mysqli_free_result($retval);
    } else {
        die('Could not get data: ');
    }
    
    $sql = sprintf("select receiver, sum(case when source like '%%duv' then 1 else 0 end) DUV, sum(case when source like '%%highspeed' then 1 else 0 end) HighSpeed from STP_HEADER where receiver='%s' %s and timestampdiff(DAY,date_time,now()) < 7 ",$STATION, $idwhere);
    if ($result = mysqli_query($mysqli, $sql )) {
        $row = mysqli_fetch_assoc($result);
        echo "<tr><td>{$row['receiver']}</td>  ".
         "<td align='center'>{$row['DUV']}</td>".
         "<td align='center'>{$row['HighSpeed']}</td> </tr> ";
        mysqli_free_result($retval);
    } else {
        die('Could not get data: ');
    }
 
    echo "</table>";
    mysqli_close($mysqli);
?>
<h3>Remaining functionality it temporarily unavailable</h3>
</body>
</html>
