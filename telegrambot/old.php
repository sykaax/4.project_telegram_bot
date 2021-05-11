<?php


function teleToLog($log) {
  $myFile = 'log.txt';
  $fh = fopen($myFile, 'a') or die('can\'t open file');
  if ((is_array($log)) || (is_object($log))) {
    $updateArray = print_r($log, TRUE);
    fwrite($fh, $updateArray."\n");
  } else {
    fwrite($fh, $log . "\n");
  }
  fclose($fh);
}
 
//обработка запроса от пользователя
function getUserRequest($text, $chat_id) {
  $resp = commIsHello($text);
  if (!empty($resp)) {
    $resp['chat_id'] = $chat_id;
    requestToTelegram($resp);
    return TRUE;
  }
  $resp = commIsUser($text);
  if (!empty($resp)) {
    $resp['chat_id'] = $chat_id;
    requestToTelegram($resp);
    return TRUE;
  }
}
 
//проверка на приветствие
function commIsHello($text) {
  $hello = array();
  $hello[] = 'привет';
  $hello[] = '/start';
  $hello[] = 'здорова';
  $hello[] = 'здравствуйте';
  $hello[] = 'здрасьте';
  $hello[] = 'йо';
 
  $bot_hello = array();
  $bot_hello[] = 'И тебе привет';
 
  if (in_array(mb_strtolower($text), $hello)) {
    //пользователь поздоровался.
    //случайная фраза привет от бота
    $bot_resp = $bot_hello[rand(0, (count($bot_hello) - 1))];
	
$button_1 = array('text' => 'Купить VIP подписку на 1 месяц');
$button_2 = array('text' => 'Включить оповщение прогнозам VIP');
$button_3 = array('text' => 'Выключить оповщение прогнозам VIP');
$button_4 = array('text' => 'Узнать когда кончится ваша VIP подписка');
$keyboard = array('keyboard' => array(array($button_1, $button_2)), 'resize_keyboard' => true);


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


//отправка запроса в чат
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