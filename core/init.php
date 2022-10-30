<?php
session_start();

require_once 'classes/DB.php';
require_once 'classes/User.php';

$userObj = new \MyApp\User;

if (isset($_SESSION['user_id']) || !empty($_SESSION['user_id'])) {
    $user = $userObj->getUser($_SESSION['user_id']);
}

define('BASE_URL', "http://127.0.0.1/rtc/");
