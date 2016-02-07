<html>
<head>
<title>Fox Server Leaderboard</title>
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<h1 class="entry-title">FOX 1A Telemetry Leaderboard</h1>
<?php
    $dbhost = 'localhost:3036';
    $dbuser = 'foxrpt';
    $dbpass = 'amsatfox';

    $id = 1;

    $conn = mysql_connect($dbhost, $dbuser, $dbpass);
   
   if(! $conn )
   {
      die('Could not connect: ' . mysql_error());
   }
   
   echo "<table cellspacing='0' cellpadding='0' width=1024 border='0'>";
   echo "<tr><td><strong>Ground station</strong></td>".
        "<td align='center'><strong>DUV Frames</strong></td>".
        "<td align='center'><strong>HighSpeed Frames</strong></td>";

  $sql = "select count(date_time) from STP_HEADER where id=$id and timestampdiff(MINUTE,date_time,now()) < 90;";
   mysql_select_db('FOXDB');
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
 
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   echo "<td rowspan=20><strong>Frames Received last 90 mins : </strong>{$row['count(date_time)']} </br>";
   echo "From ground stations: <br>";
  $sql = "select distinct receiver from STP_HEADER where id=$id and timestampdiff(MINUTE,date_time,now()) < 90 order by resets desc, uptime desc;";
   mysql_select_db('FOXDB');
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
   $stations = 0; 
 while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
   {
      echo " {$row['receiver']} ";
      $stations = $stations + 1;
      if ($stations == 5) {
         $stations=0;
         echo "<br>";
      }
   }
      echo "<br> ";
      echo "<br> ";

  $sql = "select count(date_time) from STP_HEADER where id=$id and timestampdiff(HOUR,date_time,now()) < 24;";
   mysql_select_db('FOXDB');
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
 
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);
   echo "<strong>Frames Received last 24 hours:</strong> {$row['count(date_time)']} </br>";

   $sql = "select count(receiver) from STP_HEADER where id=$id";
   mysql_select_db('FOXDB');
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
   
   $row = mysql_fetch_array($retval, MYSQL_ASSOC);

   echo	"<strong>Total Frames Since Launch: </strong>{$row['count(receiver)']} <br>".
	"<br>";
   echo	"<strong>Fox-1A:</strong> <a href=http://www.amsat.org/tlm/ao85.php>latest spacecraft health</a> <br>".
	"</td>".
	"</tr>";
   $sql = "select receiver, sum(case when source = 'amsat.fox-1a.ihu.duv' then 1 else 0 end) DUV, sum(case when source = 'amsat.fox-1a.ihu.highspeed' then 1 else 0 end) HighSpeed from STP_HEADER where id=$id group by receiver order by DUV DESC";
   mysql_select_db('FOXDB');
   $retval = mysql_query( $sql, $conn );
   if(! $retval ) {
      die('Could not get data: ' . mysql_error());
   }
   
   while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
   {
      echo "<tr><td>{$row['receiver']}</td>  ".
         "<td align='center'>{$row['DUV']}</td>".
         "<td align='center'>{$row['HighSpeed']}</td> </tr> ";
   }
   echo "</table>";
   
   mysql_close($conn);
?>
</body>
</html>
