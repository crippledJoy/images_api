<?php

$mode = 'Production';//or Development
$responseType = 'JSON';
$paginationLimit = 100;//Must be positive number
$storageDir = 'Storage/';
$csvSeparator = '|';

//Dbase credentials
$user = '';
$pass = '';
$dbName = '';

spl_autoload_register(function ($class) {
	$file = str_replace('\\', '/', $class) . '.php';
	include_once __DIR__.'/'.$file;
});