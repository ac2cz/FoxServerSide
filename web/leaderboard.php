<?php
session_start()
?>
<html>
<head>
<?php
if(isset($_POST["call"])) {
    #$callsign=$_POST['call'];
    $_SESSION['user'] = $_POST['call'];
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
if(isset($_POST["clear"])) {
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
<img src='https://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
<style>
table, th, td {
    border: 0px; 
}
</style>

<?php

    # This function is run for each sat we want to display totals for
    # The results sit in the right hand column
    function latest($i, $imageDir) {
        global $DB, $conn, $PORT, $show, $PERIOD;
        $name=getName($i);
        $self=$_SERVER['PHP_SELF'];
        if ($show == "") {
            if ($PERIOD <= 30) {
                echo "<a href=$self?id=$i&db=$DB><strong class=entry-title>$name</strong></a> ";
            } else {
                echo "<a href=$self?id=$i&db=$DB&period=100><strong class=entry-title>$name</strong></a> ";
            }
        } else {
            if ($PERIOD <= 30) {
                echo "<a href=$self?id=$i&db=$DB&show=all><strong class=entry-title>$name</strong></a> ";
            } else {
                echo "<a href=$self?id=$i&db=$DB&show=all&period=100><strong class=entry-title>$name</strong></a> ";
            }
        }
        echo " | <a href=health.php?id=$i&port=$PORT>latest spacecraft health </a>";
		if ($i >= 5)
            echo "| <a href=wod.php?id=$i&port=$PORT>whole orbit data </a>";
        if ($imageDir != "")
            echo "| <a href=showImages.php?id=$i>Camera Images</a>";
        echo " <br>";
        if ($i == 6) {
            #echo "<b style='color:red;' class=entry-title>Only real time data being processed.  No recorded data will be stored</b><br> ";
        }

        # Now calculate the total for this sat and display it
        $sql = "select (select count(*) from STP_HEADER where id=$i) as sumCountHeader;";
        if ($result = mysqli_query($conn, $sql )) {
            $row1 = mysqli_fetch_assoc($result);
        } else {
            die('Could not get data: ');
            #die('Could not get data: ' . mysqli_error($conn));
        }
        $headerCount=$row1['sumCountHeader'];
        mysqli_free_result($result);

        $sql = "select (select total from STP_ARCHIVE_TOTALS where id=$i) as sumCountArchive;";
        if ($result = mysqli_query($conn, $sql )) {
            $row2 = mysqli_fetch_assoc($result);
        } else {
            die('Could not get data: ');
            #die('Could not get data: ' . mysqli_error($conn));
        }
        $archiveCount=$row2['sumCountArchive'];
	$totalCount=$headerCount+$archiveCount;

        echo "Frames: ".number_format($totalCount)." ";
        mysqli_free_result($result);

        # Now get the frames in last 24 hours
        $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(HOUR,date_time,now()) < 24;";
        if ($result = mysqli_query($conn, $sql )) {
            $row = mysqli_fetch_assoc($result);
        } else {
            die('Could not get data: ');
            #die('Could not get data: ' . mysqli_error($conn));
        }
        echo "- last 24 hours: ".number_format($row['count(*)'])." ";
        mysqli_free_result($result);

        # Now the frames in last 90 mins
        $sql = "select count(*) from STP_HEADER where id=$i and timestampdiff(MINUTE,date_time,now()) < 90;";
        if ($result = mysqli_query($conn, $sql )) {
            $row = mysqli_fetch_assoc($result);
        } else {
            die('Could not get data: ');
            #die('Could not get data: ' . mysqli_error($conn));
        }
        echo " - last 90 mins: ".number_format($row['count(*)'])." <br>";
        mysqli_free_result($result);

        # Now the list of ground stations in the last 90 mins
        echo "From ground stations: <br>";
        $sql = "select distinct receiver from ( select a.receiver, a.uptime from STP_HEADER a where a.id=$i and timestampdiff(MINUTE,a.date_time,now()) < 90 order by a.resets, a.uptime desc) tmp;";
        if ($result = mysqli_query($conn, $sql )) {
            while($row = mysqli_fetch_assoc($result)) {
                echo "<a href=ground_station.php?id=$i&db=$DB&station={$row['receiver']}>{$row['receiver']}</a> ";
            }
        } else {
            die('Could not get data: ');
            #die('Could not get data: ' . mysqli_error($conn));
        }

        mysqli_free_result($result);
        echo "<br> ";
        echo "<br> ";
    }

    #
    # MAIN - Execution stats here
    #
    $dbhost = 'localhost';
    $dbuser = 'g0kla';
    $dbpass = 'amsatfox';

	$id = 0;
    $id = $_GET['id'];
	$show = "";
	if (isset($_GET['show']))
		$show = $_GET['show'];
    if ($show == "")
        $ROW_LIMIT=11;
    else
        $ROW_LIMIT=999999;
	$period = 30;
	if (isset($_GET['period']))
		$period = $_GET['period'];
    if ($period == "")
        $PERIOD=30;
    else {
        if (!is_numeric($period)) { die("invalid paramater"); }
        $PERIOD=$period;
    }
    if (!is_numeric($id)) { die("invalid paramater"); }
    if ($id < 0 || $id > 6) { die("invalid FoxId"); }
    # Uncomment DB and port for test environments, if needed
    #$DB = $_GET['db'];
    #$PORT = $_GET['port'];

    if ($id == "") { $id = "1"; }
    #if ($DB == "") { $DB="FOXDB"; }
    $DB = "FOXDB";
	$where="where STP_HEADER.id=$id";
    $archive_where="and STP_HEADER.id=STP_HEADER_ARCHIVE_COUNT.id and STP_HEADER.receiver=STP_HEADER_ARCHIVE_COUNT.receiver";
    $name=getName($id);
    if ($id == '0') {
        $name = "FOX";
        $where="";
    	$archive_where="on STP_HEADER.receiver=STP_HEADER_ARCHIVE_COUNT.receiver";
    }
    if ($PERIOD <= 30)
        echo "<h1 class=entry-title>$name Telemetry - Monthly Leaders</h1> ";
    else
        echo "<h1 class=entry-title>$name Telemetry - All-Time Leaders</h1> ";

    #echo "<h2 style='color:red;' class=entry-title>FOX TELEMETRY SERVER is current down for maintenance</h2></p> ";
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $DB);
    if(mysqli_connect_errno($conn)) {
       # Not to be inthe production code
       #echo "Error: Failed to make a MySQL connection, here is why: <br>";
       #echo "Errno: " . mysqli_connect_errno($conn) . "<br>";
       #echo "Error: " . mysqli_connect_error($conn) . "<br>";
        die("No Connection<br>");
    }
 
    # This <div> holds the individual spacecrat results to the right
    echo "<div class='col-2 latest-stats' style='float:right;'>";
    if ($id=='0') {
        latest(3, "fox1c/images");
        latest(2, "");
        latest(4, "fox1d/images");
    } else {
        if ($id == 3)
            latest(3, "fox1c/images");
        else if ($id == 4)
            latest(4, "fox1d/images");
        else
           latest($id, "");
    }
    echo "</div>";
	
    # This is the <div> for the table of leaderboard results
    # 'col-1' class handles the width requirements for different screen sizes
    echo "<div class='col-1'>";
    echo "<table cellspacing='0' cellpadding='0' width=1024 border='0'>";
	
    echo "<tr><td><strong>Num</strong></td>".
        "<td><strong>Ground station</strong></td>".
        "<td align='center'><strong>FSK Frames</strong></td>".
        "<td align='center'><strong>PSK Frames</strong></td>";
        if ($PERIOD <= 30) {
            echo "<td align='center'><strong>Last 30 days</strong></td>";
        }
        echo "<td align='center'><strong>Last 7 days</strong></td>";
		
    
    if ($id==0) { # This is for all spacecraft
        if ($PERIOD <= 30) {
            $sql = "select id, receiver, sum(case when source like '%duv' or source like '%highspeed' then 1 else 0 end) DUV, sum(case when source like '%bpsk' then 1 else 0 end) PSK, sum(case when source like '%duv' or source like '%highspeed' or source like '%bpsk' then 1 else 0 end) total, sum(case when timestampdiff(DAY,date_time,now()) < 7 then 1 else 0 end) last from STP_HEADER group by receiver order by total DESC";
        } else {
            $sql = "call StpLeaderboardMthTotals()";
        }
    } else { # Just one spacecraft
        if ($PERIOD <= 30) {
            $sql = "select id, receiver, sum(case when source like '%duv' or source like '%highspeed' then 1 else 0 end) DUV, sum(case when source like '%bpsk' then 1 else 0 end) PSK, sum(case when source like '%duv' or source like '%highspeed' or source like '%bpsk' then 1 else 0 end) total, sum(case when timestampdiff(DAY,date_time,now()) < 7 then 1 else 0 end) last from STP_HEADER where id=$id group by receiver order by total DESC";
        } else {
            $sql = "call StpLeaderboardMthTotalsById($id)";
        }
    }
    if ($result = mysqli_query($conn, $sql )) {
	$callsign = "";
        if (!empty($_SESSION['user'])) {
            $callsign=$_SESSION['user'];
        }
        $j=1;
        while($row = mysqli_fetch_assoc($result) ) {
            if ($j < $ROW_LIMIT || ($callsign != "" && strcasecmp($row['receiver'], $callsign) == 0)) {
            #if ($j < $ROW_LIMIT ) {
                echo "<tr><td align='center'>$j</td> ".
                "<td><a href=ground_station.php?id=$id&db=$DB&station={$row['receiver']}>{$row['receiver']}</a></td>  ".
                "<td align='center'>".number_format($row['DUV']+$row['HighSpeed'])."</td>".
                "<td align='center'>".number_format($row['PSK'])."</td> ";
                if ($PERIOD <= 30) {
                   echo  "<td align='center'>".number_format($row['total'])."</td> ";
                }
                echo "<td align='center'>".number_format($row['last'])."</td> </tr> ";
            }
            $j++;
        }
        mysqli_free_result($result);
    } else {
        #die('Could not get data: ');
        die('Could not get data: ' . mysqli_error($conn));
    }
    echo "</table>";
    echo "</div>";
	
    echo "<div style='float:left;'>";	
    $self=$_SERVER['PHP_SELF'];
    echo "<table class='tlm_table'><tr><td class=tlm_td>";
    if ($show == "") {
        if ($PERIOD <= 30) {
            echo "<a href=$self?id=$id&db=$DB&show=all>Show all ground stations</a>";
            echo "<br><a href=$self?id=$id&db=$DB&period=100>Show all-time leaderboard</a>";
            if ($id != 0) 
                $showAllSpacecraft = " <a href=$self?id=0&db=FOXDB>| Show all spacecraft</a>";
        } else {
            echo "<a href=$self?id=$id&db=$DB&show=all&period=100>Show all ground stations</a>";
            echo "<br><a href=$self?id=$id&db=$DB>Show monthly leaderboard</a>";
            if ($id != 0) 
                $showAllSpacecraft = " <a href=$self?id=0&db=FOXDB&period=100>| Show all spacecraft</a>";
        }
    } else {
        if ($PERIOD <= 30) {
            echo "<a href=$self?id=$id&db=$DB>Show short leaderboard</a>";
            echo "<br><a href=$self?id=$id&db=$DB&show=all&period=100>Show all-time leaderboard</a>";
            if ($id != 0) 
                $showAllSpacecraft = " <a href=$self?id=0&db=FOXDB&show=all>| Show all spacecraft</a>";
        } else {
            echo "<a href=$self?id=$id&db=$DB&period=100>Show short leaderboard</a>";
            echo "<br><a href=$self?id=$id&db=$DB&show=all>Show monthly leaderboard</a>";
            if ($id != 0) 
                $showAllSpacecraft = " <a href=$self?id=0&db=FOXDB&show=all&period=100>| Show all spacecraft</a>";
        }
    }
    echo "</td><td>";
    echo $showAllSpacecraft;
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
<a href=https://www.g0kla.com/foxtelem/index.php>FoxTelem</a> is the program you use to decode the data transmissions from the AMSAT Fox-1 series of spacecraft.
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
<a href=https://www.g0kla.com/workbench/2019-03-09.php>Costas Loop or Dot Product Decoder for BPSK</a>
<br>
<a href=https://www.g0kla.com/workbench/2018-01-26.php>Earth Plots Tutorial - What they are and how to plot them</a>
<br>
<a href=https://www.g0kla.com/foxtelem/skyplot.php>Analyze your QTH with SKY PLOTs to see how well you are receving</a>
<br>
<a href=https://www.g0kla.com/workbench/2016-05-07.php>Use FoxTelem to analyze the received telemetry with graphs</a>
<br>
<a href=https://www.g0kla.com/sdr/index.php>How to write a Software Defined Radio - SDR and DSP Tutorial</a>
<br>
</div>
<?php
    $idLink = $id;
    if ($id==0 || $id == 4) {
    echo "<div class='col-1'>";
    if ($id == 0) {
        echo "<h2>Silent spacecraft memorial</h2>";
        echo "<h3>Notable Image from AO-92</h3>";
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
    $files = glob("/home/tlmmgr/tlm/".$imageDir."/*.jpg.comments.html");
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
    if ($id=='0') {
       # latest(4, "fox1d/images");
        latest(6, "");
        latest(1, "");
        latest(5, "");
    } 
    mysqli_close($conn);
}
?>
</div>
</body>
</html>
