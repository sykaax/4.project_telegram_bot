<?php
//задаём наш токен, полученный при создании бота и указываем путь к API телеграма
define('BOT_TOKEN', '653147430:AAGvSe_ZHtJ6bh2hEyM1yceQA05N4avloe4');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

// данный файл отвечает за коннект к базе данных и последующим опперация станадартным.
$dbconnect = mysqli_connect("127.0.0.1", "u0562844_default", "6G7!bm7!basjdiauahs1a!bm1kW6kW6W63g9A1zW63g9A1z", "u0562844_default");
	

//принимаем запрос от бота(то что напишет в чате пользователь)
$content = file_get_contents('php://input');
//превращаем из json в массив
$update = json_decode($content, TRUE);
//получаем id чата
$chat_id = $update['message']['chat']['id'];
//получаем username человека
$username = $update['message']['chat']['username'];
//получаем текст запроса
$text = $update['message']['text'];


//конект чтобы проверить есть ли такая запись или нет.
$mysqlqidtovar = mysqli_query($dbconnect, "SELECT * FROM `botvip` WHERE `username` = '$username' and `vipenable` = '1' ");
	
	if (  mysqli_num_rows($mysqlqidtovar) == 0 )
	{
		// записываем переменную что у человека не активна подписка
		$sqlanservipenable = '0';
		
	}
	
	//ниже выводится код со страницой товара
	else {
	// создание масива из таблицы MYSQL
	$associdtovar = mysqli_fetch_assoc ($mysqlqidtovar);	
	
	// назначение данных из масива базы данных полученого
	$vipdatebuy = $associdtovar['vipdatebuy'];
	
	// записываем переменную что у человека активна подписка
		$sqlanservipenable = '1';
	}


//проверка на приветствие  и добавление переменных для работы

  $resp = commIsHello($text, $username, $sqlanservipenable, $dbconnect, $vipdatebuy);
  if (!empty($resp)) {
    $resp['chat_id'] = $chat_id;
    requestToTelegram($resp);
    return TRUE;
  }

 
//проверка на приветствие
function commIsHello($text, $username, $sqlanservipenable, $dbconnect, $vipdatebuy) {


	
  $hello = array();
  $hello[] = 'привет';
  $hello[] = 'включить оповещение прогнозам vip';
  $hello[] = 'выключить оповещение прогнозам vip';
  $hello[] = 'купить vip подписку на 1 месяц';
  $hello[] = 'узнать когда кончится ваша vip подписка';
  $hello[] = 'вернуться назад';
  $hello[] = 'оповещения о vip прогнозах включены, ожидайте. мы вас уведомим о новых прогнозах, как они появятся';
  $hello[] = 'вернуться назад2';
  $hello[] = 'вернуться назад3';
  $hello[] = '/start';
 
  $bot_hello = array();
  $bot_hello[] = '. ';
   $bot_hello[] = '. ';
 
  if (in_array(mb_strtolower($text), $hello)) {
    //пользователь поздоровался.
    //случайная фраза привет от бота
    $bot_resp = $bot_hello[rand(0, (count($bot_hello) - 1))];
	if($text == '/start' or $text == 'Вернуться назад'){
$bot_resp = 'Добро пожаловать на главную страницу данного бота. Вы можете использовать кнопки меню для навигации по функциям бота.(может быть прекрыто клавиатурой на телефоне)';
$button_1 = array('text' => 'Купить VIP подписку на 1 месяц');
$button_2 = array('text' => 'Включить оповещение прогнозам VIP');
$button_3 = array('text' => 'Выключить оповещение прогнозам VIP');
$button_4 = array('text' => 'Узнать когда кончится ваша VIP подписка');
$keyboard = array('keyboard' => array(array($button_1), array($button_2), array($button_3), array($button_4)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	elseif($text == 'Узнать когда кончится ваша VIP подписка' and $sqlanservipenable == '0'){
$bot_resp = 'У вас нету подпиcки, можете её купить прямо сейчас'; 
$button_1 = array('text' => 'Купить VIP подписку на 1 месяц');
$button_2 = array('text' => 'Вернуться назад');
$keyboard = array('keyboard' => array(array($button_1), array($button_2)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	elseif($text == 'Узнать когда кончится ваша VIP подписка' and $sqlanservipenable == '1'){
$bot_resp = 'Ваша подписка заканчивается ' . date('Y-m-d H:i:s',strtotime("$vipdatebuy +1 month")) . ' мы вас оповестим об её окончание';
$button_1 = array('text' => 'Купить VIP подписку на 1 месяц');
$button_2 = array('text' => 'Вернуться назад');
$keyboard = array('keyboard' => array(array($button_1), array($button_2)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	elseif($text == 'Включить оповещение прогнозам VIP' and $sqlanservipenable == '0'){// тут ошибка и не заходит крче скрипт
$bot_resp = 'У вас нету подписки, можете её купить прямо сейчас';
$button_1 = array('text' => 'Купить VIP подписку на 1 месяц');
$button_2 = array('text' => 'Вернуться назад');
$keyboard = array('keyboard' => array(array($button_1), array($button_2)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	elseif($text == 'Включить оповещение прогнозам VIP' and $sqlanservipenable == '1'){
$bot_resp = 'Оповещения о VIP прогнозах включены, ожидайте. Мы вас уведомим о новых прогнозах, как они появятся';
//код изменения в базе данных записи о том что прогнозы теперь уведомления будут приходить
$mysqlfreetovartabzakaz = mysqli_query($dbconnect,"UPDATE `botvip` SET `waitprognoz` = '1' WHERE `username` = '$username' ")or die("Ошибка " . mysqli_error($dbconnect));
$button_1 = array('text' => 'Вернуться назад');
$keyboard = array('keyboard' => array(array($button_1)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	elseif($text == 'Выключить оповещение прогнозам VIP' and $sqlanservipenable == '0'){
$bot_resp = 'Оповещения по прогнозам VIP отключены' . $username . $sqlanservipenable;
$button_1 = array('text' => 'Вернуться назад');
$keyboard = array('keyboard' => array(array($button_1)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
}
    elseif($text == 'Выключить оповещение прогнозам VIP' and $sqlanservipenable == '1'){
$bot_resp = 'Оповещения по прогнозам VIP отключены';
//код изменения в базе данных записи о том что прогнозы теперь уведомления не будут приходить
$mysqlfreetovartabzakaz = mysqli_query($dbconnect,"UPDATE `botvip` SET `waitprognoz` = '0' WHERE `username` = '$username' ")or die("Ошибка " . mysqli_error($dbconnect));
$button_1 = array('text' => 'Вернуться назад');
$keyboard = array('keyboard' => array(array($button_1)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	elseif($text == 'Купить VIP подписку на 1 месяц'){
$bot_resp = 'Для покупки подписки VIP прогнозов перейдите пожалуйста на сайт http://prognozinasport.ru/';
$button_1 = array('text' => 'Вернуться назад');
$keyboard = array('keyboard' => array(array($button_1)), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	else{
		$keyboard = array('keyboard' => array(array()), 'resize_keyboard' => true, 'one_time_keyboard' => true);
	}
	
    $data = array(
      'text' => $bot_resp,
	  'disable_notification' => TRUE,
	  'parse_mode' => 'HTML',
	  'reply_markup' => json_encode($keyboard, TRUE)
    )
	;
    return $data;
  }
   return NULL;
}

 function requestToTelegram($data, $type = 'sendMessage') {
  if( $curl = curl_init() ) {
    curl_setopt($curl, CURLOPT_URL, API_URL . $type);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_exec($curl);
    curl_close($curl);
  }
 }


?>