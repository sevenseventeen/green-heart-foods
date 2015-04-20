<?php 

session_start();
require_once("../_classes/User.php");
$user = new User();

if ($user->is_client_logged_in() != '1') {
	header('Location: login.php');
	exit();
}