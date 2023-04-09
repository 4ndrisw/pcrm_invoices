<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-statements">
    <?php echo _l('clients_my_statements'); ?>
    <?php if (has_contact_permission('statements')) { ?>
    <span class="tw-text-sm">
        <a href="<?php echo site_url('clients/statement'); ?>" class="view-account-statement">
            <?php echo _l('view_account_statement'); ?>
        </a>
    </span>
    <?php } ?>
</h4>
<div class="panel_s">
    <div class="panel-body">
        <?php get_template_part('statements_stats'); ?>
        <hr />
        <table class="table dt-table table-statements" data-order-col="1" data-order-type="desc">
            <thead>
                <tr>
                    <th class="th-statement-number"><?php echo _l('clients_statement_dt_number'); ?></th>
                    <th class="th-statement-date"><?php echo _l('clients_statement_dt_date'); ?></th>
                    <th class="th-statement-duedate"><?php echo _l('clients_statement_dt_duedate'); ?></th>
                    <th class="th-statement-amount"><?php echo _l('clients_statement_dt_amount'); ?></th>
                    <th class="th-statement-status"><?php echo _l('clients_statement_dt_status'); ?></th>
                    <?php
                $custom_fields = get_custom_fields('statement', ['show_on_client_portal' => 1]);
                foreach ($custom_fields as $field) { ?>
                    <th><?php echo $field['name']; ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statements as $statement) { ?>
                <tr>
                    <td data-order="<?php echo $statement['number']; ?>"><a
                            href="<?php echo site_url('statement/' . $statement['id'] . '/' . $statement['hash']); ?>"
                            class="statement-number"><?php echo format_statement_number($statement['id']); ?></a></td>
                    <td data-order="<?php echo $statement['date']; ?>"><?php echo _d($statement['date']); ?></td>
                    <td data-order="<?php echo $statement['duedate']; ?>"><?php echo _d($statement['duedate']); ?></td>
                    <td data-order="<?php echo $statement['total']; ?>">
                        <?php echo app_format_money($statement['total'], $statement['currency_name']); ?></td>
                    <td><?php echo format_statement_status($statement['status'], 'inline-block', true); ?></td>
                    <?php foreach ($custom_fields as $field) { ?>
                    <td><?php echo get_custom_field_value($statement['id'], $field['id'], 'statement'); ?></td>
                    <?php } ?>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>