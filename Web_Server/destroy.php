<?php
include 'templates/sessioncheck.php';
if (isset($_GET['sess']) && $_GET['sess'] == 1)
{
    session_destroy();
    echo "Destroyed !!";
    header('Location:index.php');
}
else
{
    include 'templates/connect.php';
    $message = '10\n';
    if( ! socket_send ( $sock , $message , strlen($message) , 0))
    {
         $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Could not send data: [$errorcode] $errormsg \n");
    }
    socket_close($sock);
    session_destroy();
    header('Location:index.php?err=2');
}