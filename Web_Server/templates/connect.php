<?php
if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

//echo "Socket created";
$address = $_SESSION['host'];//'10.50.36.115';


if(!socket_connect($sock , $address , $_SESSION['port_no']))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Could not connect: [$errorcode] $errormsg \n");
}
//echo "Connection established";
return $sock;
?>