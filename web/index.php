<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 30.09.15
 * Time: 14:21
 */

use Silex\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->mount();
