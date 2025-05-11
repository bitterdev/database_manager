<?php

defined('C5_EXECUTE') or die('Access denied');

use Bitter\DatabaseManager\Menu;
use Concrete\Core\Legacy\Pagination;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url as UrlFacade;
use Concrete\Core\Utility\Service\Url;
use Concrete\Core\View\View;

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
/** @noinspection PhpUnhandledExceptionInspection */
$urlHelper = $app->make(Url::class);

?>

<?php /** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/did_you_know", [], "database_manager"); ?>

<div id="ccm-search-results-table" style="overflow-x: auto; min-height: 50vh;">
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

            <th>
                &nbsp;
            </th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($tableData as $tableRow): ?>
            <tr>
                <?php $i = 0; ?>

                <?php foreach ($tableRow as $tableColumn => $tableValue): ?>
                    <td>
                        <?php echo $tableValue; ?>
                    </td>
                <?php endforeach; ?>

                <td class="ccm-search-results-menu-launcher">
                    <div class="dropdown float-end" data-menu="search-result">
                        <button class="btn btn-icon show" data-boundary="viewport" type="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <svg width="16" height="4">
                                <use xlink:href="#icon-menu-launcher"></use>
                            </svg>
                        </button>

                        <?php
                        if ($i === 0) {
                            $menu = new Menu($selectedTable, $tableRow);
                            echo $menu->getMenuElement();
                        }
                        $i++
                        ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="text-center">
    <ul class="pagination">
        <?php
        /** @var Pagination $pagination */
        /** @noinspection PhpUnhandledExceptionInspection */
        $pagination = $app->make(Pagination::class);
        $pagination->queryStringPagingVariable = "pageIndex";
        $pagination->init($pageIndex, $tableCount, UrlFacade::to("/dashboard/database_manager"), $pageSize);
        echo $pagination->getPages("li");
        ?>
    </ul>
</div>

<!--suppress ES6ConvertVarToLetConst, JSUnresolvedVariable, JSUnresolvedFunction -->
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
            });

            $("a[data-action='delete']").on("click", function (e) {
                e.preventDefault();
                e.stopPropagation();

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
        });
    })(jQuery);
</script>
