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

// авторизация по сессии
if ($config['PHPSESSID']){
    $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray(
        [
            'PHPSESSID' => $config['PHPSESSID'],
            'hash' => $config['hash']
        ],
        'gogetlinks.net'
    );
}

// $cookieJar = null;

// $cookieJar = new \GuzzleHttp\Cookie\FileCookieJar(__DIR__.'/cookie-' . preg_replace('#[^a-z]#i','_',$config['email']). '.txt', true);

// $cookieJar = new FileCookieJar($cookieFile, TRUE);

$ggl = new \Amxm\Gogetlinks\GogetlinksClient(['debug'=>true,'client_debug'=>false], $cookieJar);

$ggl->login($config['email'], $config['password']);

$balanceInfo = $ggl->getBalance();
dump($balanceInfo);


// $ggl->getSites(file_get_contents('test.html'));

// $sites = $ggl->getSites();
// // dump ($sites);
// dump("Получено сайтов: ".count($sites));

$tasks = $ggl->getTasks('NEW');
dump("Получено заданий: ".count($tasks));


dump ($tasks);