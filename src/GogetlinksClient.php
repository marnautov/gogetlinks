<?php

namespace Amxm\Gogetlinks;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GogetlinksClient implements GogetlinksInterface
{

    private ClientInterface $client;
    private $debug = false;

    private $balance = [];

    function __construct($config = array(), ?CookieJar $cookieJar = null)
    {

        $this->client = new Client([
            'cookies' => $cookieJar ?? true, // You can set cookies to true in a client constructor if you would like to use a shared cookie jar for all requests.
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36',
                // 'Accept'    => 'application/json, text/javascript, */*; q=0.01',
            ],
            'debug' =>  $config['client_debug'] ?? false,
        ]);

        if (isset($config['debug'])) $this->debug = $config['debug'];
    }

    /**
     * Установить client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }



    /**
     * Авторизация
     */
    public function login(string $email, string $password, $attemptSessionRestore = true)
    {

        if ($attemptSessionRestore) {
            $this->dprint("Проверяем авторизацию по сессии [attemptSessionRestore]");
            // проверяем, возможно мы еще авторизованы по куке
            $url = 'https://gogetlinks.net/user/signIn';
            $response = $this->client->request('GET', $url);
            $html = (string)$response->getBody();
            $html = mb_convert_encoding($html, "utf-8", "windows-1251");

            if (Parser::hasAuthenticatedMarkup($html)) {
                $this->dprint("Уже авторизованы в системе, повторная авторизация не требуется");
                $this->parseBalance($html);
                return true;
            }
        }


        // $response = $this->client->request('GET', $url);
        // $html = (string)$response->getBody();
        // dd($html);

        $this->dprint("Авторизовываемся в системе с email {$email}");

        $url = 'https://gogetlinks.net/user/signIn';
        $postData = [
            'e_mail' => $email,
            'password' => $password,
            'remember' =>   'on',
            'is_ajax' => 'true'
        ];

        $response = $this->client->request('POST', $url, ['form_params' => $postData]);
        $html = (string)$response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");

        if (Parser::hasAuthenticatedMarkup($html)) {
            $this->dprint("Успешно авторизовались в системе");
            $this->parseBalance($html);
            return true;
        }

        $this->dprint("Авторизация не удалась");

        dd($html);

        throw new \Exception("Не удалась авторизация в системе");
    }


    /**
     * Список сайтов вебмастера
     */
    public function getSites(): array
    {

        $response = $this->client->request('GET', 'https://gogetlinks.net/mySites');
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Ошибка, статус страницы вернул код: " . $response->getStatusCode());
        }

        $html = (string)$response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");

        return Parser::parseSites($html);
    }


    /**
     * Список заданий вембастера
     */
    public function getTasks($type = 'NEW'): array
    {

        $urls = [
            'NEW'       =>  'https://gogetlinks.net/webTask/index',
            'WAIT'      =>  'https://gogetlinks.net/webTask/index/action/viewWait',
            'WAIT_INDEX'=>  'https://gogetlinks.net/webTask/index/action/viewWaitIndexation',
            'PAID'      =>  'https://gogetlinks.net/webTask/index/action/viewPaid',
        ];

        $url = $urls[$type];
        if (!$url) throw new \InvalidArgumentException("Указан не верный тип заданий");


        $response = $this->client->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Ошибка, статус страницы вернул код: " . $response->getStatusCode());
        }

        $html = (string)$response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");


        $tasksInfo = Parser::parseTaskTabs($html);

        // костыль, когда нет новых заданий
        if ($type == 'NEW' && $tasksInfo['active'] != "Новые") return [];

        // парсим таски
        $tasks = Parser::parseTasks($html);

        // array_walk($tasks, function (&$task) {
        //     $task['review'] = $this->getTask($task['id']);
        // });

        return $tasks;
    }



    /**
     * Получить подробную информацию о задании
     */
    public function getTask(int $taskId)
    {

        $response = $this->client->request('GET', 'https://gogetlinks.net/template/view_task.php?curr_id=' . $taskId);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Ошибка, статус страницы вернул код: " . $response->getStatusCode());
        }
        $html = $response->getBody();
        $html = mb_convert_encoding($html, "utf-8", "windows-1251");

        return Parser::parseTaskInfo($html);
    }

    /**
     * Заказать написание текста у gogetlinks
     */
    public function orderText()
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

    public function getBalance() {
        if ($this->balance) return $this->balance;
    }

    private function parseBalance($html)
    {
        $this->balance = Parser::parseBalance($html);
    }


    private function dprint($text)
    {
        if (!$this->debug) return;
        echo ($text . (php_sapi_name() == 'cli' ? "\n" : "<br>"));
    }
}
