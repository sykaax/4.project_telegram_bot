<?php
	public function checkAuth($login, $password) {
		$query_string = $this->query("SELECT * FROM `authme` WHERE `username` LIKE '{$login}'");
		if($query_string == true) {
			$salt = substr($query_string->password, 5, 16);
			$mask = '\$SHA$'.$salt.'$'.hash('SHA256', hash('SHA256',$password).$salt);
			if($query->password == $mask) {
				$query_insert = $this->engine->query("UPDATE `users` SET `nick` = '{$login}' WHERE `secret` = '{$user->secret}' ");
				return true;
			} else {
				$this->engine->redirect('/lk/?page=index');
			}
		}
	}
?>