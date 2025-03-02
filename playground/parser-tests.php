<?php

require __DIR__.'/../vendor/autoload.php';


$html = file_get_contents(__DIR__.'/ggl-tasks.html');







exit();
$html = file_get_contents(__DIR__.'/ggl-mysites.html');

// $html = mb_convert_encoding($html, "utf-8", "windows-1251");


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


dd($sites);