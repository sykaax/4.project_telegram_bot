<?php
date_default_timezone_set('Europe/Moscow');
class Engine{

	public $db;
	public $cfg;

	public function __construct(){
		require_once($_SERVER['DOCUMENT_ROOT'].'/engine/config.php');
		$this->cfg = $config;
		$this->db = new mysqli($config['db']['db_host'], $config['db']['db_user'], $config['db']['db_pass'], $config['db']['db_name']);
		if($this->db->connect_error){
			die("Couldn't connect to MySQLi: ".$this->db->connect_error);
		}
		if (!$this->db->set_charset("utf8")) {
			die("Ошибка при загрузке набора символов utf8: ".$this->db->error);
		}
	}
	
	/*public function mysql_escape_mimic($inp) { 
		if(is_array($inp)) 
			return array_map(__METHOD__, $inp); 

		if(!empty($inp) && is_string($inp)) { 
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
		} 

		return $inp; Fire
	} */

	public function strex($string){
		$string = str_replace("'", '', htmlspecialchars($string));
		$string = str_replace("'", "", $string);
		$string = str_replace("(", '', $string);
		$string = str_replace('"', '', $string);
		$string = str_replace(")", '', $string);
		$string = str_replace("$", '', $string);
		$string = str_replace("<", '', $string);
		$string = str_replace(">", '', $string);
		
		return $string;
    }
	
	public function query($query){
		return $this->db->query($query);
	}

	public function query_result($query){
		return $this->db->query($query)->fetch_object();
	}
	
	public function redirect($url){
		echo '<script type="text/javascript">';
		echo 'window.location.href="'.$url.'";';
		echo '</script>';
	}

	public function all_groups($price = '0'){
		$groups = $this->query("SELECT * FROM `groups` WHERE `price` > ".(int)$price." ORDER BY `price` ASC"); 
		$list = array();
		while($el = mysqli_fetch_assoc($groups)) { 
			if(!is_array($list[$el['category']])) $list[$el['category']] = array(); 
			$list[$el['category']][] = $el; 
		} 
			$form = '<option selected disabled>Выберите товар</option>';
			foreach($list as $cat=>$group){ 
				$form .= '<optgroup label="'.$cat.'">';
					foreach($group as $data){
						if($data['surcharge'] == 1)	$buy = $data['price'] - $price;
						else $buy = $data['price'];
						
						$form .= '<option value="'.$data['id'].'">'.$data['name'].' - '.$buy.' рублей</option>';
					}
				$form .= '</optgroup>';
			}
		return $form;
	}

	public function group($id){
		$query = $this->query_result("SELECT * FROM `groups` WHERE `id` = ".(int)$id." LIMIT 1");
		if($query == null) return false;
		return $query;
	}
	
	public function order($id){
		$query = $this->query_result("SELECT * FROM orders WHERE id = '".intval($id)."' LIMIT 1");
		if($query == null) return false;
		return $query;
	}

	public function surcharge($nick, $type = 'get', $price = ''){
		if($type == "get")
		{
			$replay = $this->query_result("SELECT * FROM `surcharge` WHERE nick = '" . $nick . "' ORDER BY price DESC LIMIT 1");
			if($replay == null) return false;
			return $replay;
		}
		elseif($type == "add") $this->query("INSERT INTO `surcharge`(`nick`, `price`) VALUES ('".$nick."', '".$price."')");
	}
	
	public function promo($promo, $price){
		$promo = $this->query_result("SELECT * FROM promo WHERE name = '".$this->noHTML($promo)."' LIMIT 1");
		if(!$promo) return $price;
		return $price - ($price/100*$promo->disc);
	}
	
