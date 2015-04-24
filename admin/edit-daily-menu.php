<!-- <pre> -->
<h1>Edit Daily Menu</h1>

<?php
	$page_class = 'create-menu';
	require_once("../_config/config.php");
    require_once("../_includes/restrict-access-green-heart-foods.php");
    require_once("../_includes/global-header.php");
    require_once("../_classes/Menu.php");
	require_once("../_classes/Servers.php");
	require_once("../_classes/Client.php");
	$client_id = $_GET['client-id'];
	$service_date = $_GET['service-date'];
	$meal_id = $_GET['meal-id'];
	$menu = new Menu();
	$menu->get_menu_form($client_id, $service_date, $meal_id);
?>

<footer>
	<p>Footer Stuff Goes Here</p>
</footer>