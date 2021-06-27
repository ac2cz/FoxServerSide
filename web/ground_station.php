<html>
<head>
<title>Ground Station Details</title>
<?php include "head.php"; ?>
<?php include "getName.php"; ?>
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
    $name=getName($id);

    if ($id == 0) {
        $name="ALL";
        $idwhere="";
        echo "<h1 class=entry-title>All Fox: Ground Station $STATION - Last 7 days</h1>";
    } else {
        $idwhere="and id=$id";
        echo "<h1 class=entry-title>$name: Ground Station $STATION - Last 7 days</h1>";
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
         "<td align='center'><strong>HighSpeed Frames</strong></td>".
         "<td align='center'><strong>PSK Frames</strong></td>";
   
    $sql = sprintf("select count(date_time) from STP_HEADER where receiver='%s' %s and timestampdiff(MINUTE,date_time,now()) < 90;",$STATION, $idwhere);
    if ($result = mysqli_query($mysqli, $sql )) {
        $row = mysqli_fetch_assoc($result);
        echo "<td rowspan=20><strong>Frames Received last 90 mins : </strong>{$row['count(date_time)']} </br>";
        mysqli_free_result($result);
    } else {
        die('Could not get data: ');
        #die('Could not get data: ' . mysqli_error($mysqli));
    }
    $sql = sprintf("select count(date_time) from STP_HEADER where receiver='%s' %s and timestampdiff(HOUR,date_time,now()) < 24",$STATION, $idwhere);;
    if ($result = mysqli_query($mysqli, $sql )) {
        $row = mysqli_fetch_assoc($result);
         echo "<strong>Frames Received last 24 hours:</strong> {$row['count(date_time)']} </br>";
         echo "</td></tr>";
        mysqli_free_result($result);
    } else {
        die('Could not get data: ');
    }
    
    $sql = sprintf("select receiver, sum(case when source like '%%duv' then 1 else 0 end) DUV, sum(case when source like '%%highspeed' then 1 else 0 end) HighSpeed,  sum(case when source like '%%bpsk' then 1 else 0 end) PSK from STP_HEADER where receiver='%s' %s and timestampdiff(DAY,date_time,now()) < 7 ",$STATION, $idwhere);
    if ($result = mysqli_query($mysqli, $sql )) {
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['receiver']}</td>  ".
             "<td align='center'>{$row['DUV']}</td>".
             "<td align='center'>{$row['HighSpeed']}</td> ".
             "<td align='center'>{$row['PSK']}</td></tr> ";
        }
        mysqli_free_result($result);
    } else {
        die('Could not get data: ');
    }
 
    echo "</table>";

    if ($id != 0) {
       echo "<p><h2>Payloads from this Spacecraft:</h2>";

       $sql = sprintf("select count(*) as count from Fox%dRTTELEMETRY t, STP_HEADER s where t.id=%d and t.id=s.id and s.type=1 and t.resets=s.resets and t.uptime=s.uptime and s.receiver='%s' and timestampdiff(DAY,date_time,now()) < 7 ",$id,$id,$STATION);
        if ($result = mysqli_query($mysqli, $sql )) {
            $row = mysqli_fetch_assoc($result);
            $RTPAYLOADS="{$row['count']}";
            echo "Real Time Payloads: $RTPAYLOADS <br>";
            mysqli_free_result($result);
        } else { die('Could not get data: '); }
    
       $sql = sprintf("select count(*) as count from Fox%dMAXTELEMETRY t, STP_HEADER s where t.id=%d and t.id=s.id and s.type=2 and t.resets=s.resets and t.uptime=s.uptime and s.receiver='%s' and timestampdiff(DAY,date_time,now()) < 7 ",$id,$id,$STATION);
        if ($result = mysqli_query($mysqli, $sql )) {
            $row = mysqli_fetch_assoc($result);
            $MAXPAYLOADS="{$row['count']}";
            echo "Max Payloads: $MAXPAYLOADS </br>";
            mysqli_free_result($result);
        } else { die('Could not get data: '); }
    
       $sql = sprintf("select count(*) as count from Fox%dMINTELEMETRY t, STP_HEADER s where t.id=%d and t.id=s.id and s.type=3 and t.resets=s.resets and t.uptime=s.uptime and s.receiver='%s' and timestampdiff(DAY,date_time,now()) < 7 ",$id,$id,$STATION);
        if ($result = mysqli_query($mysqli, $sql )) {
            $row = mysqli_fetch_assoc($result);
            $MINPAYLOADS="{$row['count']}";
            echo "Min Payloads: $MINPAYLOADS </br>";
            mysqli_free_result($result);
        } else { die('Could not get data: '); }
    
       $sql = sprintf("select count(*) as count from Fox%dRADTELEMETRY t, STP_HEADER s where t.id=%d and t.id=s.id and s.type=4 and t.resets=s.resets and t.uptime=s.uptime and s.receiver='%s' and timestampdiff(DAY,date_time,now()) < 7 ",$id,$id,$STATION);
        if ($result = mysqli_query($mysqli, $sql )) {
            $row = mysqli_fetch_assoc($result);
            $RADPAYLOADS="{$row['count']}";
            $TOTAL=$RTPAYLOADS + $MAXPAYLOADS + $MINPAYLOADS ;
            echo "<strong>Total Fox Telemetry Payloads:</strong> $TOTAL </br>";
            echo "Experiment Payloads: $RADPAYLOADS </br>";
            $TOTAL=$RTPAYLOADS + $MAXPAYLOADS + $MINPAYLOADS + $RADPAYLOADS;
            echo "<strong>Total Payloads:</strong> $TOTAL </br>";
            mysqli_free_result($result);
        } else { echo('Experiment: Could not get data '); }
    }

    # Now display the station details that we have stored
    echo "<p><h2>Using Demodulator:</h2>";
    $sql = sprintf("select demodulator from STP_HEADER where receiver='%s' %s order by date_time DESC limit 1",$STATION, $idwhere);
    if ($result = mysqli_query($mysqli, $sql )) {
        $row = mysqli_fetch_assoc($result);
        echo "- {$row['demodulator']}<br> ";
        mysqli_free_result($result);
    } else { die('Could not get data: '); }
 
    echo "<p><h2>Station Receiver(s):</h2>";
    $sql = sprintf("select distinct(receiver_rf) from STP_HEADER where receiver_rf != 'NONE' and receiver='%s' %s",$STATION, $idwhere);
    if ($result = mysqli_query($mysqli, $sql )) {
        while($row = mysqli_fetch_assoc($result)) {
            echo "- {$row['receiver_rf']}<br> ";
        }
        mysqli_free_result($result);
    } else { die('Could not get data: '); }
 

    mysqli_close($mysqli);
?>
</body>
</html>
