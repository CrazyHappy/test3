<?php
/**
 * Created by PhpStorm.
 * User: Mi
 * Date: 26.10.2018
 * Time: 2:22
 */

namespace App\Controller;


class Controller
{
    public function view($view, $array = []) {
        // Create new Plates instance
        $templates = new \League\Plates\Engine('/App/Views');

        // Render a template
        echo $templates->render($view, $array);
    }
}