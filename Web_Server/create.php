<?php
    include 'templates/sessioncheck.php';
?>
<html>
     <head>
        <title>Create Table</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
    </head>
    <body>
        <?php
	 
        require 'templates/head.php';
        require 'templates/sidebar.php';
	$flag=true;
        ?>
        <div class="content">
        <h2>Create Table</h2>
	<form action="" method="POST">
            <table>
		<tr>
                    <td>Enter Table name</td>
                    <td><input type="text" name="name" <?php 
                                                            if(isset($_POST['name']))
                                                            {
                                                                echo 'value="'.$_POST['name'].'"';
                                                            }
                                                       ?>/></td>
		</tr>
		<tr>
                    <td>Number of columns</td>
                    <td><input type="number" name="col" min="1" <?php 
                                                                    if(isset($_POST['col']))
                                                                    {
                                                                        echo 'value="'.$_POST['col'].'"';
                                                                    }
                                                                ?>/></td>
		</tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="sub" value="OK"/></td>
                </tr>
            </table>
	</form>

<?php 
    if(isset($_POST['sub']))
    {
   
        if(!isset($_POST['name']) || trim($_POST['name']) == '')
        {
            echo "<h3>Table name missing</h3>";
            $flag = false;
            
        }
        else if(!isset($_POST['col']) || trim($_POST['col']) == '')
        {
            echo "<h3>Number of columns missing</h3>";
            $flag = false;
        }

    }

    if(isset($_POST['sub1']) || (isset($_POST['sub']) && $flag === true))
    {
?>
	<form action="" method="POST">
            <table>
		<tr>
                    <th>Column Name</th>
                    <th>Data Type</th>
                    <th>Size</th>
		</tr>
<?php 
            for( $i = 1;$i <= $_POST['col'];$i++ )
            {				
?>
            	<tr>
                    <td><input type="text" name=<?php 
                                                    echo '"colname'.$i.'"'; 
                                                    if(isset($_POST["colname$i"]))
                                                    {
                                                        echo 'value="'.$_POST["colname$i"].'"';
                                                    }
                                                ?>/></td>
                    <td>
                        <select name=<?php echo '"coltype'.$i.'"';?>>
                            <option value="INTEGER" <?php
                                                        if(isset($_POST["coltype$i"]) && $_POST["coltype$i"]==="INTEGER")
                                                        {
                                                            echo 'selected="selected"';
                                                        }
                                                    ?>>INTEGER</option>
                            <option value="VARCHAR" <?php
                                                        if(isset($_POST["coltype$i"]) && $_POST["coltype$i"]==="VARCHAR")
                                                        {
                                                            echo 'selected="selected"';
                                                        }
                                                    ?>>VARCHAR</option>
                            <option value="DATE" <?php
                                                        if(isset($_POST["coltype$i"]) && $_POST["coltype$i"]==="DATE")
                                                        {
                                                            echo 'selected="selected"';
                                                        }
                                                 ?>>DATE</option>
			</select>
                    </td>
                    <td><input type="number" min="1" name=<?php 
                                                            echo '"colsize'.$i.'"';
                                                            if(isset($_POST["colsize$i"]))
                                                            {
                                                                echo 'value="'.$_POST["colsize$i"].'"';
                                                            }
                                                          ?>/></td>
		</tr>
<?php 
            } 
?>		<tr>
                    <td><input type="submit" name="sub1" value="Go"></td> 
                    <td><input type="hidden" name="name" value=<?php echo '"'.$_POST['name'].'"';?>></td>
                    <td><input type="hidden" name="col" value=<?php echo '"'.$_POST['col'].'"';?>></td>
		</tr>
            </table>
	</form>
<?php
    }
	if(isset($_POST['sub1']))
	{
            $f = 0;
            for($i = 1; $i <= $_POST['col']; $i++)
            {
		if(!isset($_POST["colname$i"]) || trim($_POST["colname$i"])=="")
		{
                    $f = 1;
                    break;
		}
		else if($_POST["coltype$i"]!== "DATE")
                {
                    if(!isset($_POST["colsize$i"]) || trim($_POST["colsize$i"])=="")
                    {
                        $f = 2;
                        break;
                    }
                }
            } 
            
            if($f === 1)
            {
		echo "One or more of the column names empty";
            }
	    else if($f === 2)
            {
                echo "One or more of the column sizes not defined";
            }
            
            else 
            {
               
               include 'templates/connect.php';
               $message = "1\n".$_POST['name']." ".$_POST['col']."\n";
                
               $query = "CREATE TABLE ".$_POST['name']." ( ";
               for($i = 1; $i <= $_POST['col']; $i++)
               {
                   if($i > 1)
                   {
                       $query .= ", ";
                   }
                   $temp = $_POST["colname$i"]." ".$_POST["coltype$i"];
                   $message .= $_POST["colname$i"]." ";  
                   if($_POST["coltype$i"] == "INTEGER")
                   {
                       $message .= "1 ".$_POST["colsize$i"];
                       $temp .="(".$_POST["colsize$i"].")"; 
                   }
                   else if($_POST["coltype$i"] == "VARCHAR")
                   {
                       $message .= "2 ".$_POST["colsize$i"];
                       $temp .= "(".$_POST["colsize$i"].")"; 
                   }
                   else
                   {
                       $message .= "3";
                   }
                   $message .= "\n";
                   $query .= $temp;
               }
               $query .= " )";
               /*if($res = mysqli_query($con, $query))
               {
                   echo "<script>alert('Table Created')</script>";
               }*/
               
               if( ! socket_send ( $sock , $message , strlen($message) , 0))
               {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);
     
                    die("Could not send data: [$errorcode] $errormsg \n");
               }
               socket_close($sock);
?>      
        <textarea rows="8" cols="80"><?php echo $query ?></textarea>
<?php
            }
	}
        
        
?>	
        </div>
	</body>
</html>