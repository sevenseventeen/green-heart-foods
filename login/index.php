<h1>Client Login</h1>

<?php 
    session_start();
    require_once("../_config/config.php");
    require_once(SERVER_ROOT . '/_classes/Messages.php');
    require_once(SERVER_ROOT . '/_classes/User.php');
    $user = new User();
?>

<pre>
Users can login with one of two accounts, created by GHF at account creation
Password retrieval, account creation or account management are not included here. 
Successful login redirects to View Menus Page

<?php 

	echo "Messages: ".Messages::render();
	echo $user->get_login_form('client');