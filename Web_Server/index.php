<?php
session_start();
if(isset($_SESSION['conn']))
{
    header('Location:select.php');
}
if(isset($_POST['user']))
{
    if(!isset($_POST['pass']))
    {
        $err = "Please Provide Password";
    }
    else if(!isset($_POST['port']))
    {
        $err = "Please Provide Port No";
    }
    else if(!isset($_POST['loc']))
    {
        $err = "Please Provide Host IP";
    }
    else
    {
        $host = $_POST['loc'];
        $user = $_POST['user'];
        $password = $_POST['pass'];
        $port = $_POST['port'];

        if($user != "root" || $password != "root")
        {
            $err = "Invalid Login Details";
        }
        else if(strlen($host) == 0 || strlen($port) == 0)
        {
            $err = "Invalid Host !";
        }
        else
        {
            $_SESSION['port_no'] = $_POST['port'];;
            $_SESSION['conn'] = 1;
            $_SESSION['host'] = $host;
            $_SESSION['user'] = $user;
            $_SESSION['pass'] = $password;

            header('Location:create.php');
        }
    }
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Query Builder</title>
        <link href="style/style.css" type="text/css" rel="stylesheet">
    </head>
    <body>
        <?php 
            require 'templates/head.php';
        ?>
        <div style="padding-top: 100px;">
            <?php
                if(isset($_REQUEST['err']))
                {
                    if($_REQUEST['err']==1)
                    {
                        echo '<center><p style="font-size: 25px; color: red;">Please Log In to Continue!!</p></center>';
                    }
                    else if($_REQUEST['err'] == 2)
                    {
                        echo '<center><p style="font-size: 25px; color: green;">Logged Out Successfully</p></center>';
                    }
                }
                if(isset($err))
                        echo '<center><p style="font-size: 25px; color: red;">'.$err.'</p></center>';
            ?>
            <div style="width: 300px; margin: auto; margin-top: 150px;">
                <form name="login" method="POST" action="index.php">
                    <table>
                        <tr>
                            <td><label for="user">Username</label></td>
                            <td><input type="text" name="user" id="user"/><br/></td>
                        </tr>
                        <tr>
                            <td><label for="pass">Password</label></td>
                            <td><input type="password" name="pass" id="pass"/><br/></td>
                        </tr>
                        <tr>
                            <td><label for="loc">Server Address</label></td>
                            <td><input type="text" name="loc" id="loc"/><br/></td>
                        </tr>
                        <tr>
                            <td><label for="port">Port Number</label></td>
                            <td><input type="text" name="port" id="port"/><br/></td>
                        </tr>
                        <!--<tr>
                            <td><label for="db">Database Name</label></td>
                            <td><input type="text" name="db" id="db"/><br/></td>
                        </tr>-->
                    </table>
                    <input type="submit" class="button" name="sub" value="Submit"/>
                </form>
            </div>
        </div>
    </body>
</html>