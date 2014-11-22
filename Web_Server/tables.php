<?php
include 'templates/sessioncheck.php';
?>
<html>
    <head>
        <title>Show Table Query</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
    </head>
    <body>
        <?php 
            require 'templates/head.php';
            require 'templates/sidebar.php';
        ?>
        <div class="content">
            <h2>Show Table Query</h2>
            <table class="qr_tab">
                <tr>
                    <td>Table :</td>
                    <td>
                        <?php
                            include 'templates/connect.php';
                            include 'templates/show.php';
                            socket_close($sock);
                            $token = strtok($msg, ";");
                            $i = (int)trim($token);
                            while($i > 0)
                            {    
                                $token = strtok(";");   
                                echo  $token.'<br/>';
                                $i--;
                            }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>