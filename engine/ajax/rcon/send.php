<?php
if($Admin->is_auth())
{
	$sendcmd = $Admin->engine->strex($_REQUEST['cmd']);
	$cmd = $Admin->cmd($sendcmd);
	$user = $Admin->user($_SESSION['user_id']);
	if($cmd) 
	{
		require_once($_SERVER['DOCUMENT_ROOT'].'/engine/classes/Rcon.class.php');
		$rcon = new Rcon($Admin->engine->cfg['rcon']['ip'], $Admin->engine->cfg['rcon']['port'], $Admin->engine->cfg['rcon']['pass'], 5);
		if(@$rcon->connect()){
			$rcon->send_command($cmd);
			   $response = $Admin->minetext($rcon->get_response());
		} else $response = $Admin->minetext($rcon->get_response());
		$Admin->engine->query("INSERT INTO `console_log`(`user`, `date`, `time`, `cmd`, `reply`) VALUES ('{$user->name} {$user->surname}', '".date("Y-m-d")."', '".date("H:i:s")."', '{$sendcmd}', '{$response}	')");
			echo '<br><div class="alert alert-dismissible alert-success">
				  <button type="button" class="close" data-dismiss="alert">&times;</button>
				  <strong>Успешно!</strong> Команда была отправлена на сервер!
				</div>';
	} else echo '<br><div class="alert alert-dismissible alert-danger">
				  <button type="button" class="close" data-dismiss="alert">&times;</button>
				  <strong>Ошибка!</strong> Вы пытаетесь отправить неизвестную команду или она неправильно написана, а так же возможно у вас превышен лимит за сутки!
				</div>';
} else die("404");