<?php /** @noinspection DuplicatedCode */

namespace Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager;

use Concrete\Controller\Backend\UserInterface;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\File\EditResponse;
use Concrete\Core\Permission\Key\Key;
use Bitter\DatabaseManager\DatabaseManager;
use Concrete\Core\Support\Facade\Application;
use Exception;

class Edit extends UserInterface
{

    protected $viewPath = '/dialogs/database_manager/edit';

    /** @var Request */
    protected $request;
    /** @var DatabaseManager; */
    protected $databaseManager;
    /** @var ResponseFactory */
    protected $responseFactory;

    public function __construct()
    {
        parent::__construct();

        if (is_null($this->app)) {
            $this->app = Application::getFacadeApplication();
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->request = $this->app->make(Request::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->databaseManager = $this->app->make(DatabaseManager::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->responseFactory = $this->app->make(ResponseFactory::class);
    }

    public function view()
    {

        $params = $this->request->query->all();

        $selectedTable = $params["selectedTable"];
        $rowIdentifiers = $params["rowIdentifiers"];

        $columns = $this->databaseManager->getTableColumsWithDetails($selectedTable);
        $tableRow = $this->databaseManager->getTableRow($selectedTable, $rowIdentifiers);

        $this->set("selectedTable", $selectedTable);
        $this->set("rowIdentifiers", $rowIdentifiers);
        $this->set("columns", $columns);
        $this->set("tableRow", $tableRow);
    }

    public function submit()
    {
        $selectedTable = $this->request->request->get("selectedTable", '');
        $fields = $this->request->request->get("fields", []);
        $rowIdentifiers = $this->request->request->get("rowIdentifiers", []);
        $null = $this->request->request->get("null", []);

        foreach ($fields as $column => $value) {
            if (isset($null[$column])) {
                $fields[$column] = null; // set this to null
            }

            switch ($this->databaseManager->getColumnType($selectedTable, $column)) {
                case DatabaseManager::COLUMN_TYPE_BOOL:
                    $fields[$column] = intval($value) === 1;
                    break;

                case DatabaseManager::COLUMN_TYPE_NUMBER:
                    if (strpos($value, ".") !== false) {
                        $fields[$column] = (float)$value;
                    } else {
                        $fields[$column] = (int)$value;
                    }

                    break;
            }
        }

        foreach ($null as $nullColumn => $dummy) {
            if (!isset($fields[$nullColumn])) {
                $fields[$nullColumn] = null;
            }
        }

        $response = new EditResponse();

        try {
            $this->databaseManager->updateRow($selectedTable, $rowIdentifiers, $fields);

            $response->setMessage(t('Row updated successfully.'));
        } catch (Exception $error) {
            $response->setError($error);
        }

        /** @noinspection PhpDeprecationInspection */
        $response->outputJSON();
    }

    public function canAccess(): bool
    {
        return Key::getByHandle("edit_rows")->validate();
    }

}
