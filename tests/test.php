<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';


$configFile = __DIR__.'/config.ini';
if (!is_file($configFile)){
    throw new \Exception("Создайте файл config.ini");
}
$config = parse_ini_file($configFile);



$ggl = new \Amxm\Gogetlinks\Parser();
$ggl->signIn($config['email'], $config['password']);


// $ggl->getSites(file_get_contents('test.html'));


$sites = $ggl->getSites();

$tasks = $ggl->getTasks();


dump ($sites);
dump ($tasks);