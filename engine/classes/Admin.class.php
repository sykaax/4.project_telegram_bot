<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'].'/engine/classes/Main.class.php';
class Admin{

	public function __construct(){
        $this->engine = new Engine();
		$this->db = new mysqli('213.32.6.76', 'hypemine', 'YfiGh03rnybR0glfnjg{fqg', 'hypemine');
		if($this->db->connect_error){
			die("Couldn't connect to MySQLi: ".$this->db->connect_error);
		}
		if (!$this->db->set_charset("utf8")) {
			die("Ошибка при загрузке набора символов utf8: ".$this->db->error);
		}
	}

	public function donate($date){
		$query = $this->engine->query("SELECT * FROM orders WHERE date = '".$date."' AND status = 1");
		if($query == null) return 0;
			$price = 0;
				while($q = $query->fetch_object()){
					$price = $price + $q->profit;
				}
		return $price;
	}

	public function query($query) {
		return $this->db->query($query);
	}

	public function query_result($query) {
		return $this->db->query($query)->fetch_object();
	}

	public function query_num_rows($query) {
		return $this->db->query($query)->num_rows;
	}
	public function query2($query) {
		return $this->db->query($query);
	}

	public function query_result2($query) {
		return $this->db->query($query)->fetch_object();
	}

	public function query_num_rows2($query) {
		return $this->db->query($query)->num_rows;
	}

	public function percent($user = '0', $time = 'all'){
		if($user == 1) $percent = 25;
		elseif($user == 2) $percent = 25;
		elseif($user == 3) $percent = 50;
		else $percent = 100;

		if($time != "all") $query = $this->engine->query("SELECT * FROM orders WHERE status = 1 AND month = ".$time);
		else $query = $this->engine->query("SELECT * FROM orders WHERE status = 1");

		if($query == null) return 0;

			$price = 0;
				while($q = $query->fetch_object()){
					$price = $price + $q->profit;
				}

		return $price / 100 * $percent;
	}

	public function authme($query) {
		$db = mysql_connect('213.32.6.76', 'hypemine', 'YfiGh03rnybR0glfnjg{fqg');
			mysql_select_db("magic", $db);
		return mysql_query($query, $db);
	}
	public function moder($query_db) {
		$db_admin = new mysqli('localhost', 'wispworld_moder', 'fkavifw823iJHMND', 'wispworld_moder');
		if($db_admin->connect_error){
			die("Couldn't connect to MySQLi: ".$db_admin->connect_error);
		}
		return $db_admin->query($query_db);
	}

	public function promo($promo, $price){
		$promo = $this->query_result("SELECT * FROM promo WHERE name = '".$this->noHTML($promo)."' LIMIT 1");
		if(!$promo) return $price;
		return $price - ($price/100*$promo->disc);
	}

	public function orders(){
		$today = date("Y-m-d");
		$query = $this->engine->query("SELECT * FROM orders WHERE date = '".$today."'");
		if($query == null) return 0;
		return $query->num_rows;
	}

	public function players(){
		$today = date("Y-m-d");
		$query = $this->engine->query("SELECT * FROM orders WHERE date = '".$today."' AND status = 1");
		if($query == null) return 0;
		return $query->num_rows;
	}

	public function today(){
		$today = date("Y-m-d");
		$query = $this->engine->query("SELECT * FROM orders WHERE date = '".$today."' AND status = 1");
		if($query == null) return 0;
			$price = 0;
				while($q = $query->fetch_object()){
					$price = $price + $q->profit;
				}
		return $price;
	}

