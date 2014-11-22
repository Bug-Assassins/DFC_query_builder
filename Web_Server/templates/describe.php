<?php
if(isset($_POST["table_name"]))
{
    $table = $_POST["table_name"];
}
$msg = "9\n".$table;
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

?>
