<?php
    $nick  = $_REQUEST['nick'];
    $group = $_REQUEST['group'];
	$promo = $_REQUEST['promo'];
	echo $Engine->buy_price($nick, $group, $promo);