<?php

namespace Bitter\DatabaseManager;

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router->buildGroup()->setNamespace('Concrete\Package\DatabaseManager\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/database_manager')
            ->routes('dialogs/support.php', 'database_manager');
    }
}