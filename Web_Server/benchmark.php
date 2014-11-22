<?php
include 'templates/sessioncheck.php';
?>
<html>
    <head>
        <title>Database BenchMarking</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
    </head>
</html>
<body>
    <?php
        require 'templates/head.php';
        require 'templates/sidebar.php';
	$flag=true;
        ?>
    <div class="content">
        <h2>Bench Mark</h2>
        <?php
            $num_records = 10;
            for($i = 0; $i < $num_records; $i++)
            {
                $rec[$i][0] = $i+1;
                $rec[$i][1] = "name".($i+1);
                $var = mt_rand()%2;
                $rec[$i][2] = $var?"M":"F" ;
                $rec[$i][3] = mt_rand(100, 1000000);
                $time = mt_rand( strtotime("Jan 01 1980"), strtotime("Dec 31 2000") );
                $rec[$i][4] = date("Y-m-d", $time);
                $rec[$i][5] = mt_rand(1,10);

            }
            shuffle($rec);

            for($i = 0; $i < $num_records; $i++)
            {
                $msg[$i] = "5\nEmployee 6";
                $in = "\n1 0 ".$rec[$i][0];
                $in .= "\n2 1 ".$rec[$i][1];
                $in .= "\n2 2 ".$rec[$i][2];
                $in .= "\n1 3 ".$rec[$i][3];
                $in .= "\n3 4 ".$rec[$i][4];
                $in .= "\n1 5 ".$rec[$i][5];
                $msg[$i] .= $in;
            }

            $old = microtime(true);

            for($i = 0; $i < $num_records; $i++)
            {   
                include 'templates/connect.php';
                if( ! socket_send ( $sock , $msg[$i] , strlen($msg[$i]) , 0))
                {
                     $errorcode = socket_last_error();
                     $errormsg = socket_strerror($errorcode);
                     die("Could not send data: [$errorcode] $errormsg \n");
                }
                if (!($bytes = socket_recv($sock, $ms, 1 , MSG_WAITALL))) 
                {  
                    die("Could not receive data " . socket_strerror(socket_last_error($sock)));
                }

                socket_close($sock);
            }
            echo '<h5>'.substr(microtime(true) - $old, 0, 6).'</h5>';
        ?>
    </div>
</body>
</html>