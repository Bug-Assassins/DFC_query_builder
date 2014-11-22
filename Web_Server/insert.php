<?php
    include 'templates/sessioncheck.php';
?>
<html>
    <head>
        <title>Insert</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
    </head>
    <body>
        <?php
        include 'templates/head.php';
        include 'templates/sidebar.php';
        ?>
        <div class="content">
            <h2>Insert Query</h2>
            <?php
            if (!isset($_POST['level'])) // Level 1
            {
                include 'templates/connect.php';
                include 'templates/show.php';
                socket_close($sock);
            }
            else
            {
                $msg = $_POST['message'];
            }
                 $token = strtok($msg, ";");
                 $i = (int)trim($token);
            ?>

            <form action="" method="POST">
                <label for="table_name">Tables</label>
                <select name="table_name">
                    <?php
                        while($i > 0)
                        {    
                            $token = strtok(";");   
                            echo  '<option value="'.$token.'" '; 
                            if(isset($_POST["table_name"]) && $_POST["table_name"]=== $token)
                            {
                               echo 'selected="selected" ';
                            }
                            echo ">$token</option>";
                            $i--;
                        }             
                    ?>
                </select>
                <input type="hidden" value="2" name="level"/>
                <input type="hidden" value="<?php echo $msg?>" name="message"/>
                <input type="submit" value="submit"/>
            </form>
        <?php
        if (isset($_POST['level']) )
        {
            if( $_POST['level']== 2) // Level 2
            {
                // Form to fill in the values to be inserted.
                include 'templates/connect.php';
                include 'templates/describe.php';
                socket_close($sock);
            }
            else
            {
                $msg = $_POST['msg'];
            }
            $token = strtok($msg, "\n");
            $cols = (int)$token;
            $colname = array();
            $coltype = array();
        ?>
            <form action="" method="POST">
                <table>
                    <tr>
                        <th>Column Name</th>
                        <th>Data Type</th>
                        <th>Value</th>
                    </tr>
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
                                <td>
                                    <?php 
                                        if($coltype[$j] === 1) 
                                        {
                                            echo '<input type="number" name="'.$j;
                                            if(isset($_POST["$j"]))
                                            {
                                                echo '" value="'.$_POST["$j"];
                                            }
                                            echo '"/>';
                                        }
                                        else if(($coltype[$j] === 2))
                                        {
                                            echo  '<input type="text" name="'.$j.'" maxlength='.$colsize;
                                            if(isset($_POST["$j"]))
                                            {
                                                echo '" value="'.$_POST["$j"].'"';
                                            }
                                            echo '/>';
                                        }
                                        else
                                        {
                                            echo  '<input type="date" name="'.$j;
                                            if(isset($_POST["$j"]))
                                            {
                                                echo '" value="'.$_POST["$j"];
                                            }
                                            echo '"/>';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php   
                        }
                        ?>
                </table>
                <input type="hidden" value="<?php echo $cols;?>" name="cols"/>
                <input type="hidden" value="3" name="level"/>
                <input type="hidden" name="table_name" value="<?php echo $_POST['table_name'];?>"/>
                <input type="hidden" value="<?php echo $msg?>" name="msg"/>
                <input type="hidden" value="<?php echo $_POST['message'];?>" name="message"/>
                <input type="submit" value="submit"/>
            </form>
        <?php    
        }

        if(isset($_POST['level']) && $_POST['level'] == 3)
        {
            if(!isset($_POST["0"]) || trim($_POST["0"])=="")
            {
                echo "Primary Key Should be set";
            }
            else
            {
                $count = 1;
                $msg = "1 0 ".$_POST["0"]."\n";
                $query = "INSERT INTO ".$_POST['table_name']." ( ".$colname[0];
                $values = $_POST["0"];
                for($i = 1; $i < $_POST['cols']; $i++)
                {
                    if(!isset($_POST["$i"]) || trim($_POST["$i"]) === "")
                    {
                        continue;
                    }
                    else
                    {
                        $query .= ", $colname[$i]";
                        $msg .= "$coltype[$i] $i ".$_POST["$i"]."\n";
                        if($coltype[$i] === 1)
                        {
                            $values .= ", ".$_POST["$i"];
                        }
                        else
                        {
                            $values .= ', "'.$_POST["$i"].'"';
                        }
                        $count ++;
                    }
                }
                $query .= " ) VALUES ( $values )";
                $msg = "5\n".$_POST['table_name']." $count\n$msg";
                //echo $msg;
                include 'templates/connect.php';
                if( ! socket_send ( $sock , $msg , strlen($msg) , 0))
                {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);
                    socket_close($sock);
                    die("Could not send data: [$errorcode] $errormsg \n");
                }
                echo '<textarea rows="8" cols="80">'.$query.'</textarea>';
            }
        }
        ?>
        </div>  
    </body>
</html>