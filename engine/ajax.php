<?php
if($_GET['select'] == "admin")
{
	require_once $_SERVER['DOCUMENT_ROOT'].'/engine/classes/Admin.class.php';
	$Admin = new Admin;
}
else
{
	require_once $_SERVER['DOCUMENT_ROOT'].'/engine/classes/Main.class.php';
	$Engine = new Engine;
}
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/engine/ajax/'.$_GET['type'].'.php')){
				$type = $_GET['get'];
				include($_SERVER['DOCUMENT_ROOT'].'/engine/ajax/'.$_GET['type'].'.php');
			}
			else die("403");