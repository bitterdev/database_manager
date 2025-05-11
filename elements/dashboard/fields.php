<?php

defined('C5_EXECUTE') or die("Access Denied.");

use Bitter\DatabaseManager\DatabaseManager;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Form\Service\Widget\DateTime;
use HtmlObject\Element;

/** @var string $selectedTable */
/** @var array $rowIdentifiers */
/** @var array $columns */
/** @var array $tableRow */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var DateTime $dateTime */
/** @noinspection PhpUnhandledExceptionInspection */
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
                    $htmlElement = $form->text("fields[" . $column["name"] . "]", $value, ["class" => "form-control"]);

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
                $inputGroupAddon = (new Element('span'))->addClass('input-group-text');
                $checkboxHtml = $form->checkbox("null[" . $column["name"] . "]", 1, $isNull, ["class" => "null-checkbox form-check-input mt-0"]);
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
<style>
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
            $(".null-checkbox").bind("change", function () {
                $(this).parent().parent().parent().parent().find(".form-control").prop("disabled", $(this).is(":checked"));
            }).trigger("change");
        });
    })(jQuery);
</script>