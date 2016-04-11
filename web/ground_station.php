<html>
<head>
<title>Ground Station Details</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<?php
    $DB="FOXDB";
    $STATION="";
    $dbhost = 'localhost:3036';
    $dbuser = 'foxrpt';
    $dbpass = 'amsatfox';
    $id = $_GET['id'];
    $DB = $_GET['db'];
    $STATION = $_GET['station'];

    if ($id == "") $id=1;
    if ($DB == "") $DB="FOXDB";
    if ($id == 1) $name="1A";
    if ($id == 3) $name="1Cliff";
    if ($id == 4) $name="1D";
    $where1="id=$id and";
    if ($id == 'A') {
        $where1="";
    }

    echo "<h1 class=entry-title>FOX-$name: Ground Station $STATION</h1>";
    $conn = mysql_connect($dbhost, $dbuser, $dbpass);
   
   if(! $conn )
   {
      die('Could not connect: ' . mysql_error());
   }
   
   echo "<table cellspacing='0' cellpadding='0' width=1024 border='0'>";
   echo "<tr><td><strong>Ground station</strong></td>".
        "<td align='center'><strong>DUV Frames</strong></td>".
        "<td align='center'><strong>HighSpeed Frames</strong></td>";

  $sql = "select count(date_time) from STP_HEADER where $where1 receiver='$STATION' and timestampdiff(MINUTE,date_time,now()) < 90;";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get 90 min data: ' . mysql_error());
   }
 
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   echo "<td rowspan=20><strong>Frames Received last 90 mins : </strong>{$row['count(date_time)']} </br>";

  $sql = "select count(date_time) from STP_HEADER where $where1 receiver='$STATION' and timestampdiff(HOUR,date_time,now()) < 24;";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
 
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   echo "<strong>Frames Received last 24 hours:</strong> {$row['count(date_time)']} </br>";

   echo	"</td>".
	"</tr>";
   $sql = "select receiver, sum(case when source like '%duv' then 1 else 0 end) DUV, sum(case when source like '%highspeed' then 1 else 0 end) HighSpeed from STP_HEADER where $where1 receiver='$STATION'";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) { die('Could not get data: ' . mysql_error()); }
   
   while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
   {
      echo "<tr><td>{$row['receiver']}</td>  ".
         "<td align='center'>{$row['DUV']}</td>".
         "<td align='center'>{$row['HighSpeed']}</td> </tr> ";
   }

   echo "</table>";
   
   
	$where="t.id=$id and s.id=$id and";
    if ($id == 'A') {
	$where="";
    }
   $sql = "select count(*) as count from Fox1RTTELEMETRY t, STP_HEADER s where $where t.resets=s.resets and t.uptime=s.uptime and s.receiver='$STATION'";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) { die('Could not get data: ' . mysql_error()); }
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   $RTPAYLOADS="{$row['count']}";
   echo "<br>Real Time Payloads: $RTPAYLOADS </br>";

   $sql = "select count(*) as count from Fox1MAXTELEMETRY t, STP_HEADER s where $where t.resets=s.resets and t.uptime=s.uptime and s.receiver='$STATION'";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) { die('Could not get data: ' . mysql_error()); }
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   $MAXPAYLOADS="{$row['count']}";
   echo "Max Payloads: $MAXPAYLOADS </br>";

   $sql = "select count(*) as count from Fox1MINTELEMETRY t, STP_HEADER s where $where t.resets=s.resets and t.uptime=s.uptime and s.receiver='$STATION'";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) { die('Could not get data: ' . mysql_error()); }
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   $MINPAYLOADS="{$row['count']}";
   echo "Min Payloads: $MINPAYLOADS </br>";

   $sql = "select count(*) as count from Fox1RADTELEMETRY t, STP_HEADER s where $where t.resets=s.resets and t.uptime=s.uptime and s.receiver='$STATION'";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) { die('Could not get data: ' . mysql_error()); }
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   $RADPAYLOADS="{$row['count']}";
   $TOTAL=$RTPAYLOADS + $MAXPAYLOADS + $MINPAYLOADS ;
   echo "<strong>Total Fox Telemetry Payloads:</strong> $TOTAL </br>";
   echo "Experiment Payloads: $RADPAYLOADS </br>";
   $TOTAL=$RTPAYLOADS + $MAXPAYLOADS + $MINPAYLOADS + $RADPAYLOADS;
   echo "<strong>Total Payloads:</strong> $TOTAL </br>";

   echo "<p><h2>Using Demodulator:</h2>";
   $sql = "select demodulator from STP_HEADER where $where1 receiver='$STATION' order by date_time DESC limit 1";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) { die('Could not get data: ' . mysql_error()); }
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   echo "- {$row['demodulator']}<br> ";

   echo "<p><h2>Station Receiver(s):</h2>";
   $sql = "select distinct(receiver_rf) from STP_HEADER where $where1 receiver_rf != 'NONE' and receiver='$STATION'";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) { die('Could not get data: ' . mysql_error()); }
   
   while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
   {
      echo "- {$row['receiver_rf']}<br> ";
   }

   mysql_close($conn);
?>
</body>
</html>
