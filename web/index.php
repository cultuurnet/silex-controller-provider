<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 30.09.15
 * Time: 14:21
 */
use CultuurNet\SilexServiceProviderOAuth\OAuthServiceProvider;
use CultuurNet\SymfonySecurityOAuth\Model\Provider\TokenProviderInterface;
use CultuurNet\SymfonySecurityOAuthRedis\NonceProvider;
use CultuurNet\SymfonySecurityOAuthRedis\TokenProviderCache;
use DerAlex\Silex\YamlConfigServiceProvider;
use Silex\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;
