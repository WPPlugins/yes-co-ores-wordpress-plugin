<?php
$secret         = '87c935';
$collectionUuid = 'aa001460-3818-11e4-916c-0800200c9a66';
$action         = 'sync_yesco_og';



// create Url
$signature  = md5('action=' . $action . 'uuid=' . $collectionUuid . $secret);
$requestUrl = 'http://' . $_SERVER['HTTP_HOST'] .'/?action=' . $action . '&uuid=' . $collectionUuid . '&signature=' . $signature;

echo $requestUrl;