	public function generateCode($length=6){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;
		while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,$clen)];
		}
		return $code;
	}
	public function min_generateCode($length=6){
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$code = "";
		$clen = strlen($chars) - 1;
		while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,$clen)];
		}
		return $code;
	}

	public function user($id){
		$query = $this->engine->query_result("SELECT * FROM users WHERE id = '".intval($id)."' LIMIT 1");
		if($query == null) return false;
		return $query;
	}

	public function is_auth(){
		if(!isset($_SESSION['user_id'])) return false;
		$query = $this->engine->query_result("SELECT * FROM users WHERE id = '".intval($_SESSION['user_id'])."' LIMIT 1");
		if($query == null) return false;
		if(($query->id == $_SESSION['user_id']) AND ($query->hash == $_SESSION['user_hash'])){
	//		if($authme->username == $_SESSION['username']) AND ($authme->password == $_SESSION['hash_pass']) {
				return true;
	//		}
		}else{
			return false;
		}
	}

	public function getperm($page){
		$user = $this->user($_SESSION['user_id']);
		if(!isset($user)) return false;
			if($user->console_type == '99') {
				return true;
			} else {
				if($user->console_type != '-1') {
					if($page == 'index' || $page == 'signup' || $page == 'changepass') {
						return true;
					}
				}
					if($this->is_moder($user->secret) == true) {
						if($page == "moders") {
							return true;
						}
					}
					if($user->logs == "+") {
						if($page == "ssh") {
							return true;
						}
					}
				}
	}

	public function hash_authme($_password, $_name) {
		$query_salt = $this->authme("SELECT * FROM `authme` WHERE `username` LIKE '{$_name}'");
		if(mysql_num_rows($query_salt) == 0) {
			return false;
		}
			$salt_result = mysql_fetch_object($query_salt);
			$salt = substr($salt_result->password, 5, 16);
		return $print_r("\$SHA$".$salt."$".hash('sha256', hash('sha256', $_password).$salt));
	}

	public function error($msg){
		return $print_r('<br><div class="alert alert-error">'.$msg.'</div>');
	}


	public function login_authme($_nick, $pass) {
		if(empty($_nick) || !isset($_nick)) {
			$this->error('Введите никнейм');
		}
		$query_authme = $this->authme("SELECT * FROM `authme` WHERE `username` LIKE '{$_nick}'");
		if(mysql_num_rows($query_authme) == 0) {
			$this->error("Введенный никнейм не зарегестрирован");
			return false;
		} elseif(mysql_num_rows($query_authme) == 1) {
		$authme = mysql_fetch_object($query_authme);
		$salt = substr($authme->password, 5, 16);
		$password = "\$SHA$".$salt."$".hash('sha256', hash('sha256', $pass).$salt);
			if($password == $authme->password) {
				return true;
			}
		}
	}

	public function check_ban($check_nick) {
		$query_ban = $this->authme("SELECT * FROM `MilitaryBans` WHERE `player` LIKE '{$check_nick}' LIMIT 1");
			$ban = mysql_fetch_object($query_ban);
		if(mysql_num_rows($query_ban) == 0) {
			return false;
		}
		if(mysql_num_rows($query_ban) == 1) {
			echo '<p class="alert alert-error">Вы были забанены администратором '.$ban->owner.' по причине "'.$ban->reason.'"<br>
			<a class="btn btn-success" href="/lk/?page=settings">Купить разбан [50 рублей]</a></p>';
			return true;
		}
	}

	public function getstars($page){
		$user = $this->user($_SESSION['user_id']);
		if(!isset($user)) return false;
			if($user->stars == "+"){
				return true;
			} else {
				foreach (explode(',', $user->stars) as $p) {
					if($p == $page)
					{
						return true;
					}
				}
			}
	}

		public function getfull($page){
		$user = $this->user($_SESSION['user_id']);
		if(!isset($user)) return false;
			if($user->full == "+"){
				return true;
			} else {
				foreach (explode(',', $user->full) as $p) {
					if($p == $page)
					{
						return true;
					}
				}
			}
	}
	public function check_password($_pass, $_username) {
		$password_query = $Admin->authme("SELECT * FROM `authme` WHERE `username` = '".$_username."'");
		$password_result = mysql_fetch_object($password_query);
		$password = $password_result->password;
		$salt = substr($password, 5, 16);
		$hashed_pass = "\$SHA$".$salt."$".hash('sha256', hash('sha256', $_pass).$salt);
		if($hashed_pass == $password) {
			return true;
		} else return false;
	}

	public function cmd($cmd){
		$cmd = mb_strtolower($cmd);
		$user = $this->user($_SESSION['user_id']);
		$nick = $user->nick;
		$arr = array("/" => "");
		$arr = array("+/" => "/");
		$cmd = strtr($cmd, $arr);
		$scmd = explode(" ", $cmd);
		$doctype = $user->stars;
			if(substr($scmd[1], 0, 1) == '*') {
				return;
			}
		if($user->full == "*") {
			$deniedWords = 'sudo,stop,reload,plugman,worldguard,etempban,op,restart';
			$denied = explode(',',$deniedWords);
			foreach($denied as $not) {
				if($scmd[0] == $not) {
					if($scmd[0] == 'sud'){
						if($scmd[1] == $user->nick) {
							if($scmd[2] == '/limit') {
								if($user->secret == '143739802'){
									$rcon = $cmd;
								}
							}
						}
					} else {
						return;
					}
				}
			}
			if($scmd[0] != 'pex') {
				$rcon = $cmd;
			} elseif($scmd[0] == "pex") {
				if($scmd[1] == "user") {
					if($scmd[3] == "group") {
						if(($user->secret == '340973478' && $user->nick == '_GG_69') OR ($user->secret == '143739802')) {
							$rcon = $cmd;
						} elseif($this->get_timeout('permissions','10')) {
								$rcon = $cmd;
								$this->add_timeout('permissions');
							}
						}
					}
				}
			}
		$valid = array();
		if($scmd[0] != "case" && $scmd[0] != "broadcast") {
			$search_db = $this->engine->query_result("SELECT * FROM `commands` WHERE `scmd` = '{$scmd[0]}'");
			$dbCmd = $search_db->cmd;
			$dbScmd = explode(" ",$dbCmd);
				if($doctype == "youtube") $cnt = $search_db->youtube;
				if($doctype == "helper") $cnt = $search_db->helper;
				if($doctype == "star") $cnt = $search_db->star;
				if($doctype == "zvezda") $cnt = $search_db->zvezda;
				if($doctype == "zvezda" && $user->full == "+") $cnt = $search_db->full;
			if($search_db->length == "1") {
				$sendCmd =  "$scmd[0]";
			}
			else {
				$valid[0] = "$scmd[0]";
				$i = 0;
				$x = 1;
				foreach($dbScmd as $check) {
					if($scmd[$i] == $check) {
						$valid[$i] = "$scmd[$i]";
					} else {
						if($check == "[scmd]") {
							if(!empty($scmd[$i])) {
								$valid[$i] = "$scmd[$i]";
							} else return false;
						}
						if($check == "[nick]") {
							if($scmd[$i] != "$user->nick") {
								$valid[$i] = "$scmd[$i]";
							} else return false;
						}
						if($check == "[me]") {
							if(strtolower($scmd[$i]) == strtolower($user->nick)) {
								$valid[$i] = $scmd[$i];
							} else return false;
						}
					}
					$i = $i + 1;
					$x = $x + 1;
				}
				$sendCmd = implode(" ", $valid);
			}
		}
		else{
			if($scmd[0] == "case") {
				if($scmd[1] == "add") {
					if(strtolower($scmd[2]) == strtolower($user->nick)) {
				$search_db = $this->engine->query_result("SELECT * FROM `commands` WHERE `timeout` = 'caseme'");
				$sendCmd = "case add $user->nick";
					if($doctype == "youtube") $cnt = $search_db->youtube;
					if($doctype == "helper") $cnt = $search_db->helper;
					if($doctype == "star") $cnt = $search_db->star;
					if($doctype == "zvezda") $cnt = $search_db->zvezda;
					if($doctype == "zvezda" && $user->full == "+") $cnt = $search_db->full;
					} elseif(strtolower($scmd[2]) != strtolower($user->nick)) {
						$search_db = $this->engine->query_result("SELECT * FROM `commands` WHERE `timeout` = 'case'");
						$sendCmd = "case add $scmd[2]";
							if($doctype == "youtube") $cnt = $search_db->youtube;
							if($doctype == "helper") $cnt = $search_db->helper;
							if($doctype == "star") $cnt = $search_db->star;
							if($doctype == "zvezda") $cnt = $search_db->zvezda;
							if($doctype == "zvezda" && $user->full == "+") $cnt = $search_db->full;
					}
				}
			}
			if($scmd[0] == "broadcast") {
				$search_db = $this->engine->query_result("SELECT * FROM `commands` WHERE `scmd` = 'broadcast'");
				$sendCmd = $cmd;
				if($doctype == "youtube") $cnt = $search_db->youtube;
			if($doctype == "helper") $cnt = $search_db->helper;
			if($doctype == "star") $cnt = $search_db->star;
			if($doctype == "zvezda") $cnt = $search_db->zvezda;
			if($doctype == "zvezda" && $user->full == "+") $cnt = $search_db->full;
			}
		}
		if($sendCmd) {
			if($cnt != '0') {
				if($cnt == '*') {
					$rcon = $sendCmd;
				} else {
					if($this->get_timeout($search_db->timeout, $cnt)) {
						$rcon = $sendCmd;
						$this->add_timeout($search_db->timeout);
					} elseif($user->perm == "*") {
						$rcon = $cmd;
					}
				}
			}
	}
		if(empty($rcon)) return false;
		else return $rcon;
	}


	public function add_timeout($command){
		$user = $this->user($_SESSION['user_id']);
		if(!$user) return false;
		$this->engine->query("INSERT INTO `console_timeout`(`date`, `command`, `user`) VALUES (NOW()+INTERVAL 1 DAY, '".$command."', '".$user->id."')");
	}

	public function add_timeout_star($command){
		$user = $this->user($_SESSION['user_id']);
		if(!$user) return false;
		$this->engine->query("INSERT INTO `console_timeout`(`date`, `command`, `user`) VALUES (NOW()+INTERVAL 7 DAYS, '".$command."', '".$user->id."')");
	}

	public function get_timeout($command, $limit){
		$user = $this->user($_SESSION['user_id']);
		if(!$user) return false;
		$query = $this->engine->query("SELECT * FROM `console_timeout` WHERE `date` > NOW() AND `command` = '{$command}' AND `user` = '{$user->id}'");
		if($query->num_rows < $limit) return true;
		else return false;
	}


	public function uuid($_nickname) {
		$uuid = $this->query_result("SELECT * FROM `pex_surv` WHERE `value` LIKE '{$_nickname}' AND `type` = '1'");
		if($uuid == null) return false;
		$uuid = $uuid->name;
		return $uuid;
	}

	public function is_moder($secret) {
		$query_mod = $this->moder("SELECT * FROM `moders` WHERE `secret` = '{$secret}'");
		$query_result = $query_mod->fetch_object();
		$moder_true = $query_result->perm;
		$donat_true = $query_result->donat;
		if($moder_true == '+' || $donat_true == '+') {
			return true;
		} elseif($moder_true == '') {
			return false;
		}
	}

	public function pex($_nickname) {
		$uuid1 = $this->query_result("SELECT * FROM `pex_surv` WHERE `value` LIKE '".$_nickname."' AND `type` = '1'");
		if($uuid1 == null) return false;
		$uuid = $uuid1->name;
			$pex = $this->query_result("SELECT * FROM `pex_inheritance` WHERE `child` = '".$uuid."' AND `type` = '1'");
			if($pex == null) return false;
			$pex = $pex->parent;
			return $pex;
	}
	public function pex2($_nickname) {
		$uuid1 = $this->query_result2("SELECT * FROM `pex_surv` WHERE `value` LIKE '".$_nickname."' AND `type` = '1'");
		if($uuid1 == null) return false;
		$uuid = $uuid1->name;
			$pex = $this->query_result2("SELECT * FROM `pex_inheritance` WHERE `child` = '".$uuid."' AND `type` = '1'");
			if($pex == null) return false;
			$pex = $pex->parent;
			return $pex;
	}

	public function group($_nickname) {
		$uuid = $this->query_result("SELECT * FROM `pex_surv` WHERE `value` LIKE '{$_nickname}' AND `type` = '1'");
		if($uuid == null) return false;
		$uuid = $uuid->name;
		if($uuid) {
			$pex = $this->query_result("SELECT * FROM `pex_inheritance` WHERE `child` = '{$uuid}' AND `type` = '1'");
				if($pex == null) return false;
			$pex = $pex->parent;
			if($pex == 'antigrief') {
				$pex = 'agplus';
			}
			if($pex == 'god') {
				$pex = 'admin';
			}
		return $pex;
		}
	}

	public function group2($_nickname) {
		$uuid = $this->query_result2("SELECT * FROM `pex_surv` WHERE `value` LIKE '{$_nickname}' AND `type` = '1'");
		if($uuid == null) return false;
		$uuid = $uuid->name;
		if($uuid) {
			$pex = $this->query_result2("SELECT * FROM `pex_inheritance` WHERE `child` = '{$uuid}' AND `type` = '1'");
				if($pex == null) return false;
			$pex = $pex->parent;
			if($pex == 'antigrief') {
				$pex = 'agplus';
			}
			if($pex == 'god') {
				$pex = 'admin';
			}
		return $pex;
		}
	}

	public function addPerm($id, $perm='', $stars='', $full='') {
							$this->engine->query("UPDATE `users` SET `perm` = '".$perm."' WHERE `id` = '".$id."'");
							$this->engine->query("UPDATE `users` SET `stars` = '".$stars."' WHERE `id` = '".$id."'");
							$this->engine->query("UPDATE `users` SET `full` = '".$full."' WHERE `id` = '".$id."'");
							if($user->perm == $perm) {
								if($user->stars == $stars) {
									if($user->full == $full) {
										return true;
									}
								}
							}
						}

	public function clan($_nickname) {
		$clan = $this->query_result("SELECT * FROM `clan_members` WHERE `name` = '{$_nickname}' LIMIT 1");
		if($clan == null) return false;
		$clan = $this->minetext($clan->clan);
		return $clan;
	}
	public function clan2($_nickname) {
		$clan = $this->query_result2("SELECT * FROM `clan_members` WHERE `name` = '{$_nickname}' LIMIT 1");
		if($clan == null) return false;
		$clan = $this->minetext($clan->clan);
		return $clan;
	}

	public function prefix($_nickname) {
				            $this->authme("SET NAMES 'utf8'");
				            $this->authme("SET CHARACTER SET 'utf8'");
		$uuid = $this->query_result("SELECT * FROM `pex_surv` WHERE `value` LIKE '{$_nickname}' AND `type` = '1'");
		if($uuid == null) return false;
		$uuid = $uuid->name;
				            $this->authme("SET NAMES 'utf8'");
				            $this->authme("SET CHARACTER SET 'utf8'");
		$result = $this->query_result("SELECT * FROM `pex_surv` WHERE `name` = '{$uuid}' AND `permission` = 'prefix'");
		if($result == null) {
			if($uuid) {
				$pex = $this->query_result("SELECT * FROM `pex_inheritance` WHERE `child` = '{$uuid}' AND `type` = '1'");
				if($pex == null) return false;
				$pex = $pex->parent;
				$result = $this->query_result("SELECT * FROM `pex_surv` WHERE `name` = '{$pex}' AND `permission` = 'prefix' AND `type` = '0'");
			}
		}
		$prefix = $result->value;
		return $prefix;
	}


	public function minetext($minetext) {
		preg_match_all("/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/", $minetext, $brokenupstrings);
		$returnstring = "";
		foreach ($brokenupstrings as $results) {
			$ending = '';
			foreach ($results as $individual) {
				$code = preg_split("/[&§][0-9a-z]/", $individual);
				preg_match("/[&§][0-9a-z]/", $individual, $prefix);
				if (isset($prefix[0])) {
					$actualcode = substr($prefix[0], 1);
					switch ($actualcode) {
						case "1":
							$returnstring = $returnstring . '<FONT COLOR="0000AA">';
							$ending       = $ending . "</FONT>";
							break;
						case "2":
							$returnstring = $returnstring . '<FONT COLOR="00AA00">';
							$ending       = $ending . "</FONT>";
							break;
						case "3":
							$returnstring = $returnstring . '<FONT COLOR="00AAAA">';
							$ending       = $ending . "</FONT>";
							break;
						case "4":
							$returnstring = $returnstring . '<FONT COLOR="AA0000">';
							$ending       = $ending . "</FONT>";
							break;
						case "5":
							$returnstring = $returnstring . '<FONT COLOR="AA00AA">';
							$ending       = $ending . "</FONT>";
							break;
						case "6":
							$returnstring = $returnstring . '<FONT COLOR="FFAA00">';
							$ending       = $ending . "</FONT>";
							break;
						case "7":
							$returnstring = $returnstring . '<FONT COLOR="AAAAAA">';
							$ending       = $ending . "</FONT>";
							break;
						case "8":
							$returnstring = $returnstring . '<FONT COLOR="555555">';
							$ending       = $ending . "</FONT>";
							break;
						case "9":
							$returnstring = $returnstring . '<FONT COLOR="5555FF">';
							$ending       = $ending . "</FONT>";
							break;
						case "a":
							$returnstring = $returnstring . '<FONT COLOR="55FF55">';
							$ending       = $ending . "</FONT>";
							break;
						case "b":
							$returnstring = $returnstring . '<FONT COLOR="55FFFF">';
							$ending       = $ending . "</FONT>";
							break;
						case "c":
							$returnstring = $returnstring . '<FONT COLOR="FF5555">';
							$ending       = $ending . "</FONT>";
							break;
						case "d":
							$returnstring = $returnstring . '<FONT COLOR="FF55FF">';
							$ending       = $ending . "</FONT>";
							break;
						case "e":
							$returnstring = $returnstring . '<FONT COLOR="FFFF55">';
							$ending       = $ending . "</FONT>";
							break;
						case "f":
							$returnstring = $returnstring . '<FONT COLOR="FFFFFF">';
							$ending       = $ending . "</FONT>";
							break;
						case "l":
							if (strlen($individual) > 2) {
								$returnstring = $returnstring . '<span style="font-weight:bold;">';
								$ending       = "</span>" . $ending;
								break;
							}
						case "m":
							if (strlen($individual) > 2) {
								$returnstring = $returnstring . '<strike>';
								$ending       = "</strike>" . $ending;
								break;
							}
						case "n":
							if (strlen($individual) > 2) {
								$returnstring = $returnstring . '<span style="text-decoration: underline;">';
								$ending       = "</span>" . $ending;
								break;
							}
						case "o":
							if (strlen($individual) > 2) {
								$returnstring = $returnstring . '<i>';
								$ending       = "</i>" . $ending;
								break;
							}
						case "r":
							$returnstring = $returnstring . $ending;
							$ending       = '';
							break;
					}
					if (isset($code[1])) {
						$returnstring = $returnstring . $code[1];
						if (isset($ending) && strlen($individual) > 2) {
							$returnstring = $returnstring . $ending;
							$ending       = '';
						}
					}
				} else {
					$returnstring = $returnstring . $individual;
				}

			}
		}

		return $returnstring;
	}
}
?>
