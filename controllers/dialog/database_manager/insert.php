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

class Insert extends UserInterface
{

    protected $viewPath = '/dialogs/database_manager/insert';

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
        $selectedTable = $this->request->query->get("selectedTable");

        $columns = $this->databaseManager->getTableColumsWithDetails($selectedTable);

        $this->set("selectedTable", $selectedTable);
        $this->set("columns", $columns);
    }

    public function submit()
    {
        $selectedTable = $this->request->request->get("selectedTable", '');
        $fields = $this->request->request->get("fields", []);
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
            $this->databaseManager->insertRow($selectedTable, $fields);

            $response->setMessage(t('Row inserted successfully.'));

        } catch (Exception $error) {
            $response->setError($error);
        }

        /** @noinspection PhpDeprecationInspection */
        $response->outputJSON();
    }

    public function canAccess(): bool
    {
        return Key::getByHandle("insert_rows")->validate();
    }

}
