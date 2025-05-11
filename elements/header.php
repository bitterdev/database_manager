<?php
defined('C5_EXECUTE') or die('Access denied');

use Bitter\DatabaseManager\DatabaseManager;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Http\Request;
use Concrete\Core\Permission\Key\Key;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\View\View;

/** @var string $selectedTable */

$app = Concrete\Core\Support\Facade\Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var DatabaseManager $databaseManager */
/** @noinspection PhpUnhandledExceptionInspection */
$databaseManager = $app->make(DatabaseManager::class);
/** @var Request $request */
/** @noinspection PhpUnhandledExceptionInspection */
$request = $app->make(Request::class);

?>

<div class="ccm-header-search-form ccm-ui">
    <form action="<?php echo Url::to("/dashboard/database_manager"); ?>" method="get">
        <div class="input-group">
            <?php echo $form->select("selectedTable", $databaseManager->getTables(), $selectedTable); ?>

            <div class="input-group-btn">
                <div class="btn-group">
                    <button class="btn btn-info" type="submit">
                        <?php echo t("Select Table"); ?>
                    </button>

                    <?php if (Key::getByHandle("insert_rows")->validate()) { ?>
                        <?php

                        $basicQueryParams = [
                            "selectedTable" => $request->query->get("selectedTable"),
                            "returnUrl" => $request->getUri()
                        ];
                        ?>

                        <!--suppress HtmlUnknownAttribute -->
                        <a dialog-on-close="window.location.reload()"
                           dialog-title="<?php echo h(t("Insert Row")); ?>"
                           dialog-width="500"
                           dialog-height="500"
                           class="btn btn-secondary dialog-launch"
                           href="<?php echo Url::to("/bitter/database_manager/dialogs/insert")->setQuery($basicQueryParams); ?>">
                            <?php echo t("Insert Row"); ?>
                        </a>
                    <?php } ?>
                    <?php

                    /** @noinspection PhpUnhandledExceptionInspection */
                    View::element("dashboard/help", [], "database_manager");
                    ?>
                </div>
            </div>
        </div>
    </form>
</div>
