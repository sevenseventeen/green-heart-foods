<pre>

<?php
	require_once("../_config/config.php");
    require_once("../_includes/restrict-access-client.php");
    require_once("../_includes/global-header.php");
	require_once("../_classes/Menu.php");
	$menu = new Menu();
	$menu->get_weekly_menu_page();
?>