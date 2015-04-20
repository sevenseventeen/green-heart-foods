<?php 

	session_start();
	define('BASE_PATH', '/green-heart-foods');

	require_once("../_classes/User.php");
	$user = new User();

	if ($user->is_green_heart_foods_logged_in() != '1') {
		header('Location: login.php');
		exit();
	}

?>

<!DOCTYPE HTML>

<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Your Website</title>
</head>
<link rel="stylesheet" type="text/css" href="<?php echo BASE_PATH; ?>/_css/main.css">
<script type="text/javascript" src="../_javascript/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="../_javascript/main.js"></script>
<body class="<?php echo $page_class; ?>" >