<?php
/**
 * Иногда для авторизации требуется каптча (как правило для авторизации с серверных ip или прокси),
 * можно использовать любой сервис, здесь пример с rucaptcha.com
 * Чтобы постоянно при авторизации не разгадывать капчу, лучше хранить сессии с помощью FileCookieJar
 * 
 */
use Amxm\Gogetlinks\GogetlinksClient;

require __DIR__.'/_init.php';

// $cookieJar = new \GuzzleHttp\Cookie\FileCookieJar(__DIR__.'/cookie-' . preg_replace('#[^a-z]#i','_',$config['email']). '.txt', true);
$cookieJar = null;

$ggl = new GogetlinksClient(['debug'=>true,'client_debug'=>false], $cookieJar);

//  Сразу задем callback который вызвется в методе login, если требуется решить капчу
$ggl->setCaptchaSolver(function($captchaInfo, $html) use ($config){

    dump("Надо решить капчу");

    $solver = new \TwoCaptcha\TwoCaptcha($config['rucaptcha_key']);
    try {
        $result = $solver->recaptcha([
            'sitekey' => $captchaInfo['data-sitekey'],
            'url'     => 'https://gogetlinks.net/user/signIn',
        ]);
        dump('результат капчи: ',$result->code);
    } catch (\Exception $e) {
        die($e->getMessage());
    }

    return ['g-recaptcha-response' => $result->code];
});

// Авторизовываемся
$ggl->login($config['email'], $config['password']);

$balanceInfo = $ggl->getBalance();
dump($balanceInfo);

$tasks = $ggl->getTasks('NEW');
dump("Получено заданий: ".count($tasks));

dump ($tasks);