<html>
<head>
  
    <title>Fox Server Field Data</title>
    <link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-a
msat-child/style.css" />

    <!-- Request data to server -->

    <?php
   include "getName.php";
   $arg1 = $_GET['sat'];
   $arg2 = $_GET['field'];
   $raw = $_GET['raw'];
   $reset = $_GET['reset'];
   $uptime = $_GET['uptime'];
   $num = $_GET['rows'];
   $port = $_GET['port'];
   $raw='conv';
   if ($port == "") $port = 8080;
   #echo ("http://localhost:8080/field/$arg1/$arg2/$raw/$num/$reset/$uptime");
   $a = file_get_contents("http://localhost:$port/field/$arg1/$arg2/$raw/$num/$reset/$uptime");

        if ($a) {
            // split html table to build array
            $rows = preg_split('#<tr>#i', $a);
            foreach ( $rows as $key => $row ) {
                $rows[$key] = preg_split('#</td><td>#i', $row);
                foreach ($rows[$key] as $key2 => $row2) {
                // remove leftover html tags
                    $rows[$key][$key2]=strip_tags($row2);
                }
            }
        }
        // delete first two rows (non-numeric)
        array_shift($rows);
        array_shift($rows);
        $num=count($rows);
    ?>

    <!-- Stuff needed to display plot through Google charts -->

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages: ['corechart', 'line']});
        google.charts.setOnLoadCallback(drawBasic);

        function drawBasic() {
            var data = new google.visualization.DataTable();
            data.addColumn('number', 'Uptime');
            data.addColumn('number', 'Value');
          
            // build array for plot. Get uptime and parameter value from php array
            <?php foreach ( $rows as $key => $row ) { ?>
                data.addRow([
                    <?php echo $rows[$key][1];?>,
                    <?php echo $rows[$key][2];?>
                ]);
            <?php }?>           
 
            var options = {
                hAxis: {
                    title: 'Uptime'
                },
                vAxis: {
                    title: '<?php echo $arg2;?>'
                },
                legend: {
                    position: 'none'
                },
                titleTextStyle: {
                    fontSize: 18
                },
                    title: 
                        '<?php 
                            echo 'Fox ', getName($arg1), ' ', $arg2, ' ';
                            if ($raw == 'conv')
                                echo 'Converted';
                            else echo 'Raw';
                            $lastRow = count($rows[0]);
                            echo ' Values, Reset = ', $rows[$num-1][0];
                        ?>'
              
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }
    </script>
      
    <body>
        <img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'>
        <div id="chart_div" style="width: 1000px; height: 500px"></div>
    </body>
  
</head>
</html>


