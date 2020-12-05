<?php

/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

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