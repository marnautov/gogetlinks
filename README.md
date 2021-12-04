# gogetlinks

Класс для работы с сервисом https://gogetlinks.net/, в связи с отсутствием официального api.

```
$ggl = new \Amxm\Gogetlinks\Parser();
$ggl->signIn('email@email.com', 'password');

// список сайтов вебмастера
$sites = $ggl->getSites();

// список заданий на написание статей
$tasks = $ggl->getTasks();
```
