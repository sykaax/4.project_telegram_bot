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
  $hello[] = 'хай';
  $hello[] = 'здорова';
  $hello[] = 'здравствуйте';
  $hello[] = 'здрасьте';
  $hello[] = 'йо';
 
  $bot_hello = array();
  $bot_hello[] = 'И тебе привет';
  $bot_hello[] = 'Привет от голоса';
  $bot_hello[] = 'Доброго времени суток';
  $bot_hello[] = 'Привет привет';
 
  if (in_array(mb_strtolower($text), $hello)) {
    //пользователь поздоровался.
    //случайная фраза привет от бота
    $bot_resp = $bot_hello[rand(0, (count($bot_hello) - 1))];
    $data = array(
      'text' => $bot_resp,
    );
    return $data;
  }
  return NULL;
}
 
//проверка на ник
function commIsUser($text) {
  $text = trim($text);//обрезаем пробелы в начале и в конце
  $space = strpos($text, ' ');
  if (($space === FALSE) && (mb_substr($text, 0, 1) == '@')) {
    //возможно это ник пользователя
    //подключаемся к блокчейну
    require('vendor/autoload.php');
    $client = new WebSocket\Client("wss://ws.golos.io/");
    $req = json_encode(
      [
        'id' => 1, 'method' => 'get_accounts', 'params' => [[mb_substr($text, 1)]]
      ]
    );
    $client->send($req);
    $golos_resp = $client->receive();
    $resp_object = json_decode($golos_resp);
    if (!empty($resp_object->result)) {
      $obj = $resp_object->result[0];
      $user = array();
      $user[] = 'ID: ' . $obj->id;
      $user[] = 'Логин: ' . $obj->name;
      $user[] = 'Аккаунт создан: ' . $obj->created;
      $user[] = 'Последний раз голосовал: ' . $obj->last_vote_time;
      $user[] = 'Голосов: ' . $obj->balance;
      $user[] = 'Золота: ' . $obj->sbd_balance;
      $user[] = 'Создано постов: ' . $obj->post_count;
 
      //расчёт репутации
      $reputation = $obj->reputation;
      $user[] = 'Репутация: ' . round((max(log10(abs($reputation)) - 9,0) * (($reputation >= 0) ? 1 : -1) * 9 + 25), 3);
 
      $json_metadata = json_decode($obj->json_metadata);
      if (!empty($json_metadata->user_image)) {
        //фото
        // передавать не буду, так как у некоторых логинов "заколдованные" аватары и сообщение в телеграм не приходит
       // $user[] = 'Аватар: ' . $json_metadata->user_image;
      }
      $text = implode("\n", $user);
 
      $data = array(
        'text' => $text,
        'parse_mode' => 'Markdown',
      );
    }
    else {
      $data = array(
        'text' => 'Пользователь не найден.',
      );
    }
    $client->close();
 
    if (!empty($data)) {
      return $data;
    }
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

$bot_token = "653147430:AAGvSe_ZHtJ6bh2hEyM1yceQA05N4avloe4"; // Telegram bot токен
// указан выше и на obr.php $chat_id = "TELEGRAM ЧАТ ID"; // не забываем добавить TELEGRAM CHAT ID
$reply = "Working";
$url = "https://api.telegram.org/bot$bot_token/sendMessage";

$keyboard = array(
"keyboard" => array(array(array(
"text" => "/button"

),
array(
"text" => "contact",
"request_contact" => true // Данный запрос необязательный telegram button для запроса номера телефона

),
array(
"text" => "location",
"request_location" => true // Данный запрос необязательный telegram button для запроса локации пользователя

)

)), 
"one_time_keyboard" => true, // можно заменить на FALSE,клавиатура скроется после нажатия кнопки автоматически при True
"resize_keyboard" => true // можно заменить на FALSE, клавиатура будет использовать компактный размер автоматически при True
); 

$postfields = array(
'chat_id' => "$chat_id",
'text' => "$reply",
'reply_markup' => json_encode($keyboard)
);

print_r($postfields);
if (!$curld = curl_init()) {
exit;
}

curl_setopt($curld, CURLOPT_POST, true);
curl_setopt($curld, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($curld, CURLOPT_URL,$url);
curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);

$output = curl_exec($curld);

curl_close ($curld);
?>