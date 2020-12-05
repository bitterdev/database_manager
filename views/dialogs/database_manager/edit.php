<?php
/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\View\View;
use Concrete\Package\DatabaseManager\Controller\Dialog\DatabaseManager\Edit;

/** @var string $selectedTable */
/** @var array $rowIdentifiers */
/** @var array $columns */
/** @var array $tableRow */
/** @var Edit $controller */
?>

<form method="post" data-dialog-form="database-manager-edit" action="<?php echo $controller->action('submit') ?>">
    <?php
    /** @noinspection PhpUnhandledExceptionInspection */
    View::element("dashboard/fields", ["selectedTable" => $selectedTable, "rowIdentifiers" => $rowIdentifiers, "columns" => $columns, "tableRow" => $tableRow], "database_manager");
    ?>

    <div class="dialog-buttons">
        <button class="btn btn-default pull-left" data-dialog-action="cancel">
            <?php echo t('Cancel') ?>
        </button>

        <button type="button" data-dialog-action="submit" class="btn btn-primary pull-right">
            <?php echo t('Save') ?>
        </button>
    </div>
</form>