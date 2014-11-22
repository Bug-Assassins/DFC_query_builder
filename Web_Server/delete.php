<?php
if(isset($_POST['sel_table']) && $_POST['num_where'] == 0)
{
    unset($_POST['sel_table']);
    header('Location:delete.php?err=1');
}
if(isset($_POST['last_state']))
{
    $query = "DELETE FROM ".$_POST['sel_table']." WHERE ";
    for($i = 1; $i <= $_POST['num_where']; $i++)
    {
        if($_POST['wh_cl_left'.$i] == "" || $_POST['wh_cl_right'.$i] == "" )
        {
            unset($_POST['sel_table']);
            header('Location:select.php?err=1');
        }
        else
        {
            if($i == 1)
            {
                $query .= $_POST['wh_cl_left'.$i]." ".$_POST['wh_cl_op'.$i]." ".$_POST['wh_cl_right'.$i]." ";
            }
            else
            {
                $query .= $_POST['wh_cl_sel'.$i]." ".$_POST['wh_cl_left'.$i]." ".$_POST['wh_cl_op'.$i]." ".$_POST['wh_cl_right'.$i]." ";
            }
        }
    }
    $res =mysqli_query($conn,$query);
}
?>
<html>
    <head>
        <title>Delete Query</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
        <script type="text/javascript">
            function put_where(a, b)
            {
                a.value= b.value;
            }
        </script>
    </head>
    <body>
        <?php 
            require 'templates/head.php';
            require 'templates/sidebar.php';
        ?>
        <div class="content">
            <h2>Delete Query</h2>
            <?php
                if(isset($_REQUEST['err']))
                {
                    if($_REQUEST['err'] == 1)
                    {
                        echo "<center><h4 style='color: red;'>Incorrect Query</h4></center>";
                    }
                }
                echo "<center><h4 style='color: red;'>Delete Command Not Yet Supported !!</h4></center>";
                 
            ?>
            <form action="delete.php" method="POST">
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
                                                $mysql_tables = mysqli_query($conn, "SHOW TABLES");
                                                while($row_table = mysqli_fetch_array($mysql_tables))
                                                {
                                                    echo "<option value='$row_table[0]'>$row_table[0]</option>";
                                                }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="num_where">Conditions :</label></td>
                                    <td><input type="number" name="num_where" id="num_where"/></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><input type="submit" value="Submit Table"/></td>
                                </tr>
                            <?php
                        }
                        else if(!isset($_POST['last_state']))
                        {
                            ?>
                                <tr>
                                    <td><label>Table :</label><input type="hidden" value="1" name="last_state"/></td>
                                    <td><?php echo $_POST['sel_table']; ?></td>
                                </tr>
                            <?php
                            echo "<input name='sel_table' type='hidden' value='".$_POST['sel_table']."'>";
                            echo "<input name='num_where' type='hidden' value='".$_POST['num_where']."'>";
                            for($i = 1; $i <= $_POST['num_where']; $i++)
                            {
                                ?>
                                <tr>
                                    <td>Clause <?php echo $i; ?></td>
                                    <td>
                                        <?php
                                        if($i > 1)
                                        {?>
                                        Combiner :
                                        <select name="wh_cl_sel<?php echo $i;?>" id="wh_cl_sel<?php echo $i;?>">
                                            <option value="AND" selected="selected">AND</option>
                                            <option value="OR">OR</option>
                                        </select>
                                        <?php } ?>
                                        <div>
                                            <input name="wh_cl_left<?php echo $i; ?>" value="" type="text"/>
                                            <select name="wh_cl_left_sel<?php echo $i; ?>" onchange="put_where(wh_cl_left<?php echo $i; ?>, wh_cl_left_sel<?php echo $i; ?>);" style="max-width: 50px;">
                                                <?php
                                                    $colres = mysqli_query($conn, "DESCRIBE ".$_POST['sel_table']);
                                                    while($col = mysqli_fetch_array($colres))
                                                    {
                                                        echo "<option value='$col[0]'>$col[0]</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        Operator :
                                        <select name="wh_cl_op<?php echo $i;?>">
                                            <option value="=">=</option>
                                            <option value=">=">&gt;=</option>
                                            <option value="<=">&lt;=</option>
                                            <option value="!=">!=</option>
                                        </select>
                                        <div>
                                            <input name="wh_cl_right<?php echo $i; ?>" value="" type="text"/>
                                            <select name="wh_cl_right_sel<?php echo $i; ?>" onchange="put_where(wh_cl_right<?php echo $i; ?>, wh_cl_right_sel<?php echo $i; ?>);" style="max-width: 50px;">
                                                <?php
                                                    $colres = mysqli_query($conn, "DESCRIBE ".$_POST['sel_table']);
                                                    while($col = mysqli_fetch_array($colres))
                                                    {
                                                        echo "<option value='$col[0]'>$col[0]</option>";
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
                                    <td colspan="2"><input type="submit" value="Submit Query"/></td>
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