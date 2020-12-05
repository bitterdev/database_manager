<?php
/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

use Bitter\DatabaseManager\DatabaseManager;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Form\Service\Widget\DateTime;
use HtmlObject\Element;

defined('C5_EXECUTE') or die("Access Denied.");

/** @var string $selectedTable */
/** @var array $rowIdentifiers */
/** @var array $columns */
/** @var array $tableRow */

$app = Application::getFacadeApplication();
/** @var Form $form */
$form = $app->make(Form::class);
/** @var DateTime $dateTime */
$dateTime = $app->make(DateTime::class);
?>

<div class="database-fields">
    <?php
    if (isset($selectedTable)) {
        echo $form->hidden("selectedTable", $selectedTable);
    }

    if (isset($rowIdentifiers)) {
        foreach ($rowIdentifiers as $column => $value) {
            echo $form->hidden("rowIdentifiers[" . $column . "]", $value);
        }
    }

    if (isset($columns)) {
        foreach ($columns as $column) {
            $htmlElement = "";

            $value = isset($tableRow) ? $tableRow[$column["name"]] : null;

            switch ($column["type"]) {
                case DatabaseManager::COLUMN_TYPE_BOOL:
                    $values = [0 => t("Unchecked"), 1 => t("Checked")];
                    $htmlElement = $form->select("fields[" . $column["name"] . "]", $values, $value, ["class" => "form-control"]);
                    break;

                case DatabaseManager::COLUMN_TYPE_DATE:
                    $htmlElement = $form->text("fields[" . $column["name"] . "]", $value, ["class" => "form-control datetimepicker"]);

                    $inputGroup = (new Element('div'))->addClass('input-group date');
                    $inputGroup->setValue($htmlElement);
                    $htmlElement = $inputGroup;

                    break;

                case DatabaseManager::COLUMN_TYPE_NUMBER:
                    $htmlElement = $form->number("fields[" . $column["name"] . "]", $value, ["class" => "form-control"]);
                    break;

                default:
                    $htmlElement = $form->text("fields[" . $column["name"] . "]", $value, ["class" => "form-control"]);
                    break;
            }

            if ($column["isNullable"]) {
                $isNull = $value === null;

                $inputGroup = (new Element('div'))->addClass('input-group');
                $inputGroup->setValue($htmlElement);
                $inputGroupAddon = (new Element('span'))->addClass('input-group-addon');
                $checkboxHtml = $form->checkbox("null[" . $column["name"] . "]", 1, $isNull, ["class" => "null-checkbox"]);
                $checkboxLabel = (new Element('label'))->setAttribute("for", "null[" . $column["name"] . "]")->addClass("null-label")->setValue($checkboxHtml . " " . t("NULL"));
                $checkboxWrapper = (new Element('div'))->addClass('checkbox null-checkbox-wrapper');
                $checkboxWrapper->setValue($checkboxLabel);
                $inputGroupAddon->setValue($checkboxWrapper);
                $inputGroup->appendChild($inputGroupAddon);
                $htmlElement = $inputGroup;
            }

            $formGroup = (new Element('div'))->addClass('form-group');
            $formGroupLabel = (new Element('label'))->setAttribute("for", "fields[" . $column["name"] . "]")->setValue($column["name"]);
            $formGroup->setValue($formGroupLabel . $htmlElement);

            echo $formGroup;
        }
    }
    ?>
</div>

<!--suppress CssUnusedSymbol -->
<style type="text/css">

    /* Override font definition. Because font definition in core file app.css is corrupted. */
    @font-face {
        font-family: "Glyphicons Halflings";
        src: url("<?php echo rtrim((string) $app->make('url/canonical'), '/') . "/concrete/css/fonts/glyphiconshalflings-regular.eot"; ?>");
        src: url("<?php echo rtrim((string) $app->make('url/canonical'), '/') . "/concrete/css/fonts/glyphiconshalflings-regular.eot"; ?>?#iefix") format("embedded-opentype"),
        url("<?php echo rtrim((string) $app->make('url/canonical'), '/') . "/concrete/css/fonts/glyphiconshalflings-regular.woff"; ?>") format("woff"),
        url("<?php echo rtrim((string) $app->make('url/canonical'), '/') . "/concrete/css/fonts/glyphiconshalflings-regular.ttf"; ?>") format("truetype"),
        url("<?php echo rtrim((string) $app->make('url/canonical'), '/') . "/concrete/css/fonts/glyphiconshalflings-regular.svg"; ?>#glyphicons_halflingsregular") format("svg")
    }

    .database-fields .null-checkbox-wrapper {
        margin: 0 !important;
        margin-top: 3px !important;
        margin-bottom: -3px !important;
    }

    .database-fields .null-checkbox {
        display: inline-block !important;
        margin-right: 5px !important;
    }

    .database-fields .null-label {
        font-weight: normal !important;
    }

    .database-fields .input-group.date {
        display: block;
    }

    .database-fields div.form-group {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: block !important;
        float: left !important;
        width: 100% !important;
    }

</style>

<!--suppress JSUnresolvedVariable -->
<script>
    (function ($) {
        $(function () {
            $('.datetimepicker').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss'
            });

            $(".null-checkbox").bind("change", function () {
                $(this).parent().parent().parent().parent().find(".form-control").prop("disabled", $(this).is(":checked"));
            }).trigger("change");
        });
    })(jQuery);
</script>

