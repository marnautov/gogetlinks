<?php

namespace Amxm\Gogetlinks;


class Parser
{

    public static function hasAuthenticatedMarkup(string $html): bool
    {
        return preg_match('#href="/profile#', $html) && preg_match('#Выйти#u', $html);
    }

    public static function parseSites(string $html)
    {
        $sites = [];
        preg_match_all('#<tr class="siteRow".+?</tr>#is', $html, $matches);
        foreach ($matches[0] as $block) {
            $sites[] = Parser::parseSiteInfo($block);
        }
        return $sites;
    }

    public static function parseSiteInfo(string $block)
    {

        $site = [];

        // основные данные

        // домен
        preg_match('#<div class="site-link__info">([^<>]+)<#is', $block, $m);
        $site['domain'] = trim($m[1]);

        // id сайта в системе
        preg_match('#/editSiteInfo/index/site_id/([0-9]+)#is', $block, $m);
        $site['site_id'] = intval($m[1]);


        if (!$site['domain'] || !$site['site_id']) {
            // пока не буду кидать exception
            return false;
        }


        // информационные данные

        // статус (видимость) сайта
        preg_match('#<td\s+data\-th="Видимость"[^>]*>(.+?)</td>#uis', $block, $m);
        $status = null;
        if (preg_match('#Доступен#uis', $m[1])) {
            $status = 'AVAILABLE';
        } elseif (preg_match('#Скрыт#uis', $m[1])) {
            $status = 'HIDDEN';
        } elseif (preg_match('#Отклон[её]н#uis', $m[1])) {
            $status = 'REJECTED';
        } elseif (preg_match('#Не активен#uis', $m[1])) {
            $status = 'INACTIVE';
        }
        $site['status'] = $status;
        $site['status_text'] = trim(strip_tags($m[0]));

        // яндекс ИКС
        preg_match('#<td\s+data\-th="ИКС".+?<div[^>]*?>\s*([0-9]+)#uis', $block, $m);
        if ($m[1] && is_numeric($m[1])) $site['yandex_iks'] = intval($m[1]);

        // TF/CF
        preg_match('#<td\s+data\-th="TF/CF".+?</td>#uis', $block, $m);
        if ($m[0]) {
            $sText = trim(strip_tags($m[0]));
            if ($sText && is_numeric($sText)) $site['tc_cf'] = intval($sText);
        }

        // PR/CY
        preg_match('#<td\s+data\-th="PR.CY".+?</td>#uis', $block, $m);
        if ($m[0]) {
            $sText = trim(strip_tags($m[0]));
            if ($sText && is_numeric($sText)) $site['pr_cy'] = intval($sText);
        }

        // Трафик
        preg_match('#<td\s+data\-th="Трафик".+?<span[^>]*?>\s*([0-9]+)\s*</span>#uis', $block, $m);
        if ($m[1] && is_numeric($m[1])) $site['traffic'] = intval($m[1]);

        // Траст
        preg_match('#<td\s+data\-th="Траст".+?</td>#uis', $block, $m);
        if ($m[0]) {
            $sText = trim(strip_tags($m[0]));
            if ($sText && is_numeric($sText)) $site['trust'] = intval($sText);
        }


        // Скорость размещения
        preg_match('#<td data-th="Скорость размещения"[^<]+<div[^<]+(<div.+?)</#uis', $block, $m);
        $sText = trim(strip_tags($m[1]));
        if (preg_match('#(дней|день)#uis', $sText)) {
            $sText = floatval($sText);
        } else {
            $sText = null;
        }
        $site['posting_speed'] = $sText;

        return $site;
    }


    public static function parseTasks(string $html)
    {

        $tasks = [];

        preg_match_all('#<tr\s+id="col_row_.+?</tr>#is', $html, $matches);
        foreach ($matches[0] as $block) {
            $tasks[] = Parser::parseTask($block);
        }

        return $tasks;
    }

    public static function parseTask(string $block)
    {

        $task = [];

        preg_match('#col_row_([0-9]+)#is', $block, $m);
        // $task['id'] = intval($m[1]);
        $task['id'] = (int) ($m[1] ?? 0);

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

        return $task;
    }


    public static function parseTaskInfo($html)
    {
        $task = [];

        //dump($html);

        preg_match('#Тип обзора</div>.+?block_value">([^>]+?)</div>#uis', $html, $m);
        $task['type'] = $m[1];

        preg_match('#Внешних ссылок.+?block_value">([^<]+?)</div>#uis', $html, $m);
        $task['externalLinks'] = $m[1];

        preg_match('#Текст задания.+?block_value">(.+?)</div>#uis', $html, $m);
        $task['description'] = $m[1];

        preg_match('#<input[^>]+?id="copy_url"[^>]+?value="([^"]+)"#is', $html, $m);
        $task['url'] = $m[1];

        preg_match('#<input[^>]+?id="copy_unhor"[^>]+?value="([^"]+)"#is', $html, $m);
        $task['anchor'] = $m[1];

        preg_match('#<input[^>]+?id="copy_source"[^>]+?value="([^"]+)"#is', $html, $m);
        $task['source'] = $m[1];

        return $task;
    }

    public static function parseTaskTabs($html){

        preg_match('#<div\s+class="tabs\s+tabs_table">(.+?)</div>#is', $html, $m);
        if (!isset($m[1])) return false;
        $block = $m[1];

        preg_match_all('#<span>([^<>]+?)</span>\s*<span[^>]*?>([^<>]+?)</span>#is', $block, $matches, PREG_SET_ORDER);
        $info = array_reduce($matches, function($c,$i){
            $c[$i[1]] = $i[2];
            return $c;
        }, []);

        return $info;
        
    }


}
