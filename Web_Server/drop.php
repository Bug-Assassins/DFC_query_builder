<?php
include 'templates/sessioncheck.php';
if(isset($_POST['sel_table']))
{
    $msg = "6\n".$_POST['sel_table'];
    include 'templates/connect.php';
    if( ! socket_send ( $sock , $msg , strlen($msg) , 0))
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        die("Could not send data: [$errorcode] $errormsg \n");
    }
    $query = 'DROP TABLE '.$_POST['sel_table'];
    $res = 1;
}
?>
<html>
    <head>
        <title>Drop Query</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
    </head>
    <body>
        <?php 
            require 'templates/head.php';
            require 'templates/sidebar.php';
        ?>
        <div class="content">
            <h2>Drop Query</h2>
            <form action="drop.php" method="POST">
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