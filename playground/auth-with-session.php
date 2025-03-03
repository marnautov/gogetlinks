<?php
/**
 * Можно авторизоваться просто по сессии, например из вашего браузера, PHPSESSID можно найти в куках
 * 
 */
use Amxm\Gogetlinks\GogetlinksClient;

require __DIR__.'/_init.php';

// авторизация по сессии
if ($config['PHPSESSID']){
    $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray(
        [
            'PHPSESSID' => $config['PHPSESSID'],
        ],
        'gogetlinks.net'
    );
}

$ggl = new GogetlinksClient(['debug'=>true,'client_debug'=>false], $cookieJar);


// Авторизовываемся
//$ggl->login($config['email'], $config['password']);

$sites = $ggl->getSites();
dump($sites);

$balanceInfo = $ggl->getBalance();
dump($balanceInfo);

$tasks = $ggl->getTasks('NEW');
dump("Получено заданий: ".count($tasks));

dump ($tasks);