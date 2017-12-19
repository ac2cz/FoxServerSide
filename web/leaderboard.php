<html>
<head>
<title>Fox Server Leaderboard</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/generatepress/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<?php

    function getName($n) {
        if ($n == 1) return "AO-85 (Fox-1A)";
        if ($n == 2) return "RadFxSat (Fox-1B)";
        if ($n == 3) return "Fox-1Cliff";
        if ($n == 4) return "Fox-1D";
        if ($n == 5) return "Fox-1E";
        return "FOX"; 
    }

    function latest($i, $imageDir) {
        global $DB, $conn, $PORT;
        $name=getName($i);
        echo "<a href=leaderboard.php?id=$i&db=$DB><strong class=entry-title>$name</strong></a> <a href=health.php?id=$i&port=$PORT>latest spacecraft health </a>";
        if ($imageDir != "")
            echo "| <a href=$imageDir>Camera Images</a>";
        echo " <br>";

        $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(HOUR,date_time,now()) < 24;";
          mysql_select_db($DB);
          $retval = mysql_query( $sql, $conn );
          if(! $retval ) {
             die('Could not get data: ' . mysql_error());
          }
 
          $row = mysql_fetch_array($retval, MYSQL_ASSOC);
          echo "Frames - last 24 hours: ".number_format($row['count(*)'])." ";

        $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(MINUTE,date_time,now()) < 90;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die("Could not get 90 min data for $i : " . mysql_error());
        }
        $row = mysql_fetch_array($retval, MYSQL_ASSOC);
        echo " - last 90 mins: ".number_format($row['count(*)'])." ";

         # Now calculate the total for this sat and display it
          $sql = "select (select count(*) from STP_HEADER where id=$i) as sumCountHeader;";
          mysql_select_db($DB);
          $retval = mysql_query( $sql, $conn );
          if(! $retval ) {
             die('Could not get data: ' . mysql_error());
          }
   
          $row1 = mysql_fetch_array($retval, MYSQL_ASSOC);
          $headerCount=$row1['sumCountHeader'];

          $sql = "select (select total from STP_ARCHIVE_TOTALS where id=$i) as sumCountArchive;";
          mysql_select_db($DB);
          $retval = mysql_query( $sql, $conn );
          if(! $retval ) {
             die('Could not get data: ' . mysql_error());
          }
   
          $row2 = mysql_fetch_array($retval, MYSQL_ASSOC);
          $archiveCount=$row2['sumCountArchive'];
	  $totalCount=$headerCount+$archiveCount;
          echo	"- since launch: ".number_format($totalCount)." <br>";

        echo "From ground stations: <br>";
        $sql = "select distinct receiver from STP_HEADER where id=$i and timestampdiff(MINUTE,date_time,now()) < 90 order by resets desc, uptime desc;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die('Could not get data: ' . mysql_error());
        }
        $stations = 0; 
        while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
            echo "<a href=ground_station.php?id=$i&db=$DB&station={$row['receiver']}>{$row['receiver']}</a> ";
            $stations = $stations + 1;
            if ($stations == 5) {
                $stations=0;
                echo "<br>";
            }
         }
         echo "<br> ";
         echo "<br> ";

    }

    $dbhost = 'localhost:3036';
    $dbuser = 'foxrpt';
    $dbpass = 'amsatfox';

    $id = $_GET['id'];
    $show = $_GET['show'];
    if ($show == "")
        $ROW_LIMIT=13;
    else
        $ROW_LIMIT=999999;
    if (!is_numeric($id)) { die("invalid paramater"); }
    if (id < 0 || $id > 5) { die("invalid FoxId"); }
    # Uncomment DB and port for test environments, if needed
    #$DB = $_GET['db'];
    #$PORT = $_GET['port'];

    if ($id == "") { $id = "1"; }
    if ($DB == "") { $DB="FOXDB"; }
    $where="where STP_HEADER.id=$id";
    $archive_where="and STP_HEADER.id=STP_HEADER_ARCHIVE_COUNT.id and STP_HEADER.receiver=STP_HEADER_ARCHIVE_COUNT.receiver";
    $name=getName($id);
    if ($id == '0') {
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
   echo "<tr><td><strong>Num</strong></td>".
        "<td><strong>Ground station</strong></td>".
        "<td align='center'><strong>DUV Frames</strong></td>".
        "<td align='center'><strong>9k6 Frames</strong></td>".
        "<td align='center'><strong>Last 7 days</strong></td>";
   # ROW SPAN needs to be at least 5x the number of spacecraft to display
      echo "<td rowspan=50 valign=top>";
   if ($id=='0') {
      latest(1, "");
      latest(2, "");
      latest(4, "fox1d/images");
      latest(5, $PORT);
   } else {
      latest($id, $PORT);
      echo "<a href=leaderboard.php?id=0&db=FOXDB>Show all spacecraft on leaderboard</a>";
   }
   echo "</td>";
   echo	"</tr>";

   if ($id==0) {
       $sql = "call StpLeaderboardTotals()";
   } else {
       $sql = "call StpLeaderboardTotalsById($id)";
   }

   mysql_select_db($DB);
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
   
   $j=1;
   while($row = mysql_fetch_array($retval, MYSQL_ASSOC) )
   {
        if ($j < $ROW_LIMIT) {
            echo "<tr><td align='center'>$j</td> ".
            "<td><a href=ground_station.php?id=$id&db=$DB&station={$row['receiver']}>{$row['receiver']}</a></td>  ".
         "<td align='center'>".number_format($row['DUV'])."</td>".
         "<td align='center'>".number_format($row['HighSpeed'])."</td> ".
         "<td align='center'>".number_format($row['last'])."</td> </tr> ";
        }
         $j++;
   }
   echo "</table>";
   if ($show == "")
       echo "<a href=leaderboard.php?id=$id&db=$DB&show=all>Show whole leaderboard</a>";
   else
       echo "<a href=leaderboard.php?id=$id&db=$DB>Show short leaderboard</a>";
   echo "<br>";
   echo "<br>";
   echo "<table><tr><td>";
   echo "<h2>Latest Image from Fox-1D</h2>";
   $files = scandir('/var/www/html/tlm/fox1d/images');
   $newest_file = $files[2]; # 0 and 1 are . and .. and index.html
   if ($newest_file != "" && $newest_file != 'index.html') {
       echo '<figure><img style="border:10px solid black;" src="fox1d/images/'.$newest_file.'"alt="Image from spacecraft Fox-1D" /><figcaption>'.$newest_file.'</figcaption></figure>';
   }
   echo "</td><td>";
   echo "<h2>FoxTelem</h2>";
   echo "<b>Latest Software:</b><br>";
   echo "<a href=http://amsat.us/FoxTelem/Windows>Download for Windows</a>";
   echo "<br>";
   echo "<a href=http://amsat.us/FoxTelem/Linux>Download for Linux</a>";
   echo "<br>";
   echo "<a href=http://amsat.us/FoxTelem/Mac>Download for Mac</a>";
   echo "<br>";
   echo "<b>Help and Tutorials:</b><br>";
   echo "</td></tr></table>";
   mysql_close($conn);
?>
</body>
</html>
