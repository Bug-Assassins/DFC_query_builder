<?php
include 'templates/sessioncheck.php';
if(isset($_POST['sel_table']))
{
    $msg = "9\n".$_POST['sel_table'];
    include 'templates/connect.php';
    if( ! socket_send ( $sock , $msg , strlen($msg) , 0))
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        die("Could not send data: [$errorcode] $errormsg \n");
    }
    if (!($bytes = socket_recv($sock, $msg, 4096 , MSG_WAITALL))) 
    {  
        die("Could not receive data " . socket_strerror(socket_last_error($sock)));
    }
    socket_close($sock);
    $query = 'DESCRIBE TABLE '.$_POST['sel_table'];
    $res = 1;
    $token = strtok($msg, "\n");
    $cols = (int)$token;
    $colname = array();
    $coltype = array();
}
?>
<html>
    <head>
        <title>Describe Query</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
    </head>
    <body>
        <?php 
            require 'templates/head.php';
            require 'templates/sidebar.php';
        ?>
        <div class="content">
            <h2>Describe Query</h2>
            <form action="describe_table.php" method="POST">
                <table class="qr_tab">
                    <?php
                        if(!isset($_POST['sel_table']))
                        {
                            ?>
                            <tr>
                                <td><label for="sel_table">Table :</label></td>
                                <td>
                                    <select name="sel_table" id="sel_table">
                                        <?php
                                            include 'templates/connect.php';
                                            include 'templates/show.php';
                                            socket_close($sock);
                                            $token = strtok($msg, ";");
                                            $i = (int)trim($token);
                                            while($i > 0)
                                            {    
                                                $token = strtok(";");   
                                                echo  '<option value="'.$token.'" >'.$token.'</option>';
                                                $i--;
                                            }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><input type="submit" value="Submit Table"/></td>
                            </tr>
                            <?php
                        }
                    ?>
                </table>
            </form>
            <?php
                if(isset($res) && $res)
                {
                    ?>
                        <h4 style="color: green;">Your Query was executed Successfully !!</h4>
                        <h5 style="color: gray; font-size: 20px;"><?php echo $query; ?></h5>
                        <table class="res_table">
                            <thead>
                                <tr>
                                    <td>Column Name</td>
                                    <td>Data Type</td>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                for( $j = 0; $j < $cols; $j++ )
                                {
                                    $token = strtok("\n");
                                    sscanf($token, "%s %d %d", $colname[$j], $coltype[$j], $colsize );
                                    ?>
                                        <tr>
                                            <td><?php echo $colname[$j];?></td>
                                            <td><?php
                                                if($coltype[$j] === 1 )
                                                    echo "INTEGER";
                                                else if($coltype[$j] === 2 )
                                                    echo "VARCHAR";
                                                else if($coltype[$j] === 3 )
                                                    echo "DATE";?>
                                            </td>
                                        </tr>
                                    <?php
                                }
                            ?>
                            </tbody>
                        </table>
                    <?php
                }
                else if(isset($res))
                {
                    ?>
                        <h4 style="color: red;">Your Query Encountered Error !!</h4>
                        <h5 style="color: gray; font-size: 20px;"><?php echo $query; ?></h5>
                    <?php
                }
            ?>
        </div>
    </body>
</html>