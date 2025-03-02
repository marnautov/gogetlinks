<?php

use Amxm\Gogetlinks\Parser;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';



$html = '	</div><div class="js-web-task__content" id="div_table_content">
	<div class="table__before">
	<div class="tabs tabs_table">
																			<a href="/webTask/index/action/viewWait" class="tabs__item tabs__item_active">
					<span>Ожидают проверки</span>
					<span class="tabs__count">4</span>
				</a>
																	<a href="/webTask/index/action/viewWaitIndexation" class="tabs__item ">
					<span>Ожидают индексации</span>
					<span class="tabs__count">1</span>
				</a>
												<a href="/webTask/index/action/viewPaid" class="tabs__item ">
					<span>Оплаченные</span>
					<span class="tabs__count">1 645/61</span>
				</a>
						</div>

	<div class="table__counter">
		<div>на&nbsp;странице</div>
		<select class="input-control input-control_small js-web-task__rows-in-table" name="count_in_page">
							<option value="5" >5</option>
							<option value="10" >10</option>
							<option value="20" selected>20</option>
							<option value="50" >50</option>
					</select>
	</div>';

Parser::parseTaskTabs($html);


dd('end');





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

// $ggl->getSites(file_get_contents('test.html'));

// $sites = $ggl->getSites();
// // dump ($sites);
// dump("Получено сайтов: ".count($sites));

$tasks = $ggl->getTasks();
dump("Получено заданий: ".count($tasks));


dump ($tasks);