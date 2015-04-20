<?php 

require_once("../_includes/global-header.php");
require_once('../_classes/Client.php'); 
$client = new Client();
$result = $client->get_client_form();
echo $result;

?>