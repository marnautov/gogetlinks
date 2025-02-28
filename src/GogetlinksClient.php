<?php

namespace Amxm\Gogetlinks;

use \GuzzleHttp\ClientInterface;
use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

class GogetlinksClient implements GogetlinksInterface {

    private ClientInterface $client;

    function __construct($config = array(), CookieJar $cookieJar = null)
    {

        $this->client = new Client([
            'cookies' => $cookieJar ?? true, // You can set cookies to true in a client constructor if you would like to use a shared cookie jar for all requests.
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36',
                // 'Accept'    => 'application/json, text/javascript, */*; q=0.01',
            ],
            'debug' =>  $config['debug']??false,
        ]);
    }


    /**
     * Установить client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }


    private function getUserId ()
    {

//        dd($html);
//
//        preg_match('#userId:\s*([0-9]+),#is', $html, $m);
//        dump("Авторизовались, userId: ");
//        dump($m);

    }


    /**
     * Авторизация
     */
    public function login(string $email, string $password)
    {
        $url = 'https://gogetlinks.net/user/signIn';

        $postData = [
            'e_mail' => $email,
            'password' => $password,
            'remember' =>   'on',
            'is_ajax' => 'true'
        ];
        
        $response = $this->client->request('POST', $url, ['form_params' => $postData]);

//        var_dump($response->getStatusCode());

        $html = (string)$response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");

        return true;

    }


    /**
     * Список сайтов вебмастера
     */
    public function getSites(): array
    {

        $response = $this->client->get('https://gogetlinks.net/mySites');
        if ($response->getStatusCode() !== 200){
            throw new \Exception("Ошибка, статус страницы вернул код: ".$response->getStatusCode());
        }

        $html = (string)$response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");

        $sites = [];

        preg_match_all('#<tr class="siteRow".+?</tr>#is', $html, $matches);
        foreach($matches[0] as $block) {

            $site = [];


 // основные данные

    // домен
    preg_match('#<div class="site-link__info">([^<>]+)<#is', $block, $m);
    $site['domain'] = trim($m[1]);

    // id сайта в системе
    preg_match('#/editSiteInfo/index/site_id/([0-9]+)#is', $block, $m);
    $site['site_id'] = intval($m[1]);


    if (!$site['domain'] || !$site['site_id']){
        // пока не буду кидать exception
        continue;
    }


    // информационные данные

    // статус (видимость) сайта
    preg_match('#<td\s+data\-th="Видимость"[^>]*>(.+?)</td>#uis', $block, $m);
    $status = null;
    if (preg_match('#Доступен#uis', $m[1])){
        $status = 'AVAILABLE';
    } elseif (preg_match('#Скрыт#uis', $m[1])){
        $status = 'HIDDEN';
    } elseif (preg_match('#Отклон[её]н#uis', $m[1])){
        $status = 'REJECTED';
    } elseif (preg_match('#Не активен#uis', $m[1])){
        $status = 'INACTIVE';
    }
    $site['status'] = $status;
    $site['status_text'] = trim(strip_tags($m[0]));

    // яндекс ИКС
    preg_match('#<td\s+data\-th="ИКС".+?<div[^>]*?>\s*([0-9]+)#uis', $block, $m);
    if ($m[1] && is_numeric($m[1])) $site['yandex_iks'] = intval($m[1]);

    // TF/CF
    preg_match('#<td\s+data\-th="TF/CF".+?</td>#uis', $block, $m);
    if ($m[0]){
        $sText = trim(strip_tags($m[0]));
        if ($sText && is_numeric($sText)) $site['tc_cf'] = intval($sText);
    }

   // PR/CY
   preg_match('#<td\s+data\-th="PR.CY".+?</td>#uis', $block, $m);
   if ($m[0]){
       $sText = trim(strip_tags($m[0]));
       if ($sText && is_numeric($sText)) $site['pr_cy'] = intval($sText);
   }

   // Трафик
   preg_match('#<td\s+data\-th="Трафик".+?<span[^>]*?>\s*([0-9]+)\s*</span>#uis', $block, $m);
   if ($m[1] && is_numeric($m[1])) $site['traffic'] = intval($m[1]);

    // Траст
    preg_match('#<td\s+data\-th="Траст".+?</td>#uis', $block, $m);
    if ($m[0]){
        $sText = trim(strip_tags($m[0]));
        if ($sText && is_numeric($sText)) $site['trust'] = intval($sText);
    }

   
   // Скорость размещения
    preg_match('#<td data-th="Скорость размещения"[^<]+<div[^<]+(<div.+?)</#uis', $block, $m);
    $sText = trim(strip_tags($m[1]));
    if (preg_match('#(дней|день)#uis',$sText)){
        $sText = floatval($sText);
    } else {
        $sText = null;
    }
    $site['posting_speed'] = $sText;

            $sites[] = $site;

        }

        return $sites;

    }


    /**
     * Список заданий вембастера
     */
    public function getTasks(): array
    {

        $response = $this->client->get('https://gogetlinks.net/webTask');
        if ($response->getStatusCode() !== 200){
            throw new \Exception("Ошибка, статус страницы вернул код: ".$response->getStatusCode());
        }

        $html = (string)$response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");

        $tasks = [];

        preg_match_all('#<tr\s+id="col_row_.+?</tr>#is', $html, $matches);
        foreach($matches[0] as $block) {

            $task = [];

            preg_match('#col_row_([0-9]+)#is', $block, $m);
            $task['id'] = intval($m[1]);

            preg_match_all('#<td[^>]*>(.+?)</td>#is', $block, $m);
            $tdBlocks = $m[1];

            //$task['blocks'] = $m[1];

            $task['price'] = trim(strip_tags(html_entity_decode($tdBlocks[5])));
            $task['time_passed'] = trim(strip_tags(html_entity_decode($tdBlocks[4])));
            $task['outbound links'] = trim(strip_tags(html_entity_decode($tdBlocks[2])));


            preg_match('#<a href="([^"]*)"[^>]*>([^<]*)</a>#is', $tdBlocks[0], $m);
            $task['domain'] = trim($m[2]);
            $task['url'] = trim($m[1]);

            preg_match('#<a href="([^"]*)"[^>]*>([^<]*)</a>#is', $tdBlocks[1], $m);
            $task['customer'] = trim($m[2]);
            $task['customer_url'] = trim($m[1]);

            if ($task['id']){
                // получаем подробную информацию о задании
                $task['review'] = $this->getTask($task['id']);
            }

            $tasks[] = $task;

        }

        return $tasks;
    }



    /**
     * Получить подробную информацию о задании
     */
    public function getTask(int $taskId)
    {

        $response = $this->client->get('https://gogetlinks.net/template/view_task.php?curr_id='.$taskId);
        if ($response->getStatusCode() !== 200){
            throw new \Exception("Ошибка, статус страницы вернул код: ".$response->getStatusCode());
        }
        $html = $response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");

        $task = [];

        //dump($html);

        preg_match('#Тип обзора</div>.+?block_value">([^>]+?)</div>#uis',$html,$m);
        $task['type'] = $m[1];

        preg_match('#Внешних ссылок.+?block_value">([^<]+?)</div>#uis',$html,$m);
        $task['externalLinks'] = $m[1];

        preg_match('#Текст задания.+?block_value">(.+?)</div>#uis',$html,$m);
        $task['description'] = $m[1];

        preg_match('#<input[^>]+?id="copy_url"[^>]+?value="([^"]+)"#is', $html, $m);
        $task['url'] = $m[1];

        preg_match('#<input[^>]+?id="copy_unhor"[^>]+?value="([^"]+)"#is', $html, $m);
        $task['anchor'] = $m[1];

        preg_match('#<input[^>]+?id="copy_source"[^>]+?value="([^"]+)"#is', $html, $m);
        $task['source'] = $m[1];

        return $task;

        //dump($task);

    }

    /**
     * Заказать написание текста у gogetlinks
     */
    public function orderText () 
    {
        /**
         * https://gogetlinks.net/copyright/createTask/currId/21188625
         * 
         * description: Текст подходящий под задание.
         * lettersCount: 2
         * mode: manual
         * 
         * response:
         * {"success":true,"data":{"button_html":"<span onmouseout=\"return nd();\" onmouseover=\"return overlib(&#039;\u0417\u0430\u0434\u0430\u043d\u0438\u0435 \u043d\u0430 \u043d\u0430\u043f\u0438\u0441\u0430\u043d\u0438\u0435 \u0442\u0435\u043a\u0441\u0442\u0430 \u043e\u0442\u043f\u0440\u0430\u0432\u043b\u0435\u043d\u043e &lt;strong&gt;03.12.2021 08:49&lt;\/strong&gt;.&lt;br\/&gt;\u0421\u0442\u0430\u0442\u0443\u0441: &lt;strong&gt;\u041d\u0430\u043f\u0438\u0441\u0430\u043d\u0438\u0435 \u0442\u0435\u043a\u0441\u0442\u0430&lt;\/strong&gt;&#039;, CAPTION, &#039;\u041d\u0430\u043f\u0438\u0441\u0430\u043d\u0438\u0435 \u0442\u0435\u043a\u0441\u0442\u0430&#039;);\" class=\"icon-round-check-invert icon_small icon_green copyright-status-icon copyright-id-73443\"><\/span>"}}
         */
    }



}