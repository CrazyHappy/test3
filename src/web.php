<?php

//PublicController Описания функций находиться в папке App/Controller а дальше как в Laravel

$router->get('/',           'PublicController@index');      // Главная страница
$router->get('/render',     'PublicController@render');     // Рендер
$router->get('/read-txt',   'PublicController@readTxt');   // Чтения текстового файла