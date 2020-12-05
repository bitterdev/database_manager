<?php

/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

namespace Concrete\Package\DatabaseManager\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Bitter\DatabaseManager\DatabaseManager as DatabaseManagerService;
use Bitter\DatabaseManager\Header;

class DatabaseManager extends DashboardPageController {

    /** @var ResponseFactory */
    protected $responseFactory;

    /** @var Request */
    protected $request;

    /** @var DatabaseManagerService; */
    protected $databaseManager;

    public function on_start() {
        parent::on_start();

        /*
         * Load dependencies
         */

        $this->responseFactory = $this->app->make(ResponseFactory::class);
        $this->request = $this->app->make(Request::class);
        $this->databaseManager = $this->app->make(DatabaseManagerService::class);
    }

    public function delete() {
        $params = $this->request->query->all();

        $selectedTable = $params["selectedTable"];
        $rowIdentifiers = $params["rowIdentifiers"];
        $returnUrl = $params["returnUrl"];

        $this->databaseManager->deleteRow($selectedTable, $rowIdentifiers);
        
        return $this->responseFactory->redirect($returnUrl, Response::HTTP_TEMPORARY_REDIRECT);
    }
    

    public function view() {
        
        /*
         * Set Defaults
         */

        $selectedTable = $this->request->query->get("selectedTable");
        $orderBy = $this->request->query->get("orderBy");
        $orderDirection = $this->request->query->get("orderDirection");
        $pageIndex = (int) $this->request->query->get("pageIndex", 1);
        $pageSize = (int) $this->request->query->get("pageSize", 50);

        if (!$this->databaseManager->isValidTable($selectedTable)) {
            $selectedTable = $this->databaseManager->getDefaultTable();
        }

        $this->set('pageTitle', t("Selected Table: %s", $selectedTable));

        $tableStructure = $this->databaseManager->getTableStructure($selectedTable);
        $tableColumns = $this->databaseManager->getTableColums($selectedTable);
        $tableCount = $this->databaseManager->getTableCount($selectedTable);
        $tableData = $this->databaseManager->getTableData($selectedTable, $orderBy, $orderDirection, $pageIndex, $pageSize);

        $this->set("selectedTable", $selectedTable);
        $this->set("tableStructure", $tableStructure);
        $this->set("tableCount", $tableCount);
        $this->set("tableColumns", $tableColumns);
        $this->set("tableData", $tableData);
        $this->set("orderBy", $orderBy);
        $this->set("orderDirection", $orderDirection);
        $this->set("pageIndex", $pageIndex);
        $this->set("pageSize", $pageSize);

        $this->set("headerMenu", new Header($selectedTable));
        $this->requireAsset('bootstrap-datetimepicker');
    }

}
