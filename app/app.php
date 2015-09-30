<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 30.09.15
 * Time: 15:43
 */

namespace CultuurNet\EventController;

use Silex\Application;
use Silex\ControllerProviderInterface;

class EventControllerProvider implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function (Application $app) {
            return $app->redirect('/event');
        });

        $controllers->get('/event', function(Application $app) {
            return $app->redirect('/event');
        });

        $controllers->post('/event', function(Application $app) {
            return $app->redirect();
        });

        return $controllers;
    }
}