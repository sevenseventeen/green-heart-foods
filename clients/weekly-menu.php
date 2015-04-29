<?php
	// require_once("../_config/config.php");
 	// require_once("../_includes/restrict-access-client.php");
 	// require_once("../_includes/global-header.php");
	// require_once("../_classes/Menu.php");
	// $menu = new Menu();
	// $menu->get_weekly_menu_page();
?>

<?php
	$page_class = 'weekly_menu_page';
	$page_title_detail = 'Weekly Menu';
	require_once("../_config/config.php");
    require_once(SERVER_ROOT . "/_includes/restrict-access-client.php");
    require_once(SERVER_ROOT . "/_includes/global-header.php");
    require_once(SERVER_ROOT . "/_classes/Menu.php");
    require_once(SERVER_ROOT . "/_classes/Messages.php");
	$menu = new Menu();
	$context = $client_access_level;
	$weekly_menu = $menu->get_weekly_menu_page($context);
?>

<?php Messages::render(); ?>

<div class="weekly_menu">
	<?php echo $weekly_menu; ?>
</div>

<?php require_once(SERVER_ROOT . "/_includes/global-footer.php"); ?>