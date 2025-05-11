<?php

namespace Bitter\DatabaseManager\Provider;

use Bitter\DatabaseManager\RouteList;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Routing\Router;

class ServiceProvider implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /** @var Router */
    protected $router;
    /** @var ResponseFactory */
    protected $responseFactory;
    /** @var Package */
    protected $pkg;

    public function __construct(
        PackageService  $packageService,
        ResponseFactory $responseFactory,
        Router          $router
    )
    {
        $this->router = $router;
        $this->pkg = $packageService->getByHandle("database_manager");
        $this->responseFactory = $responseFactory;
    }

    public function register()
    {
        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/edit', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Edit::view');
        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/edit/submit', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Edit::submit');
        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/insert', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Insert::view');
        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/insert/submit', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Insert::submit');


        $list = new RouteList();
        $list->loadRoutes($this->router);
    }

}
