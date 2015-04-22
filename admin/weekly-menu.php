<pre>

<?php
	require_once("../_config/config.php");
    require_once("../_includes/restrict-access-green-heart-foods.php");
    require_once("../_includes/global-header.php");
	require_once("../_classes/Menu.php");
	$menu = new Menu();
	$context = $green_heart_foods_access_level;
	$menu->get_weekly_menu_page($context);
?>