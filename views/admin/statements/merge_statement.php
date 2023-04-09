<?php defined('BASEPATH') or exit('No direct script access allowed');
if (count($statements_to_merge) > 0) { ?>
<div class="mergeable-statements">
    <h4 class="tw-font-semibold tw-mb-4"><?php echo _l('statements_available_for_merging'); ?></h4>
    <?php foreach ($statements_to_merge as $_inv) { ?>
    <div class="checkbox">
        <input type="checkbox" name="statements_to_merge[]" value="<?php echo $_inv->id; ?>">
        <label for="">
            <a href="<?php echo admin_url('statements/list_statements/' . $_inv->id); ?>" data-toggle="tooltip"
                data-title="<?php echo format_statement_status($_inv->status, '', false); ?>" target="_blank">
                <?php echo format_statement_number($_inv->id); ?>
            </a> - <?php echo app_format_money($_inv->total, $_inv->currency_name); ?>
        </label>
    </div>
    <?php
                if ($_inv->discount_total > 0) {
                    echo '<b>' . _l('statements_merge_discount', app_format_money($_inv->discount_total, $_inv->currency_name)) . '</b><br />';
                }
                ?>
    <?php } ?>
    <p>
    <div class="checkbox checkbox-info">
        <input type="checkbox" checked name="cancel_merged_statements" id="cancel_merged_statements">
        <label for="cancel_merged_statements"><i class="fa-regular fa-circle-question" data-toggle="tooltip"
                data-title="<?php echo _l('statement_merge_number_warning'); ?>" data-placement="bottom"></i>
            <?php echo _l('statements_merge_cancel_merged_statements'); ?></label>
    </div>
    </p>
</div>
<?php } ?>
