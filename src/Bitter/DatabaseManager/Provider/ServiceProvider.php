<?php

/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

namespace Bitter\DatabaseManager\Provider;

use Bitter\DatabaseManager\RouteList;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Routing\Router;
use Concrete\Core\Http\Response;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Asset\Asset;

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
        PackageService $packageService,
        ResponseFactory $responseFactory,
        Router $router
    )
    {
        $this->router = $router;
        $this->pkg = $packageService->getByHandle("database_manager");
        $this->responseFactory = $responseFactory;
    }

    public function register()
    {

        $al = AssetList::getInstance();

        $al->register("javascript", "bootstrap-datetimepicker", "/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js", ["version" => "4.17.47", "position" => Asset::ASSET_POSITION_FOOTER], "database_manager");
        $al->register("css", "bootstrap-datetimepicker", "/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css", ["version" => "4.17.47", "position" => Asset::ASSET_POSITION_HEADER], "database_manager");

        $al->registerGroup(
            "bootstrap-datetimepicker",
            [
                ["javascript", "jquery"],
                ["javascript", "moment"],
                ["javascript", "bootstrap/*"],
                ["css", "bootstrap"],
                ["javascript", "bootstrap-datetimepicker"],
                ["css", "bootstrap-datetimepicker"]
            ]
        );

        /*
         * Register the database manager routes
         */

        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/edit', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Edit::view');
        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/edit/submit', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Edit::submit');
        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/insert', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Insert::view');
        /** @noinspection PhpDeprecationInspection */
        $this->router->register('/bitter/database_manager/dialogs/insert/submit', '\Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Insert::submit');

        /*
         * Register marketing routes
         */

        /** @noinspection PhpDeprecationInspection */
        $this->router->register("/bitter/database_manager/reminder/hide", function () {
            $this->pkg->getConfig()->save('reminder.hide', true);
            $this->responseFactory->create("", Response::HTTP_OK)->send();
            $this->app->shutdown();
        });

        /** @noinspection PhpDeprecationInspection */
        $this->router->register("/bitter/database_manager/did_you_know/hide", function () {
            $this->pkg->getConfig()->save('did_you_know.hide', true);
            $this->responseFactory->create("", Response::HTTP_OK)->send();
            $this->app->shutdown();
        });

        /** @noinspection PhpDeprecationInspection */
        $this->router->register("/bitter/database_manager/license_check/hide", function () {
            $this->pkg->getConfig()->save('license_check.hide', true);
            $this->responseFactory->create("", Response::HTTP_OK)->send();
            $this->app->shutdown();
        });

        $list = new RouteList();
        $list->loadRoutes($this->router);
    }

}
