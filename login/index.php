<pre>
<h1>Login</h1>

<?php 
    session_start();
    require_once("../_config/config.php");
    require_once(SERVER_ROOT . '/_classes/Messages.php');
    require_once(SERVER_ROOT . '/_classes/User.php');
    $user = new User();

    echo Messages::render();
	echo $user->get_login_form('client');
?>	