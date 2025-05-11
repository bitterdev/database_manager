<?php

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

    public function getElement(): string
    {
        return 'header';
    }

    public function view()
    {
        $this->set("selectedTable", $this->selectedTable);
    }
}
