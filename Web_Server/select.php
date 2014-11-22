<?php
    include 'templates/sessioncheck.php';
?>
<html>
    <head>
        <title>Select Query</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
        <script type="text/javascript">
            function check(id,x)
            {
                var c = "wh_cl_right"+id;
                var d = "wh_cl_right_sel"+id;
                console.log(c);
                if(x===0)
                {
                    document.getElementsByName(c)[0].disabled = false;
                    document.getElementsByName(d)[0].disabled = true;
                }
                else
                {
                    document.getElementsByName(c)[0].disabled = true;
                    document.getElementsByName(d)[0].disabled = false;
                }
                
            }
        </script>
    </head>
    <body>
<?php
 
       require 'templates/head.php';
       require 'templates/sidebar.php'; 
        session_start();

if(isset($_POST['sel_table']) && count($_POST['sel_table']) == 0)
{
    unset($_POST['sel_table']);
    header('Location:select.php?err=1');
}
if(isset($_POST['sel_cols']) && count($_POST['sel_cols']) == 0)
{
    unset($_POST['sel_cols']);
    header('Location:select.php?err=1');
}
if(isset($_POST['last_state']))
{
    $msg = "2\n";
    $query = "SELECT ";
    $colno = 0;
    foreach($_POST['sel_cols'] as $col)
    {
        $colm = explode('?',$col);
        if($colno==0)
        {
            $query = $query.$colm[1];
            $columns = $colm[0];
        }
        else
        {
            $query = $query.", ".$colm[1];
            $columns .= "\n$colm[0]";
        }
        $colno++;
    }
    $columns = "$colno\n$columns";
    $query .= " FROM ";
    $i = 0;
    foreach ($_POST['sel_table'] as $tab)
    {
        if($i==0)
        {
            $query .= $tab;
            $table = $tab;
        }
        else
        {
            $query = $query.", ".$tab;
            $table .= "\n$tab";
        }
        $i++;
    }
    $msg .= "$i\n$table\n$columns\n";
    $msg .= $_POST['num_where'];
    if($_POST['num_where'] > 0)
    {
        $query .= " WHERE ";
        for($i = 1; $i <= $_POST['num_where']; $i++)
        {
            if(!isset($_POST['wh_cl_right'.$i]) && (!isset($_POST['wh_cl_right_sel'.$i])))
            {
                unset($_POST['sel_table']);
                header('Location:select.php?err=1');
            }
            else
            {
                $colm = explode('?',$_POST['wh_cl_left'.$i]);
                switch($_POST['wh_cl_op'.$i])
                {
                    case "="    : $op = 1;
                                break;
                    case ">="   : $op = 2;
                                break;
                    case "<="   : $op = 3;
                                break;
                    case "!="   : $op = 4;
                                break;
                    case ">"    : $op = 5;
                                break;
                    case "<"    : $op = 6;
                                break;
                }
                if($i == 1)
                {
                    $query .= $colm[1]." ".$_POST['wh_cl_op'.$i]." ";
                }
                else
                {
                    $query .= "AND ".$colm[1]." ".$_POST['wh_cl_op'.$i]." ";
                }
                $msg .= "\n$colm[0] $op "; 
                if(isset($_POST['wh_cl_right_sel'.$i]))
                {
                    $coln = explode('?',$_POST['wh_cl_right_sel'.$i]);
                    $msg .= $coln[0];
                    $query .= $coln[1]." ";
                }
                else
                {
                    $var = $_POST['wh_cl_right'.$i];
                    
                    if(is_numeric($var))
                    {
                        $msg .= "-1 $var";
                        $query .= $var." "; 
                    }
                    else 
                    {
                        $msg .= "-2 $var";
                        $query .= "'$var'";
                    }
                }          
             }
        }
    }
        include 'templates/connect.php';
        if( ! socket_send ( $sock , $msg , strlen($msg) , 0))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
     
            die("Could not send data: [$errorcode] $errormsg \n");
        }
        if($_POST['sort_col'] != "none")
        {
            $query .= " ORDER BY ".$_POST['sort_col']." ".$_POST['sort_or'];
        }
        if(trim($_POST['limit_rows']) != "")
        {
            $query .= " LIMIT 0, ".$_POST['limit_rows'];
        }
        $res =1;//mysqli_query($conn,$query);
        $exec = 1;
}
?>
        <div class="content">
            <h2>Select Query</h2>
            <?php
                if(isset($_REQUEST['err']))
                {
                    if($_REQUEST['err'] == 1)
                    {
                        echo "<center><h4 style='color: red;'>Incorrect Query</h4></center>";
                    }
                }
            ?>
            <form action="select.php" method="POST">
                <table class="qr_tab">
                    <?php
                        if(!isset($_POST['sel_table']))
                        {
                            ?>
                                <tr>
                                    <td><label for="sel_table">Table :</label></td>
                                    <td>
                                        <select name="sel_table[]" id="sel_table" multiple>
                                            <?php
                                               
                                                include 'templates/connect.php';
                                                include 'templates/show.php';
                                                $token = strtok($msg, ";");
                                                $i = (int)trim($token);
                                                while($i > 0)
                                                {          
                                                    $token = strtok(";");   
                                                    echo  '<option value="'.$token.'">'.$token.'</option>'; 
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
                        else if(!isset($_POST['sel_cols']))
                        {
                            ?>
                                <tr>
                                    <td><label for="sel_table">Table(s) :</label></td>
                                    <td>
                                        <?php
                                            foreach($_POST['sel_table'] as $table)
                                            {
                                                echo "<input name='sel_table[]' type='hidden' value='$table'>";
                                                echo $table;
                                                echo '<br/>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="sel_cols[]">Select Columns :</label></td>
                                    <td>
                                        <select name="sel_cols[]" id="sel_cols[]" multiple style="height: 200px;">
                                            <?php
                                                $t = $i =0;
                                                //$tot_cols = array();
                                                foreach($_POST['sel_table'] as $table)
                                                {
                                                    include 'templates/connect.php';
                                                    include 'templates/describe.php';
                                                    
                                                    $token = strtok($msg, "\n");
                                                    $cols = (int)$token;
                                                     for( $j = 0; $j < $cols; $j++ )
                                                     {
                                                        $token = strtok("\n");
                                                        sscanf($token, "%s", $colname);
                                                        echo "<option value='$t $j?$table.$colname'>$table  ->  $colname</option>";
                                                        $tot_cols[$i] = "$t $j?$table.$colname";
                                                        $i++;
                                                     }
                                                     $t++;
                                                     socket_close($sock);
                                                }
                                            ?>
                                         </select>
                                            <?php
                                                foreach($tot_cols as $col)
                                                {
                                                    echo '<input type="hidden" name="tot_cols[]" value="'. $col. '">';
                                                }
                                            ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="num_where">Conditions :</label></td>
                                    <td><input type="number" name="num_where" id="num_where" value="0"/></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><input type="submit" value="Submit"/></td>
                                </tr>
                            <?php
                        }
                        else if (!isset($_POST['last_state']))
                        {
                            ?>
                                <tr>
                                    <td><label for="sel_table">Table :</label></td>
                                    <td>
                                        <input type="hidden" value="1" name="last_state"/>
                                        <?php
                                            foreach($_POST['sel_table'] as $table)
                                            {
                                                echo "<input name='sel_table[]' type='hidden' value='$table'>";
                                                echo $table;
                                                echo '<br/>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label>Selected Columns :</label></td>
                                    <td>
                                        <?php
                                            echo "<input name='num_where' type='hidden' value='".$_POST['num_where']."'>";
                                            foreach($_POST['sel_cols'] as $sel_col)
                                            {
                                                echo "<input name='sel_cols[]' type='hidden' value='$sel_col'>";
                                                $column = explode('?', $sel_col); 
                                                echo $column[1];
                                                echo "<br/>";
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                                    
                                    for($i = 1; $i <= $_POST['num_where']; $i++)
                                    {
                                        ?>
                                        <tr>
                                            <td>Clause <?php echo $i; ?></td>
                                            <td>
                                                <div>
                                                   
                                                    <select name="wh_cl_left<?php echo $i;?>">
                                                        <?php
                                                            foreach($_POST['tot_cols'] as $sel_col)
                                                            {
                                                                $column = explode('?', $sel_col); 
                                                                echo "<option value='$sel_col'>$column[1]</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                Operator :
                                                <select name="wh_cl_op<?php echo $i;?>">
                                                    <option value="=">=</option>
                                                    <option value="<">&lt;</option>
                                                    <option value=">">&gt;</option>
                                                    <option value=">=">&gt;=</option>
                                                    <option value="<=">&lt;=</option>
                                                    <option value="!=">!=</option>
                                                </select>
                                                <div>
                                                    <input type="radio" name="wh_cl_opt<?php echo $i; ?>" id="val"  onclick="check(<?php echo $i?>,0)" >Value
                                                    <input type="radio" name="wh_cl_opt<?php echo $i; ?>" id="join" onclick="check(<?php echo $i?>,1)">Column<br/>
                                                    <input name="wh_cl_right<?php echo $i; ?>" value="" type="text" disabled = "true"/>
                                                    <select name="wh_cl_right_sel<?php echo $i; ?>" disabled = "true">
                                                        <?php
                                                            foreach($_POST['tot_cols'] as $sel_col)
                                                            {
                                                                $column = explode('?', $sel_col);
                                                                echo "<option value='$sel_col'>$column[1]</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </div>                                                
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td>Sorting :</td>
                                    <td>
                                        By :
                                        <select name="sort_col" style="max-width: 100px;">
                                            <option value="none" selected="selected">None</option>
                                            <?php
                                                foreach($_POST['sel_cols'] as $col)
                                                {
                                                    echo "<option value='$col'>$col</option>";
                                                }
                                            ?>
                                        </select>
                                        <select name="sort_or" style="max-width: 100px;">
                                            <option value="ASC">Ascending</option>
                                            <option value="DESC">Descending</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Limit :</td>
                                    <td><input type="number" name="limit_rows"/></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                       <input type="submit" value="Submit Query">
                                    </td>
                                </tr>
                            <?php
                        }
                    ?>
                </table>
            </form>
            <?php
                if(isset($res) && $res && isset($exec) && $exec)
                {
                    
            ?>  
                <h4 style="color: green;">Your Query was executed Successfully !!</h4>
                <h5 style="color: gray; font-size: 20px;"><?php echo $query; ?></h5>
                 <?php
                    if (!($bytes = socket_recv($sock, $msg, 30 , MSG_WAITALL))) 
                    {  
                          die("Could not receive data " . socket_strerror(socket_last_error($sock)));
                    }
                    $rec=explode(';',$msg);
                    $ct = trim($rec[0]);
                    $size = trim($rec[1]);
                    if (!($bytes = socket_recv($sock, $msg, $size, MSG_WAITALL))) 
                    {  
                        die("Could not receive data " . socket_strerror(socket_last_error($sock)));
                    }
                    $msg = trim($msg);
                    $msg = explode(';',$msg);
            
                    echo "<table border='1px' align='center'><tr>";
                    for($i = 0; $i < $colno; $i++ )
                    {
                        echo "<th>$msg[$i]</th>"; 
                    }
                    echo "</tr>";
                    while($ct--)
                    {
                        if (!($bytes = socket_recv($sock, $msg,$size, MSG_WAITALL))) 
                        {  
                              die("Could not receive data " . socket_strerror(socket_last_error($sock)));
                        }
                        $msg = trim($msg);
                        $msg = explode(';',$msg);
            
                        echo "<tr>";
                        for($i = 0; $i < $colno; $i++ )
                        {
                            echo "<td>$msg[$i]</td>"; 
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                    socket_close($sock);     
                }
                else if(isset($exec) && $exec)
                {
                    ?>
                        <h4 style="color: red;">Your Query was not Executed !!</h4>
                        <h5 style="color: gray; font-size: 20px;"><?php echo $query; ?></h5>
                    <?php
                }
            ?>
        </div>
    </body>
</html>