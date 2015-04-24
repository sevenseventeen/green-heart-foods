<pre>
<h1>Clients Page</h1>

<?php 
	require_once("../_config/config.php");
    require_once("../_includes/restrict-access-green-heart-foods.php");
    require_once("../_includes/global-header.php");
	require_once("../_classes/Client.php");
	require_once("../_classes/Messages.php");
	$client = new Client();
	$result = $client->get_all_clients();
	for ($i=0; $i < count($result); $i++) { 
		echo '<a href="weekly-menu.php?client-id='.$result[$i]['client_id'].'">'.$result[$i]['company_name'].'</a>';
		echo ' --- <a href="edit-client.php?client-id='.$result[$i]['client_id'].'">Edit</a><br />';
	}
?>
<?php 
	require_once(SERVER_ROOT . '/_classes/User.php');
	$user = new User();
	echo "Display Name: ".$user->get_user_display_name();
?>

<div class="message">
	<?php Messages::render(); ?>
</div>

<a href="create-client.php">Create Client</a>