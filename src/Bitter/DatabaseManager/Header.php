<?php

/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

namespace Bitter\DatabaseManager;

use Concrete\Core\Controller\ElementController;

class Header extends ElementController
{

    protected $pkgHandle = "database_manager";
    protected $selectedTable = null;

    public function __construct($selectedTable = null)
    {
        parent::__construct();

        $this->selectedTable = $selectedTable;
    }

    public function getElement()
    {
        return 'header';
    }

    public function view()
    {
        $this->set("selectedTable", $this->selectedTable);
    }

}
