<?php
	
    require_once("../_classes/Client.php");
    require_once("../_classes/Messages.php");
    require_once("../_includes/global-header.php");
	require_once("../_classes/Menu.php");
	$menu = new Menu();
	return $menu->get_daily_menu_page();

?>