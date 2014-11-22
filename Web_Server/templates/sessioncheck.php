<?php
session_start();
if (!isset($_SESSION['conn']))
{
    header('Location:index.php?err=1');
}
