<!--View Clients Page
List of current clients displayed from database
Displayed as images.
Create new client links to Create Client Page.
Logos link to Client Page
Remove client checkbox-->
<!-- Remove links to Remove Client Page -->
<?php 
	echo "<h1>Clients Page</h1>";
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

<div class="message">
	<?php echo "Flash Messages: " . Messages::render(); ?>
</div>

<a href="create-client.php">Create Client</a>