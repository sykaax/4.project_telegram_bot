<?php

/**
 * Payment form
 *
 * @link http://payment.prognozinasport.ru/sample/initPaymentForm.php?telg=alinapns&&email=sykaax@gmail.com&&vip=1
 */

require_once('./orderInfo.php');
require_once('../UnitPay.php');

function funcformatstremail($str) // функция удаления фредоностоного кода и оставлений букв вроде как XD вроде ну должны
// удалятся собаки и т д. делалась для email но используется и в другой части кода на buyw.php например код из payment.gamebop.ru
    {
        $str = trim($str);
        $str = stripslashes($str);
        $str = htmlspecialchars($str);
        return $str;
    }

// Создание даты для записи время заказа в базе данных
// https://gamebop.ru/prognasp/sample/initPaymentForm.php?telg=alinapns&&email=sykaax@gmail.com&&vip=2
// 1rub https://gamebop.ru/prognasp/sample/initPaymentForm.php?telg=alinapns&&email=sykaax@gmail.com&&vip=Umenyaboleznzoofiliya.Menyaochentyanetnasobak.BrosilamenyadevchonkaLiyaIskazalato_chtoyamudak.Yaebusobak_vsegdagotovSrazutrahnutneskolkokotov.Da_yazoofil_negovori.LuchshemnesobachkupodariYaebusobak_vsegdagotovSrazutrahnutneskolkokotov.Da_yazoofil_negovori.
$date = date('Y-m-d H:i:s');
$ordertelegramgg = $_GET['telg'];
$orderemailgg = $_GET['email'];
$ordervipgg = $_GET['vip'];
$ordertelegram =  funcformatstremail($ordertelegramgg);
$orderemail =  funcformatstremail($orderemailgg);
$ordervip =  funcformatstremail($ordervipgg);

// отсюда берут данные как спрашиватель перед платежем, так и отправитель форма. так видимо и Pay
if($ordervip == '2'){
	$orderSum       =  6899;
}
elseif($ordervip == 'Umenyaboleznzoofiliya.Menyaochentyanetnasobak.BrosilamenyadevchonkaLiyaIskazalato_chtoyamudak.Yaebusobak_vsegdagotovSrazutrahnutneskolkokotov.Da_yazoofil_negovori.LuchshemnesobachkupodariYaebusobak_vsegdagotovSrazutrahnutneskolkokotov.Da_yazoofil_negovori.'){
	$orderSum       =  1;
}
elseif($ordervip == '3'){
	$orderSum       =  699;
}
else{
  $orderSum       =  1499;
}
$orderDesc      = 'Оплата подписки на VIP прогнозы телеграмм аккаунту "'.$ordertelegram.'"';
$orderCurrency  = 'RUB';



$dbconnect = mysqli_connect("127.0.0.1", "u0562844_default", "6G7!bm7!basjdiauahs1a!bm1kW6kW6W63g9A1zW63g9A1z", "u0562844_default");
//запись в базу данных заказа
$mysqlqzakaz = mysqli_query($dbconnect, "INSERT INTO `unit` (`orderid`, `ordersum`, 
`unitpayend`, `unitpayid`, `telegram`, `email`, `dateoplata` ) 
VALUES (NULL, '$orderSum', '0', '0', '$ordertelegram', '$orderemail', '$date'  ); ") or die("Ошибка " . mysqli_error($dbconnect));

//запрос в базу данных чтобы узнать ид account и отдать его юнит пею
$mysqlqidtovar = mysqli_query($dbconnect, "SELECT `orderid` FROM `unit` WHERE `telegram` = '$ordertelegram' and `dateoplata` = '$date' ");
	
	if (  mysqli_num_rows($mysqlqidtovar) == 0 )
	{
		// пишем что такой записи на найдено
		throw new InvalidArgumentException('Oplata create eror! database!');
	}
	//ниже выводим что всё хорошо и товар найден, отправляем положительный результат
	else {
	
	// создание масива из таблицы MYSQL
	$associdtovar = mysqli_fetch_assoc ($mysqlqidtovar);	
	// назначение ид для того чтобы отправить его в form юнит ниже
	$sqlorderidfind = $associdtovar['orderid'];
	}

$unitPay = new UnitPay($secretKey);

$redirectUrl = $unitPay->form(
    $publicId,
    $orderSum,
    $sqlorderidfind,
    $orderDesc,
    $orderCurrency
);

header("Location: " . $redirectUrl);
