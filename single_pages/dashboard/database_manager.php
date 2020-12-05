<?php
/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

use Bitter\DatabaseManager\Menu;
use Concrete\Core\Legacy\Pagination;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url as UrlFacade;
use Concrete\Core\Utility\Service\Url;
use Concrete\Core\View\View;

defined('C5_EXECUTE') or die('Access denied');

/** @var int $pageIndex */
/** @var int $pageSize */
/** @var int $tableCount */
/** @var string $selectedTable */
/** @var string $orderDirection */
/** @var string $orderBy */
/** @var array $tableColumns */
/** @var array $tableData */

$app = Application::getFacadeApplication();
/** @var Url $urlHelper */
$urlHelper = $app->make(Url::class);

/** @noinspection PhpUnhandledExceptionInspection */
View::element('/dashboard/help', null, 'database_manager');
/** @noinspection PhpUnhandledExceptionInspection */
View::element('/dashboard/reminder', array("packageHandle" => "database_manager", "rateUrl" => "https://www.concrete5.org/marketplace/addons/database-manager-1/reviews"), 'database_manager');
/** @noinspection PhpUnhandledExceptionInspection */
View::element('/dashboard/license_check', array("packageHandle" => "database_manager"), 'database_manager');

?>

    <table id="database-manager-table" class="ccm-search-results-table">
        <thead>
        <tr>
            <?php foreach ($tableColumns as $tableColumn): ?>
                <?php
                $cssOrderClass = "";

                if ($tableColumn == $orderBy) {
                    if ($orderDirection == "ASC") {
                        $cssOrderClass = "ccm-results-list-active-sort-asc";
                        $orderDirectionParam = "DESC";
                    } else {
                        $cssOrderClass = "ccm-results-list-active-sort-desc";
                        $orderDirectionParam = "ASC";
                    }
                } else {
                    $orderDirectionParam = "ASC";
                }

                $url = $urlHelper->setVariable(["orderBy" => $tableColumn, "orderDirection" => $orderDirectionParam], false, false);
                ?>

                <th class="<?php echo $cssOrderClass; ?>">
                    <a href="<?php echo $url; ?>">
                        <?php echo $tableColumn; ?>
                    </a>
                </th>
            <?php endforeach; ?>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($tableData as $tableRow): ?>
            <tr>
                <?php $i = 0; ?>

                <?php foreach ($tableRow as $tableColumn => $tableValue): ?>
                    <td>
                        <?php echo $tableValue; ?>

                        <?php
                        if ($i === 0) {
                            $menu = new Menu($selectedTable, $tableRow);
                            echo (string)$menu->getMenuElement();
                        }
                        $i++
                        ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="text-center">
        <ul class="pagination">
            <?php
            /** @var Pagination $pagination */
            $pagination = $app->make(Pagination::class);
            $pagination->queryStringPagingVariable = "pageIndex";
            $pagination->init($pageIndex, $tableCount, UrlFacade::to("/dashboard/database_manager"), $pageSize);
            echo $pagination->getPages("li");
            ?>
        </ul>
    </div>

    <!--suppress ES6ConvertVarToLetConst, JSUnresolvedVariable -->
    <script type="text/javascript">
        (function ($) {
            $(function () {
                // Add polyfill for version 8.0.0
                if (typeof ConcreteAlert.confirm === "undefined") {
                    ConcreteAlert.confirm = function (a, c, d, e) {
                        var f = $('<div id="ccm-popup-confirmation" class="ccm-ui"><div id="ccm-popup-confirmation-message">' + a + "</div>");
                        d = d ? "btn " + d : "btn btn-primary";
                        e = e ? e : "<?php echo h(t("Go")); ?>";
                        f.dialog({
                            title: "<?php echo h(t("Confirm")); ?>",
                            width: 500,
                            maxHeight: 500,
                            modal: !0,
                            dialogClass: "ccm-ui",
                            close: function () {
                                f.remove()
                            },
                            buttons: [{}],
                            open: function () {
                                $(this).parent().find(".ui-dialog-buttonpane").addClass("ccm-ui").html("");
                                $(this).parent().find(".ui-dialog-buttonpane").append('<button onclick="jQuery.fn.dialog.closeTop()" class="btn btn-default">' + "<?php echo h(t("Cancel")); ?>" + '</button><button data-dialog-action="submit-confirmation-dialog" class="btn ' + d + ' pull-right">' + e + "</button></div>")
                            }
                        });
                        f.parent().on("click", "button[data-dialog-action=submit-confirmation-dialog]", function () {
                            return c()
                        })
                    };
                }

                $("#database-manager-table tbody tr").on("mouseover", function () {
                    $(this).addClass("ccm-search-select-hover");
                }).on("mouseout", function () {
                    $(this).removeClass("ccm-search-select-hover");
                }).contextmenu(function (e) {
                    e.preventDefault();

                    var el = this;

                    $("#database-manager-table tbody tr").each(function () {
                        if (JSON.stringify(this) !== JSON.stringify(el)) {
                            $(this).removeClass("ccm-menu-item-active");
                        }
                    });

                    var concreteMenu = new ConcreteMenu($(this), {
                        menu: $(this).find(".ccm-popover-file-menu"),
                        handle: 'none',
                        container: $(this)
                    });

                    concreteMenu.show(e);

                    $("a[data-action='delete']").bind("click", function (e) {
                        e.preventDefault();

                        var deleteUrl = $(this).attr("href");

                        ConcreteAlert.confirm(
                            "<?php echo h(t("Are you sure?")); ?>",
                            function () {
                                document.location.href = deleteUrl;
                            },
                            'btn-danger',
                            "<?php echo h(t("Delete")); ?>"
                        );

                        return false;
                    });

                    return false;
                });
            });
        })(jQuery);
    </script>

<?php /** @noinspection PhpUnhandledExceptionInspection */
View::element('/dashboard/did_you_know', array("packageHandle" => "database_manager"), 'database_manager'); ?>