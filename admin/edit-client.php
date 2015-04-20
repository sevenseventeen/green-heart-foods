<pre>
<h1>Edit Client</h1>

<?php 

	require_once('../_classes/Client.php'); 
	$client = new Client();
	$client_id = $_GET['client-id'];
	$result = $client->get_client_form($client_id);
	echo $result;

?>