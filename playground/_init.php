<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';


$configFile = __DIR__.'/config.ini';
if (!is_file($configFile)){
    throw new \Exception("Создайте файл config.ini");
}
$config = parse_ini_file($configFile);
