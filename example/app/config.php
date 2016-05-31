<?php
return [
	'website'=>[
		'root' 	=> 'E:\web\mvc\example\root',
		'cache'	=> 'E:\web\mvc\example\app\cache',
		'logs'	=> 'E:\web\mvc\example\app\logs',
	],
	'database'=> [
		'dsn'		=> "mysql:host=192.168.0.103;dbname=catalog;port=3306;charset=utf8",
		'username'	=> "root",
		'password'	=> "",
		'options' 	=> [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
			PDO::ATTR_EMULATE_PREPARES => false
		]
	],
];
?>
