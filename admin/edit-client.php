<pre>
<h1>Edit Client</h1>

<?php 
	require_once("../_config/config.php");
    require_once("../_includes/restrict-access-green-heart-foods.php");
    require_once("../_includes/global-header.php");
	require_once('../_classes/Client.php'); 
	$client = new Client();
	$client_id = $_GET['client-id'];
	$result = $client->get_client_form($client_id);
	echo $result;
?>