	public function buy_price($nick, $group, $promo = '', $type = 'check'){
		if(empty($nick)) return "error|Ник не указан||Ник не указан";
		if(empty($group)) return "error|Купить / Доплатить||Группа не выбрана";
		$group = $this->group($group);
			if(!$group) return "error|Купить / Доплатить||Группа не обнаружена";
			$price = $group->price;
			$surcharge = $this->surcharge($nick);
				if ($group->surcharge == 1) {
					if ($surcharge != NULL) {
						$price = $price - $surcharge->price;
					}
				}
				if(!empty($promo)) $price = $this->promo($promo, $price);
				if ($price > 0) {
					if($type == "check")
					{
						if ($surcharge == NULL || $group->surcharge == 0) {
							$alert = '
										   <div class="alert alert-dismissible alert-info text-center">
											   Вы собираетесь приобрести донат ' . $group->name . ' за ' . $price . ' рублей
										   </div>
										   ';
							return "ok|Купить за " . $price . " рублей|" . $alert;
						} else {
							$alert = '
										   <div class="alert alert-dismissible alert-info text-center">
											   Вы собираетесь доплатить до доната ' . $group->name . ' за ' . $price . ' рублей
										   </div>
										   ';
							return "ok|Доплатить за " . $price . " рублей|" . $alert;
						}
					}
					elseif($type == "buy") $this->buy($nick, $price, $group->id);
					else return false;
				} else {
					$alert = '
									   <div class="alert alert-dismissible alert-danger text-center">
										   У вас уже имеется более высокий донат, выберите другой из списка!
									   </div>
									   ';
					return "error|У вас имеется более высокий донат!|" . $alert."|У вас имеется более высокий донат!";
					}
	}

	public function buy($nick, $price, $group){
		$date = date("Y-m-d");
		$time = date("G:i:s");
		$month = date("n");
		$group = $this->group($group);
		$this->query("INSERT INTO `orders`(`groupid`, `group`, `price`, `nick`, `date`, `time`, `month`) VALUES ('".$group->id."','".$group->name."','".$price."','".$nick."', '".$date."', '".$time."', '".$month."')");
		
			$desc = "Покупка 30-дневной подписки [".$group->name."] - PNS PrognoziNaSport";
			$this->redirect("https://unitpay.ru/pay/{$this->cfg['unitpay']['project_id']}/webmoney?sum={$price}&account={$this->db->insert_id}*{$nick}&desc={$desc}");
	}
	
    private function get_sign($method, array $params){
        $delimiter = '{up}';
        ksort($params);
        unset($params['sign']);
        unset($params['signature']);

        return hash('sha256', $method.$delimiter.join($delimiter, $params).$delimiter.$this->cfg['unitpay']['key']);
    }

	public function payment_action($method, $params){
		if($params['signature'] != $this->get_sign($method, $params)) return $this->payment_replay($params, "Подпись не верна");
		$account = explode("*", $params['account']);
		$data = $this->order($account[0]);
		if(!$data) return $this->payment_replay($params, "Счет не обнаружен");
			if($method == 'check'){
				if($data->status == 1) return $this->payment_replay($params, "Счет уже оплачен");
				if($params['sum'] < $data->price) return $this->payment_replay($params, "Сумма не совпадает");
				return $this->payment_replay($params, "Счет готов к оплате", "success");
			}
			elseif($method == 'pay'){
				$this->query("UPDATE `orders` SET `status` = '1', `profit` = '".$params['profit']."' WHERE `id` = ".(int)$data->id);
				$this->surcharge($data->nick, "add", $data->price);
				$this->payment_rcon($data->id);
				//$this->payment_cart($data->id);
				return $this->payment_replay($params, "Счет успешно оплачен, выдаем донат...", "success");
			}
			else return $this->payment_replay($params, "Метод не поддерживается: ".$method);
	}

	public function payment_cart($order){
		$data = $this->order($order);
		$group = $this->group($data->groupid);
		$arr = array("&lowbar;" => "_", " " => "");
		$nick = strtr($data->nick, $arr);
		$this->query("INSERT INTO `shop_cart` ( `player`, `type`, `item`, `amount` ) VALUES ( '".$nick."', 'permgroup', '".$group->cmd."', '1') ");
	}
	
