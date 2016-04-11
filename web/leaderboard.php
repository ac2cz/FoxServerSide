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
        if ($n == 3) return "Fox-1Cliff";
        if ($n == 4) return "Fox-1D";
        return "FOX"; 
    }

    function latest($i) {
        global $DB, $conn, $PORT;
        $name=getName($i);
        $sql = "select count(date_time) from STP_HEADER where id=$i and  timestampdiff(MINUTE,date_time,now()) < 90;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die('Could not get data: ' . mysql_error());
        }
 
        $row = mysql_fetch_array($retval, MYSQL_ASSOC);
        echo "<strong>$name </strong>Frames Received last 90 mins : {$row['count(date_time)']} </br>";
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

         $sql = "select count(date_time) from STP_HEADER where id=$i and timestampdiff(HOUR,date_time,now()) < 24;";
          mysql_select_db($DB);
          $retval = mysql_query( $sql, $conn );
          if(! $retval ) {
             die('Could not get data: ' . mysql_error());
          }
 
          $row = mysql_fetch_array($retval, MYSQL_ASSOC);
          echo "Frames Received last 24 hours: {$row['count(date_time)']} </br>";

          $sql = "select count(receiver) from STP_HEADER where id=$i";
          mysql_select_db($DB);
          $retval = mysql_query( $sql, $conn );
          if(! $retval ) {
             die('Could not get data: ' . mysql_error());
          }
   
          $row = mysql_fetch_array($retval, MYSQL_ASSOC);

          echo	"Total Frames Since Launch: {$row['count(receiver)']} <br>".
	"<br>";
          echo	"<strong>$name:</strong> <a href=health.php?id=$i&port=$PORT>latest spacecraft health </a> <br>";
          echo "<br>";
    }

    $dbhost = 'localhost:3036';
    $dbuser = 'foxrpt';
    $dbpass = 'amsatfox';
    $id = $_GET['id'];
    $DB = $_GET['db'];
    $PORT = $_GET['port'];

    $where="where id=$id";
    $name=getName($id);
    if ($id == 'A') {
        $name = "FOX";
        $where="";
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
        "<td align='center'><strong>HighSpeed Frames</strong></td>";
   echo "<td rowspan=20>";
   if ($id=='A') {
      latest(1, $PORT);
      latest(3, $PORT);
      latest(4, $PORT);
   } else {
      latest($id, $PORT);
   }
   echo "</td>";
   echo	"</tr>";
   $sql = "select receiver, sum(case when source like '%duv' then 1 else 0 end) DUV, sum(case when source like '%highspeed' then 1 else 0 end) HighSpeed from STP_HEADER $where group by receiver order by DUV DESC";
   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
   
   while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
   {
      echo "<tr><td><a href=ground_station.php?id=$id&db=$DB&station={$row['receiver']}>{$row['receiver']}</a></td>  ".
         "<td align='center'>{$row['DUV']}</td>".
         "<td align='center'>{$row['HighSpeed']}</td> </tr> ";
   }
   echo "</table>";
   mysql_close($conn);
?>
</body>
</html>
