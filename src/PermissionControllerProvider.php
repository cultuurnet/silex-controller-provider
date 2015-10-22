<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 30.09.15
 * Time: 15:43
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionControllerProvider implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return Response
     */
    public function connect(Application $app) {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get(
            '/CheckPermission',
            function (Request $request, Application $app) {
            }
        );
        return $controllers;
    }
}
