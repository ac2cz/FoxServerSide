<html>
<head>
<title>Fox Server Leaderboard</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<?php

    function getName($n) {
        if ($n == 1) return "Fox-1A";
        if ($n == 2) return "RadFxSat";
        if ($n == 3) return "Fox-1Cliff";
        if ($n == 4) return "Fox-1D";
        return "FOX"; 
    }

    function latest($i) {
        global $DB, $conn, $PORT;
        $name=getName($i);
        echo	"<a href=leaderboard.php?id=$i&db=$DB><strong>$name:</strong></a> <a href=health.php?id=$i&port=$PORT>latest spacecraft health </a> <br>";
        $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(MINUTE,date_time,now()) < 90;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die("Could not get 90 min data for $i : " . mysql_error());
        }
 
        $row = mysql_fetch_array($retval, MYSQL_ASSOC);
        echo "Frames last 90 mins : {$row['count(*)']} </br>";
        echo "From ground stations: <br>";
        $sql = "select distinct receiver from STP_HEADER where id=$i and timestampdiff(MINUTE,date_time,now()) < 90 order by resets desc, uptime desc;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die('Could not get data: ' . mysql_error());
        }
        $stations = 0; 
        while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
            echo " {$row['receiver']} ";
            $stations = $stations + 1;
            if ($stations == 5) {
                $stations=0;
                echo "<br>";
            }
         }
         echo "<br> ";
         echo "<br> ";

         $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(HOUR,date_time,now()) < 24;";
          mysql_select_db($DB);
          $retval = mysql_query( $sql, $conn );
          if(! $retval ) {
             die('Could not get data: ' . mysql_error());
          }
 
          $row = mysql_fetch_array($retval, MYSQL_ASSOC);
          echo "Frames Received last 24 hours: {$row['count(*)']} </br>";

          $sql = "select (select count(*) from STP_HEADER where id=1) + (select total from STP_ARCHIVE_TOTALS where id=1) as sumCount;";
          mysql_select_db($DB);
          $retval = mysql_query( $sql, $conn );
          if(! $retval ) {
             die('Could not get data: ' . mysql_error());
          }
   
          $row = mysql_fetch_array($retval, MYSQL_ASSOC);

          echo	"Total Frames since launch: {$row['sumCount']} <br>".
	"<br>";
          echo "<br>";
    }

    $dbhost = 'localhost:3036';
    $dbuser = 'g0kla';
    $dbpass = 'amsatfox';
    $id = $_GET['id'];
    $DB = $_GET['db'];
    $PORT = $_GET['port'];
    if ($id == "") { $id = "1"; }
    if ($DB == "") { $DB="FOXDB"; }
    $where="where STP_HEADER.id=$id";
    $archive_where="and STP_HEADER.id=STP_HEADER_ARCHIVE_COUNT.id and STP_HEADER.receiver=STP_HEADER_ARCHIVE_COUNT.receiver";
    $name=getName($id);
    if ($id == 'A') {
        $name = "FOX";
        $where="";
    	$archive_where="on STP_HEADER.receiver=STP_HEADER_ARCHIVE_COUNT.receiver";
    }
    echo "<h1 class=entry-title>$name Telemetry Leaderboard</h1> ";
    $conn = mysql_connect($dbhost, $dbuser, $dbpass);
   
   if(! $conn )
   {
      die('Could not connect: ' . mysql_error());
   }
   
   echo "<table cellspacing='0' cellpadding='0' width=1024 border='0'>";
   echo "<tr><td><strong>Ground station</strong></td>".
        "<td align='center'><strong>DUV Frames</strong></td>".
        "<td align='center'><strong>9k6 Frames</strong></td>".
        "<td align='center'><strong>Last 7 days</strong></td>";
   echo "<td rowspan=20>";
   if ($id=='A') {
      latest(1, $PORT);
      latest(2, $PORT);
      latest(3, $PORT);
      latest(4, $PORT);
   } else {
      latest($id, $PORT);
   }
   echo "</td>";
   echo	"</tr>";
   $sql = "call StpLeaderboardTotals()";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
   
   while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
   {
      echo "<tr><td><a href=ground_station.php?id=$id&db=$DB&station={$row['receiver']}>{$row['receiver']}</a></td>  ".
         "<td align='center'>{$row['DUV']}</td>".
         "<td align='center'>{$row['HighSpeed']}</td> ".
         "<td align='center'>{$row['last']}</td> </tr> ";
   }
   echo "</table>";
   mysql_close($conn);
?>
</body>
</html>
