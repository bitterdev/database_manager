<?php

namespace Bitter\DatabaseManager;

use Concrete\Core\Application\UserInterface\ContextMenu\DropdownMenu as ContextMenu;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\LinkItem;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\DialogLinkItem;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Http\Request;
use Concrete\Core\Permission\Key\Key;

class Menu extends ContextMenu
{
    protected $menuAttributes = ['class' => 'ccm-popover-file-menu'];
    protected $minItemThreshold = 0;

    public function __construct($table, $row)
    {
        parent::__construct();

        $app = Application::getFacadeApplication();
        /** @var DatabaseManager $databaseManager */
        /** @noinspection PhpUnhandledExceptionInspection */
        $databaseManager = $app->make(DatabaseManager::class);
        $rowIdentifiers = $databaseManager->getRowIdentifiers($table, $row, "rowIdentifiers[", "]");
        /** @var Request $request */
        /** @noinspection PhpUnhandledExceptionInspection */
        $request = $app->make(Request::class);

        $basicQueryParams = [
            "selectedTable" => $table,
            "returnUrl" => $request->getUri()
        ];

        $queryParams = array_merge($basicQueryParams, $rowIdentifiers);

        if (Key::getByHandle("edit_rows")->validate()) {
            $this->addItem(
                new DialogLinkItem(
                    sprintf(
                        "%s?%s",
                        Url::to("/bitter/database_manager/dialogs/edit"),
                        http_build_query($queryParams)
                    ),
                    t('Edit'),
                    t("Edit Row"),
                    500,
                    500,
                    [
                        "dialog-on-close" => "window.location.reload()"
                    ]
                )
            );
        }

        if (Key::getByHandle("delete_rows")->validate()) {
            $this->addItem(
                new LinkItem(
                    sprintf(
                        "%s?%s",
                        Url::to("/dashboard/database_manager/delete"),
                        http_build_query($queryParams)
                    ),
                    t('Delete'),
                    [
                        "data-action" => "delete"
                    ]
                )
            );
        }
    }

}
