<?php 
include_once 'core/init.php';
unset($_SESSION['user_id']);
session_regenerate_id();
header("location: login.php");
die();
?>