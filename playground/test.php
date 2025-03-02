<?php

use Amxm\Gogetlinks\Parser;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';



$configFile = __DIR__.'/config.ini';
if (!is_file($configFile)){
    throw new \Exception("Создайте файл config.ini");
}
$config = parse_ini_file($configFile);


// $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray(
//     [
//         'PHPSESSID' => 'a9d9dc85fc83566d905ba169bd0ce68b',
//         'hash' => 'b59899e24bbb20e8bfe98d79e5983732'
//     ],
//     'gogetlinks.net'
// );

// $cookieJar = null;

$cookieJar = new \GuzzleHttp\Cookie\FileCookieJar(__DIR__.'/cookie.txt', true);

// $cookieJar = new FileCookieJar($cookieFile, TRUE);

$ggl = new \Amxm\Gogetlinks\GogetlinksClient(['debug'=>true,'client_debug'=>false], $cookieJar);

$ggl->login($config['email'], $config['password']);

$balanceInfo = $ggl->getBalance();
dump($balanceInfo);


// $ggl->getSites(file_get_contents('test.html'));

// $sites = $ggl->getSites();
// // dump ($sites);
// dump("Получено сайтов: ".count($sites));

$tasks = $ggl->getTasks();
dump("Получено заданий: ".count($tasks));


dump ($tasks);