	public function payment_rcon($order){
		$data = $this->order($order);
		$group = $this->group($data->groupid);
		$arr = array("&lowbar;" => "_", " " => "");
		$nick = strtr($data->nick, $arr);
			require_once($_SERVER['DOCUMENT_ROOT'].'/engine/classes/Rcon.class.php');
						$rcon = new Rcon($this->cfg['rcon']['ip'], $this->cfg['rcon']['port'], $this->cfg['rcon']['pass'], 5);
						foreach (explode(';', $group->cmd_rcon) as $c) {
							$cmd = str_replace(array('[nick]'),array($nick), $c);
								$this->db->query("UPDATE `orders` SET `status` = '1' WHERE `id` = ".(int)$data->id);
								if(@$rcon->connect()){
									$rcon->send_command($cmd);
									$this->rcon_log($nick, "CONNECT: ".$rcon->get_response());
								} else {
									$this->rcon_log($nick, "ERROR: ".$rcon->get_response());
								}
						}
		return $rcon->get_response();
	}
	
	public function rcon_log($login, $cmd){
		$this->query("INSERT INTO `log` (`nick`, `message`) VALUES ('".$login."', '".$cmd."')");
	}
	
	public function payment_replay($params, $message, $type = "error"){
			if($type == "success")
			{
				return json_encode(
					array(
						"jsonrpc" => "2.0",
						"result" => array(
							"message" => $message
						),
						'id' => $params['projectId']
					)
				);
			}
			else
			{
				return json_encode(
					array(
						"jsonrpc" => "2.0",
						"error" => array(
							"code" => -32000,
							"message" => $message
						),
					'id' => $params['projectId']
					)
				);
			}
	}
	
	public function online($type = "online"){
		require_once($_SERVER['DOCUMENT_ROOT'].'/engine/classes/MCQuery.class.php');
		$mon = mcraftQuery_SE($this->cfg['server']['ip']);
			if($type == "record")
			{
				$record = $this->query_result("SELECT * FROM `settings` WHERE `type` = 'record'");
					if($mon['numplayers'] > $record->num)
					{
						$query = $this->query("UPDATE `settings` SET `num` = '".$mon['numplayers']."' WHERE `settings`.`id` = 1");
						return $mon['numplayers'];
					}
					else return $record->num;
			}
			else return $mon['numplayers'];
	}
	
	public function daten($date){
		$time = explode(" ", $date);
		$month = explode("-", $time[0]);
				if($month[1] == 1) $month_t = "января";
			elseif($month[1] == 2) $month_t = "Февраля";
			elseif($month[1] == 3) $month_t = "марта";
			elseif($month[1] == 4) $month_t = "апреля";
			elseif($month[1] == 5) $month_t = "мая";
			elseif($month[1] == 6) $month_t = "июня";
			elseif($month[1] == 7) $month_t = "июля";
			elseif($month[1] == 8) $month_t = "августа";
			elseif($month[1] == 9) $month_t = "сентября";
			elseif($month[1] == 10) $month_t = "октября";
			elseif($month[1] == 11) $month_t = "ноября";
			elseif($month[1] == 12) $month_t = "декабря";
			else return $date;
		$sec = explode(":", $time[1]);
		if($time[0] == date("Y-m-d")) return "Сегодня в ".$sec[0].":".$sec[1];
		elseif($time[0] == date('Y-m-d', strtotime('-1 days'))) return "Вчера в ".$sec[0].":".$sec[1];
		else return $month[2]." ".$month_t." в ".$sec[0].":".$sec[1];
	}
	
	public function repay($id, $nickname){
		$data = $this->order($id);
		$group = $this->group($data->groupid);
		$arr = array("&lowbar;" => "_", " " => "");
		$nick = strtr($data->nick, $arr);
		if($group->surcharge == 1)
		{
				if($data->status == 1)
				{
					$this->payment_rcon($data->id);
					return '
					<div class="alert alert-dismissible alert-warning">
					  <button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>Ответ сервера:</strong> 
						<br>
						- Группа игрока '.$nick.' успешно изменена
					  </div>';
				} else return "Счет не оплачен";
			} else return '
					<div class="alert alert-dismissible alert-danger">
					  <button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>Ответ сервера:</strong> 
							Донат '.$group->name.' перевыдать невозможно!
					  </div>';
	}
	
	public function statistics(){
		$time = date("G:i:s");
		$online = $this->online("online");
		$query = $this->query("INSERT INTO `online`(`online`, `time`) VALUES ('".$online."', '".$time."')");
		return "ok";
	}
}

?>
