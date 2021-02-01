<?php

require __DIR__ . '/../vendor/autoload.php';

function url($url) {
    return $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . $url;
}

// Создания роутинга
$router = new \Bramus\Router\Router();
// Роуты по умоляанию
$router->setNamespace('\App\Controller');

require __DIR__ . '/../web.php';

// Run it!
$router->run();