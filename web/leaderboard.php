<?php
session_start()
?>
<html>
<head>
<?php
if($_POST["call"]) {
    $callsign=$_POST['call'];
    $_SESSION['user'] = $callsign;
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
if($_POST["clear"]) {
    $_SESSION['user'] = "";
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>
<title>Fox Server Leaderboard</title>
<?php include "head.php"; ?>
<?php include "getName.php"; ?>
</head>
<body>
<img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<style>
table, th, td {
    border: 0px; 
}
</style>

<?php

    function latest($i, $imageDir) {
        global $DB, $conn, $PORT;
        $name=getName($i);
        $self=$_SERVER['PHP_SELF'];
        echo "<a href=$self?id=$i&db=$DB><strong class=entry-title>$name</strong></a> | <a href=health.php?id=$i&port=$PORT>latest spacecraft health </a>";
        if ($imageDir != "")
            echo "| <a href=showImages.php?id=$i>Camera Images</a>";
        echo " <br>";

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
        echo "Frames: ".number_format($totalCount)." ";

        # Now get the frames in last 24 hours
        $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(HOUR,date_time,now()) < 24;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die('Could not get data: ' . mysql_error());
        }
 
        $row = mysql_fetch_array($retval, MYSQL_ASSOC);
        echo "- last 24 hours: ".number_format($row['count(*)'])." ";

        # Now the frames in last 90 mins
        $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(MINUTE,date_time,now()) < 90;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die("Could not get 90 min data for $i : " . mysql_error());
        }
        $row = mysql_fetch_array($retval, MYSQL_ASSOC);
        echo " - last 90 mins: ".number_format($row['count(*)'])." <br>";

        # Now the list of ground stations in the last 90 mins
        echo "From ground stations: <br>";
        $sql = "select distinct receiver from ( select a.receiver, a.uptime from STP_HEADER a where a.id=$i and timestampdiff(MINUTE,a.date_time,now()) < 90 order by a.resets, a.uptime desc) tmp;";
        mysql_select_db($DB);
        $retval = mysql_query( $sql, $conn );
        if(! $retval ) {
            die('Could not get data: ' . mysql_error());
        }
        $stations = 0; 
        while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
            echo "<a href=ground_station.php?id=$i&db=$DB&station={$row['receiver']}>{$row['receiver']}</a> ";
            $stations = $stations + 1;
            if ($stations == 7) {
                $stations=0;
                #echo "<br>";
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
        $ROW_LIMIT=11;
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
   
    if(! $conn ) {
        die('Could not connect: ' . mysql_error());
    }
 
    # ROW SPAN needs to be at least 5x the number of spacecraft to display
    #echo "<td rowspan=50 valign=top>";
	
    echo "<div class='col-2 latest-stats' style='float:right;'>";
    if ($id=='0') {
        latest(1, "");
        latest(2, "");
        latest(3, "fox1c/images");
        latest(4, "fox1d/images");
    } else {
        if ($id == 3)
            latest(3, "fox1c/images");
        else if ($id == 4)
            latest(4, "fox1d/images");
        else
            latest($id, "");
    }
    #echo "</td>";
    #echo	"</tr>";
    echo "</div>";
	
    echo "<div class='col-1'>";
    echo "<table cellspacing='0' cellpadding='0' width=1024 border='0'>";
	
    echo "<tr><td><strong>Num</strong></td>".
        "<td><strong>Ground station</strong></td>".
        "<td align='center'><strong>DUV Frames</strong></td>".
        "<td align='center'><strong>9k6 Frames</strong></td>".
        "<td align='center'><strong>Total</strong></td>".
        "<td align='center'><strong>Last 7 days</strong></td>";
		
    
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
   
    if ($callsign == "" && !empty($_SESSION['user'])) {
        $callsign=$_SESSION['user'];
    }
    $j=1;
    while($row = mysql_fetch_array($retval, MYSQL_ASSOC) ) {
        if ($j < $ROW_LIMIT || strcasecmp($row['receiver'], $callsign) == 0) {
            echo "<tr><td align='center'>$j</td> ".
            "<td><a href=ground_station.php?id=$id&db=$DB&station={$row['receiver']}>{$row['receiver']}</a></td>  ".
            "<td align='center'>".number_format($row['DUV'])."</td>".
            "<td align='center'>".number_format($row['HighSpeed'])."</td> ".
            "<td align='center'>".number_format($row['total'])."</td> ".
            "<td align='center'>".number_format($row['last'])."</td> </tr> ";
        }
        $j++;
    }
    mysql_close($conn);
    echo "</table>";
    echo "</div>";
	
    echo "<div style='float:left;'>";	
    $self=$_SERVER['PHP_SELF'];
    echo "<table class='tlm_table'><tr><td class=tlm_td>";
    if ($show == "")
        echo "<a href=$self?id=$id&db=$DB&show=all>Show all ground stations</a>";
    else
        echo "<a href=$self?id=$id&db=$DB>Show short leaderboard</a>";
    echo "</td><td>";
    if ($id != 0)
        echo " <a href=$self?id=0&db=FOXDB>| Show all spacecraft</a>";
    echo "</td><td>";
    $params="id=$id&db=$DB";
    $form = '<form action="'.$self.'?'.$params.'" method="post">
    | Include Ground station: 
    <input type="text" name="call"/>
    <input type="submit" value="Clear" name="clear"/>
    </form></td>';
    echo $form;
    echo "</td><td width=100>";
    echo "</td></tr></table>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";
    #<input type="text" value='.$callsign.' name="call"/>
    #<input type="submit" value="Show" name="add"/>

?>

<div class='col-2' style='float:right;'>
<h2>FoxTelem</h2>
<p>
<a href=http://www.g0kla.com/foxtelem/index.php>FoxTelem</a> is the program you use to decode the data transmissions from the AMSAT Fox-1 series of spacecraft.
It will decode, store and allow analysis of telemetry and onboard experiments.
</p>
<p>
   <b>Latest Software:</b><br>
   <a href=http://amsat.us/FoxTelem/Windows>Download for Windows</a>
   <br>
   <a href=http://amsat.us/FoxTelem/Linux>Download for Linux</a>
   <br>
   <a href=http://amsat.us/FoxTelem/Mac>Download for Mac</a>
   <br>
</p>
   <b>Help and Tutorials:</b><br>
<p>
FoxTelem comes with a manual which you can find from the Help menu.  It covers the basics, but Software Defined Radio, Digital Signal processing and telemetry are non trivial topics.  It's relatively easy to decode the first few frames, but you can spend a lifetime perfecting your ground station.  These articles are aimed at taking you a step beyond the basics.
</p>
<a href=http://www.g0kla.com/workbench/2018-01-26.php>Earth Plots Tutorial - What they are and how to plot them</a>
<br>
<a href=http://www.g0kla.com/foxtelem/skyplot.php>Analyze your QTH with SKY PLOTs to see how well you are receving</a>
<br>
<a href=http://www.g0kla.com/workbench/2016-05-07.php>Use FoxTelem to analyze the received telemetry with graphs</a>
<br>
<a href=http://www.g0kla.com/sdr/index.php>How to write a Software Defined Radio - SDR and DSP Tutorial</a>
<br>
</div>
<?php
    $idLink = $id;
    if ($id==0 || $id == 3 || $id == 4) {
    echo "<div class='col-1'>";
    if ($id == 0) {
        echo "<h2>Notable Image from Fox-1D</h2>";
        $imageDir = "fox1d/images";
        $idLink = 4;
    } else if ($id == 4) {
        echo "<h2>Notable Image from Fox-1D</h2>";
        $imageDir = "fox1d/images";
    } else {
        echo "<h2>Notable Image</h2>";
        $imageDir = "fox1c/images";
    }
    #$files = scandir('/srv/www/www.amsat.org/public_html/tlm/fox1d/images', SCANDIR_SORT_DESCENDING);
    $files = glob("/srv/www/www.amsat.org/public_html/tlm/".$imageDir."/*.jpg.comments.html");
    usort($files, function($a, $b){
        return filemtime($a) < filemtime($b);
    });

    $found = FALSE;
    foreach ($files as $filePath) {
        $file = basename($filePath);
        $file = str_replace(".comments.html", "", $file);
   	#echo "<br>file: ".$file;
        if (!$found && $file != "" && substr( $file, 0, 5 ) != 'index') {
   	        #echo " found!: ".$file;
            $found = TRUE;
   	        $newest_file = $file;
        }
    }
    #echo "<br>newest: ".$newest_file;
    if ($newest_file != "" && $newest_file != 'index.html') {
        echo '<a href=showImages.php?id='.$idLink.'&start='.$newest_file.'><figure><img style="border:10px solid black;" src="'.$imageDir.'/'.$newest_file.'" alt="Image from spacecraft '.getName($idLink).'" /><figcaption>'.$newest_file.'</figcaption></figure></a>';
    }
}
?>
</div>
</body>
</html>
