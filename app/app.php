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

        $controllers->get('event/{id}/', 'controller.event.read:readOne')
            ->method('GET')
            ->bind('route.event.read.one');

        $controllers->get('event', 'controller.event.update:doGet')
            ->method('POST')
            ->bind('route.event.update.get');

        $controllers->get('event/{id}', 'controller.event.update:doGet')
            ->method('POST')
            ->bind('route.event.update.get');

        $controllers->get('event/{id}', 'controller.event.update:doGet')
            ->method('PUT')
            ->bind('route.event.update.get');

        return $controllers;
    }
}