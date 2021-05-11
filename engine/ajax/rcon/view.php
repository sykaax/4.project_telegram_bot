<?php
if($Admin->is_auth())
{
	$log = $Admin->engine->query("SELECT * FROM `console_log` ORDER BY id DESC LIMIT 15");
	while($l = $log->fetch_object()) echo "<li class=\"list-group-item list-group-item-info\">{$l->user} ({$l->date}) [{$l->time}]<br>Команда: <code>{$l->cmd}</code><br>Ответ: <em>{$l->reply}</em></li>";
	?>
		<li class="list-group-item list-group-item-success">Готово к использованию.</li>
		<li class="list-group-item list-group-item-success">Подключились к серверу.</li>
		<li class="list-group-item list-group-item-warning">Консоль запущена.</li>
	<?php
} else die("404");