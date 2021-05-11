<?php
if($_GET['gay'] == 'udasjdiyhutgAYIHSUOJDAOsjdiauahs1HDUJIPAUOsudgyhua311aiIAo10-1hfOanwepEO4gyishuodja19212asudgyhuaojispdohaigstudagyishuodja192'){

$dbconnect = mysqli_connect("127.0.0.1", "u0562844_default", "6G7!bm7!basjdiauahs1a!bm1kW6kW6W63g9A1zW63g9A1z", "u0562844_default");

  $sql = mysqli_query($dbconnect, "SELECT * FROM `unit` WHERE `unitpayend` = '1' ");
  while ($result = mysqli_fetch_array($sql)) {
    print_r ($result);
	echo '<br><br>Следующая транзакция<br><br>';
  }
  
  echo '<br><br><br><br><br><br>Закончились транзакции, начинаются пользователи бота vip телеги<br><br><br><br><br><br>';
  
$sql2 = mysqli_query($dbconnect, 'SELECT * FROM `botvip` ');
  while ($result2 = mysqli_fetch_array($sql2)) {
    print_r ($result2);
	echo '<br><br>Следующий пользователь<br><br>';
  }
}
else{
	echo 'как говорил ленил, я ленин бл*********************************************ть';
	echo '<br> p.s мусора сосац';
}

?>