<?php
session_start();
if (isset($_SESSION['conn']))
{
    echo "Connected :";
}