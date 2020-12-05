<?php
/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

defined('C5_EXECUTE') or die('Access denied');

use Bitter\DatabaseManager\DatabaseManager;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Url;

/** @var string $selectedTable */

$app = Concrete\Core\Support\Facade\Application::getFacadeApplication();
/** @var Form $form */
$form = $app->make(Form::class);
/** @var DatabaseManager $databaseManager */
$databaseManager = $app->make(DatabaseManager::class);

?>

<div class="ccm-header-search-form ccm-ui">
    <form action="<?php echo Url::to("/dashboard/database_manager"); ?>" method="get">
        <div class="input-group">
            <?php echo $form->select("selectedTable", $databaseManager->getTables(), $selectedTable); ?>

            <span class="input-group-btn">
                <button class="btn btn-info" type="submit">
                    <?php echo t("Select Table"); ?>
                </button>
            </span>
        </div>
    </form>
</div>