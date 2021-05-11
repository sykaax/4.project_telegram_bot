<?php

/**
 *  Demo handler for your projects
 *
 * @link http://help.unitpay.ru/article/35-confirmation-payment
 */

require_once('./orderInfo.php');
require_once('../UnitPay.php');

$unitPay = new UnitPay($secretKey);

try {
    // Validate request (check ip address, signature and etc)
    $unitPay->checkHandlerRequest();

	//присвоение переменных для отправки их в бд для проверки, существует ли такая запись в бд.
$sqlaccountask = $_GET['params']['account'];
$sqlsumask = $_GET['params']['orderSum'];
$sqlunitpayidask = $_GET['params']['unitpayId'];

// Запрос в базу данных на наличие заказа
//$dbconnect = mysqli_connect("87.236.19.170", "wispworld_pns", "t78j82duGJRXjsihgt13", "wispworld_pns");
$dbconnect = mysqli_connect("127.0.0.1", "u0562844_default", "6G7!bm7!basjdiauahs1a!bm1kW6kW6W63g9A1zW63g9A1z", "u0562844_default");
//конект чтобы проверить есть ли такая запись или нет.
$mysqlqidtovar = mysqli_query($dbconnect, "SELECT * FROM `unit` WHERE `orderid` = '$sqlaccountask' and `ordersum` = '$sqlsumask' ");
	
	if (  mysqli_num_rows($mysqlqidtovar) == 0 )
	{
		// пишем что такой записи на найдено
		throw new InvalidArgumentException('That order not find in database');
		exit();
		
	}
	//ниже выводим что всё хорошо и товар найден, отправляем положительный результат
	else {
	
	// создание масива из таблицы MYSQL
	$associdtovar = mysqli_fetch_assoc ($mysqlqidtovar);	
	// назначение данных из масива базы данных полученого
	$sqlsum = $associdtovar['ordersum'];
	$sqlaccount = $associdtovar['orderid'];
	$sqltelegram = $associdtovar['telegram'];
	$sqltdate = $associdtovar['dateoplata'];
	}
	
    list($method, $params) = array($_GET['method'], $_GET['params']);

    // Very important! Validate request with your order data, before complete order
    if ( 
        $params['orderSum'] != $sqlsum ||
        $params['orderCurrency'] != $orderCurrency ||
        $params['account'] != $sqlaccount ||
        $params['projectId'] != $projectId
    ) {
        // logging data and throw exception
        throw new InvalidArgumentException('Order validation Error!');
    }

    switch ($method) {
        // Just check order (check server status, check order in DB and etc)
        case 'check':
            print $unitPay->getSuccessHandlerResponse('Check Success. Ready to pay.');
            break;
        // Method Pay means that the money received
        case 'pay':
            // Please complete order
            print $unitPay->getSuccessHandlerResponse('Pay Success');
			//записываем в базу данных что платеж прошёл успешно
		$mysqlfreetovartabzakaz = mysqli_query($dbconnect,"UPDATE `unit` SET `unitpayend` = '1', `unitpayid` = '$sqlunitpayidask'  WHERE `orderid` = '$sqlaccount' ")or die("Ошибка " . mysqli_error($dbconnect));
		
		// далее проверяем есть ли в базе ботов человек с такой телегой или нет, если нету то создаем строку, если есть просто обновляем дату и enablevip 1 ставим.
		$mysqlqidtovar4 = mysqli_query($dbconnect, "SELECT * FROM `botvip` WHERE `username` = '$sqltelegram' ");
	
	       if (  mysqli_num_rows($mysqlqidtovar4) == 0 )
	       {
	       $mysqlqzakaz4 = mysqli_query($dbconnect, "INSERT INTO `botvip` (`id`, `username`, 
           `vipenable`, `vipdatebuy`, `waitprognoz` ) 
           VALUES (NULL, '$sqltelegram', '1', '$sqltdate', '0'  ); ") or die("Ошибка " . mysqli_error($dbconnect));
	       }
	      else {
	       $mysqlfreetovartabzakaz4 = mysqli_query($dbconnect,"UPDATE `botvip` SET `vipenable` = '1', `vipdatebuy` = '$sqltdate'  WHERE `username` = '$sqltelegram' ")or die("Ошибка " . mysqli_error($dbconnect));
	      }
            break;
        // Method Error means that an error has occurred.
        case 'error':
            // Please log error text.
            print $unitPay->getSuccessHandlerResponse('Error logged');
            break;
        // Method Refund means that the money returned to the client
        case 'refund':
            // Please cancel the order
            print $unitPay->getSuccessHandlerResponse('Order canceled');
            break;
    }
// Oops! Something went wrong.
} catch (Exception $e) {
    print $unitPay->getErrorHandlerResponse($e->getMessage());
}
