<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/engine/classes/Admin.class.php';
$Admin = new Admin();
	if(isset($_GET['code'])){
		$params = array(
			'client_id' => "6391463",
			'client_secret' => "wMv9LfueP6I8c8grfm73",
			'code' => $_GET['code'],
			'redirect_uri' => 'http://hype-mine.ru/engine/auth.php'
		);
		$token = json_decode(file_get_contents('https://oauth.vk.com/access_token?'.http_build_query($params)), true);
		if(!$token) {
			exit('ОШИБКА token');
		}
			$params_1 = array(
				'user_ids'         => $token['user_id'],
				'fields'       => 'uid,first_name,last_name,photo_200_orig,photo_200',
				'access_token' => $token['access_token']
			);
			$userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get?'.http_build_query($params_1)), true);
			if(isset($userInfo['response'][0]['uid'])){
				$userInfo = $userInfo['response'];
				$result = true;
			}
			$q = $Admin->engine->query_result("SELECT * FROM `users` WHERE secret = '".$token['user_id']."'");
			$hash = md5($Admin->generateCode(10));
			if(isset($q->secret)){
				$Admin->engine->query("UPDATE `users` SET hash = '".$hash."', access_token = '".$token['access_token']."' WHERE secret = '".$token['user_id']."'");
				$_SESSION['user_id'] = $q->id;
				$_SESSION['user_hash'] = $hash;
			}else{
				$Admin->engine->query("INSERT INTO `users` (`secret`, `name`, `surname`, `hash`, `auth_type`, `access_token`, `perm`) VALUES ('".$token['user_id']."', '".$userInfo['first_name']."', '".$userInfo['last_name']."', '".$hash."','vk','".$token['access_token']."', '')");
				$_SESSION['user_id'] = $Admin->engine->insert_id;
				$_SESSION['user_hash'] = $hash;
			}
			echo "Авторизация...";
			$Admin->engine->redirect("/lk");
			$_SESSION['info_array'] = $userInfo;
			exit;
	}
