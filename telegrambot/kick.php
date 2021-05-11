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


  if (!empty($resp)) {
    $resp['chat_id'] = $chat_id;
    requestkick($resp);
    return TRUE;
  }
  
  requestkick($resp){
    $data = array(
      'text' => $bot_resp,
	  'chat_id' => '@';
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