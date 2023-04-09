<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="mtop15 preview-top-wrapper">
    <div class="row">
        <div class="col-md-3">
            <div class="mbot30">
                <div class="statement-html-logo">
                    <?php echo get_dark_company_logo(); ?>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="top" data-sticky data-sticky-class="preview-sticky-header">
        <div class="container preview-sticky-container">
            <div class="sm:tw-flex tw-justify-between -tw-mx-4">
                <div class="sm:tw-self-end">
                    <h3 class="bold tw-my-0 statement-html-number">
                        <span class="sticky-visible hide tw-mb-2">
                            <?php echo format_statement_number($statement->id); ?>
                        </span>
                    </h3>
                    <span class="statement-html-status">
                        <?php echo format_statement_status($statement->status, '', true); ?>
                    </span>
                </div>
                <div class="tw-flex tw-items-end tw-space-x-2 tw-mt-3 sm:tw-mt-0">
                    <?php if (is_client_logged_in() && has_contact_permission('statements')) { ?>
                    <a href="<?php echo site_url('clients/statements/'); ?>"
                        class="btn btn-default action-button go-to-portal">
                        <?php echo _l('client_go_to_dashboard'); ?>
                    </a>
                    <?php } ?>
                    <?php echo form_open($this->uri->uri_string()); ?>
                    <button type="submit" name="statementpdf" value="statementpdf" class="btn btn-default action-button">
                        <i class='fa-regular fa-file-pdf'></i>
                        <?php echo _l('clients_statement_html_btn_download'); ?>
                    </button>
                    <?php echo form_close(); ?>

                    <a href="#" class="btn btn-success action-button statement-html-pay-now-top hide sticky-hidden
                  <?php if (($statement->status != Statements_model::STATUS_PAID && $statement->status != Statements_model::STATUS_CANCELLED
                     && $statement->total > 0) && found_statement_mode($payment_modes, $statement->id, false)) {
    echo ' pay-now-top';
} ?>">
                        <?php echo _l('statement_html_online_payment_button_text'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>

<div class="panel_s tw-mt-6">
    <div class="panel-body">
        <?php if (is_statement_overdue($statement)) { ?>
        <div class="col-md-10 col-md-offset-1 tw-mb-5">
            <div class="alert alert-danger text-center">
                <p class="tw-font-medium">
                    <?php echo _l('overdue_by_days', get_total_days_overdue($statement->duedate)) ?>
                </p>
            </div>
        </div>
        <?php } ?>
        <div class="col-md-10 col-md-offset-1">
            <div class="row mtop20">
                <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
                    <h4 class="tw-font-semibold tw-text-neutral-700 statement-html-number">
                        <?php echo format_statement_number($statement->id); ?>
                    </h4>
                    <address class="statement-html-company-info tw-text-neutral-500 tw-text-normal">
                        <?php echo format_organization_info(); ?>
                    </address>
                    <?php hooks()->do_action('after_left_panel_statementhtml', $statement); ?>
                </div>
                <div class="col-sm-6 text-right transaction-html-info-col-right">
                    <span class="tw-font-medium tw-text-neutral-700 statement-html-bill-to">
                        <?php echo _l('statement_bill_to'); ?>
                    </span>
                    <address class="statement-html-customer-billing-info tw-text-neutral-500 tw-text-normal">
                        <?php echo format_customer_info($statement, 'statement', 'billing'); ?>
                    </address>
                    <!-- shipping details -->
                    <?php if ($statement->include_shipping == 1 && $statement->show_shipping_on_statement == 1) { ?>
                    <span class="tw-font-medium tw-text-neutral-700 statement-html-ship-to">
                        <?php echo _l('ship_to'); ?>
                    </span>
                    <address class="statement-html-customer-shipping-info tw-text-neutral-500 tw-text-normal">
                        <?php echo format_customer_info($statement, 'statement', 'shipping'); ?>
                    </address>
                    <?php } ?>
                    <p class="statement-html-date tw-mb-0 tw-text-normal">
                        <span class="tw-font-medium tw-text-neutral-700">
                            <?php echo _l('statement_data_date'); ?>
                        </span>
                        <?php echo _d($statement->date); ?>
                    </p>
                    <?php if (!empty($statement->duedate)) { ?>
                    <p class="statement-html-duedate tw-mb-0 tw-text-normal">
                        <span class="tw-font-medium tw-text-neutral-700">
                            <?php echo _l('statement_data_duedate'); ?>
                        </span>
                        <?php echo _d($statement->duedate); ?>
                    </p>
                    <?php } ?>
                    <?php if ($statement->sale_agent != 0 && get_option('show_sale_agent_on_statements') == 1) { ?>
                    <p class="statement-html-sale-agent tw-mb-0 tw-text-normal">
                        <span class="tw-font-medium tw-text-neutral-700"><?php echo _l('sale_agent_string'); ?>:</span>
                        <?php echo get_staff_full_name($statement->sale_agent); ?>
                    </p>
                    <?php } ?>
                    <?php if ($statement->project_id != 0 && get_option('show_project_on_statement') == 1) { ?>
                    <p class="statement-html-project tw-mb-0 tw-text-normal">
                        <span class="tw-font-medium tw-text-neutral-700"><?php echo _l('project'); ?>:</span>
                        <?php echo get_project_name_by_id($statement->project_id); ?>
                    </p>
                    <?php } ?>
                    <?php $pdf_custom_fields = get_custom_fields('statement', ['show_on_pdf' => 1, 'show_on_client_portal' => 1]);
               foreach ($pdf_custom_fields as $field) {
                   $value = get_custom_field_value($statement->id, $field['id'], 'statement');
                   if ($value == '') {
                       continue;
                   } ?>
                    <p class="tw-mb-0 tw-text-normal">
                        <span class="tw-font-medium tw-text-neutral-700"><?php echo $field['name']; ?>: </span>
                        <?php echo $value; ?>
                    </p>
                    <?php
               } ?>
                    <?php hooks()->do_action('after_right_panel_statementhtml', $statement); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <?php
                            $items = get_items_table_data($statement, 'statement');
                            echo $items->table();
                        ?>
                    </div>
                </div>
                <div class="col-md-6 col-md-offset-6">
                    <table class="table text-right tw-text-normal">
                        <tbody>
                            <tr id="subtotal">
                                <td>
                                    <span class="bold tw-text-neutral-700"><?php echo _l('statement_subtotal'); ?></span>
                                </td>
                                <td class="subtotal">
                                    <?php echo app_format_money($statement->subtotal, $statement->currency_name); ?>
                                </td>
                            </tr>
                            <?php if (is_sale_discount_applied($statement)) { ?>
                            <tr>
                                <td>
                                    <span class="bold tw-text-neutral-700"><?php echo _l('statement_discount'); ?>
                                        <?php if (is_sale_discount($statement, 'percent')) { ?>
                                        (<?php echo app_format_number($statement->discount_percent, true); ?>%)
                                        <?php } ?></span>
                                </td>
                                <td class="discount">
                                    <?php echo '-' . app_format_money($statement->discount_total, $statement->currency_name); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php
                                foreach ($items->taxes() as $tax) {
                                    echo '<tr class="tax-area"><td class="bold !tw-text-neutral-700">' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)</td><td>' . app_format_money($tax['total_tax'], $statement->currency_name) . '</td></tr>';
                                }
                            ?>
                            <?php if ((int)$statement->adjustment != 0) { ?>
                            <tr>
                                <td>
                                    <span class="bold tw-text-neutral-700">
                                        <?php echo _l('statement_adjustment'); ?>
                                    </span>
                                </td>
                                <td class="adjustment">
                                    <?php echo app_format_money($statement->adjustment, $statement->currency_name); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td>
                                    <span class="bold tw-text-neutral-700"><?php echo _l('statement_total'); ?></span>
                                </td>
                                <td class="total">
                                    <?php echo app_format_money($statement->total, $statement->currency_name); ?>
                                </td>
                            </tr>
                            <?php if (count($statement->payments) > 0 && get_option('show_total_paid_on_statement') == 1) { ?>
                            <tr>
                                <td>
                                    <span class="bold tw-text-neutral-700">
                                        <?php echo _l('statement_total_paid'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo '-' . app_format_money(sum_from_table(db_prefix() . 'statementpaymentrecords', ['field' => 'amount', 'where' => ['statementid' => $statement->id]]), $statement->currency_name); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if (get_option('show_credits_applied_on_statement') == 1 && $credits_applied = total_credits_applied_to_statement($statement->id)) { ?>
                            <tr>
                                <td>
                                    <span class="bold tw-text-neutral-700"><?php echo _l('applied_credits'); ?></span>
                                </td>
                                <td>
                                    <?php echo '-' . app_format_money($credits_applied, $statement->currency_name); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if (get_option('show_amount_due_on_statement') == 1 && $statement->status != Statements_model::STATUS_CANCELLED) { ?>
                            <tr>
                                <td>
                                    <span
                                        class="<?php echo $statement->total_left_to_pay > 0 ? 'text-danger ': ''; ?> bold">
                                        <?php echo _l('statement_amount_due'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?php echo $statement->total_left_to_pay > 0 ? 'text-danger': ''; ?>">
                                        <?php echo app_format_money($statement->total_left_to_pay, $statement->currency_name); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if (get_option('total_to_words_enabled') == 1) { ?>
                <div class="col-md-12 text-center statement-html-total-to-words">
                    <p class="tw-font-medium">
                        <?php echo _l('num_word'); ?>:<span class="tw-text-neutral-500">
                            <?php echo $this->numberword->convert($statement->total, $statement->currency_name); ?>
                        </span>
                    </p>
                </div>
                <?php } ?>
                <?php if (count($statement->attachments) > 0 && $statement->visible_attachments_to_customer_found == true) { ?>
                <div class="clearfix"></div>
                <div class="statement-html-files">
                    <div class="col-md-12">
                        <hr />
                        <p><b><?php echo _l('statement_files'); ?></b></p>
                    </div>
                    <?php foreach ($statement->attachments as $attachment) {
                                // Do not show hidden attachments to customer
                                if ($attachment['visible_to_customer'] == 0) {
                                    continue;
                                }
                                $attachment_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);
                                if (!empty($attachment['external'])) {
                                    $attachment_url = $attachment['external_link'];
                                } ?>
                    <div class="col-md-12 mbot10">
                        <div class="pull-left">
                            <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>
                        </div>
                        <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
                    </div>
                    <?php
                            } ?>
                </div>
                <?php } ?>
                <?php if (!empty($statement->clientnote)) { ?>
                <div class="col-md-12 statement-html-note">
                    <p>
                        <b><?php echo _l('statement_note'); ?></b>
                    </p>
                    <div class="tw-text-neutral-500 tw-mt-2.5">
                        <?php echo $statement->clientnote; ?>
                    </div>
                </div>
                <?php } ?>
                <?php if (!empty($statement->terms)) { ?>
                <div class="col-md-12 statement-html-terms-and-conditions">
                    <hr />
                    <p>
                        <b>
                            <?php echo _l('terms_and_conditions'); ?>
                        </b>
                    </p>
                    <div class="tw-text-neutral-500 tw-mt-2.5">
                        <?php echo $statement->terms; ?>
                    </div>
                </div>
                <?php } ?>
                <div class="col-md-12">
                    <hr />
                </div>
                <div class="col-md-12 statement-html-payments">
                    <p>
                        <b><?php echo _l('statement_received_payments'); ?></b>
                    </p>
                    <?php
               $total_payments = count($statement->payments);

               if ($total_payments > 0) { ?>
                    <table class="table table-hover statement-payments-table tw-mt-2.5">
                        <thead>
                            <tr>
                                <th><?php echo _l('statement_payments_table_number_heading'); ?></th>
                                <th><?php echo _l('statement_payments_table_mode_heading'); ?></th>
                                <th><?php echo _l('statement_payments_table_date_heading'); ?></th>
                                <th><?php echo _l('statement_payments_table_amount_heading'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statement->payments as $payment) { ?>
                            <tr>
                                <td>
                                    <span class="pull-left"><?php echo $payment['paymentid']; ?></span>
                                    <?php echo form_open($this->uri->uri_string()); ?>
                                    <button type="submit" value="<?php echo $payment['paymentid']; ?>"
                                        class="btn btn-icon btn-default pull-right" name="paymentpdf"><i
                                            class="fa-regular fa-file-pdf"></i></button>
                                    <?php echo form_close(); ?>
                                </td>
                                <td><?php echo $payment['name']; ?> <?php if (!empty($payment['paymentmethod'])) {
                   echo ' - ' . $payment['paymentmethod'];
               } ?></td>
                                <td><?php echo _d($payment['date']); ?></td>
                                <td><?php echo app_format_money($payment['amount'], $statement->currency_name); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <hr />
                    <?php } else { ?>
                    <h5 class="tw-font-medium tw-mt-0 tw-text-neutral-500">
                        <?php echo _l('statement_no_payments_found'); ?></h5>
                    <div class="clearfix"></div>
                    <hr />
                    <?php } ?>
                </div>
                <?php
            // No payments for paid and cancelled
            if (($statement->status != Statements_model::STATUS_PAID
               && $statement->status != Statements_model::STATUS_CANCELLED
               && $statement->total > 0)) { ?>
                <div class="col-md-12">
                    <div class="row">
                        <?php
                     $found_online_mode = false;
                     if (found_statement_mode($payment_modes, $statement->id, false)) {
                         $found_online_mode = true; ?>
                        <div class="col-md-6 text-left">
                            <p class="tw-mb-2.5 tw-font-medium"><?php echo _l('statement_html_online_payment'); ?></p>
                            <?php echo form_open($this->uri->uri_string(), ['id' => 'online_payment_form', 'novalidate' => true]); ?>
                            <?php foreach ($payment_modes as $mode) {
                             if (!is_numeric($mode['id']) && !empty($mode['id'])) {
                                 if (!is_payment_mode_allowed_for_statement($mode['id'], $statement->id)) {
                                     continue;
                                 } ?>
                            <div class="radio radio-success online-payment-radio">
                                <input type="radio" value="<?php echo $mode['id']; ?>"
                                    id="pm_<?php echo $mode['id']; ?>" name="paymentmode">
                                <label for="pm_<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></label>
                            </div>
                            <?php if (!empty($mode['description'])) { ?>
                            <div class="mbot15">
                                <?php echo $mode['description']; ?>
                            </div>
                            <?php }
                             }
                         } ?>
                            <div class="form-group mtop25">
                                <?php if (get_option('allow_payment_amount_to_be_modified') == 1) { ?>
                                <label for="amount"
                                    class="control-label"><?php echo _l('statement_html_amount'); ?></label>
                                <div class="input-group">
                                    <input type="number" required max="<?php echo $statement->total_left_to_pay; ?>"
                                        data-total="<?php echo $statement->total_left_to_pay; ?>" name="amount"
                                        class="form-control" value="<?php echo $statement->total_left_to_pay; ?>">
                                    <span class="input-group-addon">
                                        <?php echo $statement->symbol; ?>
                                    </span>
                                </div>
                                <?php } else {
                             echo '<h4 class="bold mbot25">' . _l('statement_html_total_pay', app_format_money($statement->total_left_to_pay, $statement->currency_name)) . '</h4>';
                         } ?>
                            </div>
                            <div id="pay_button">
                                <input id="pay_now" type="submit" name="make_payment" class="btn btn-success"
                                    value="<?php echo _l('statement_html_online_payment_button_text'); ?>">
                            </div>
                            <input type="hidden" name="hash" value="<?php echo $hash; ?>">
                            <?php echo form_close(); ?>
                        </div>
                        <?php
                     } ?>
                        <?php if (found_statement_mode($payment_modes, $statement->id)) { ?>
                        <div class="statement-html-offline-payments <?php if ($found_online_mode == true) {
                         echo 'col-md-6 text-right';
                     } else {
                         echo 'col-md-12';
                     }; ?>">
                            <p class="tw-mb-2.5 tw-font-medium"><?php echo _l('statement_html_offline_payment'); ?></p>
                            <?php foreach ($payment_modes as $mode) {
                         if (is_numeric($mode['id'])) {
                             if (!is_payment_mode_allowed_for_statement($mode['id'], $statement->id)) {
                                 continue;
                             } ?>
                            <p class="bold"><?php echo $mode['name']; ?></p>
                            <?php if (!empty($mode['description'])) { ?>
                            <div class="mbot15">
                                <?php echo $mode['description']; ?>
                            </div>
                            <?php }
                         }
                     } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<script>
$(function() {
    new Sticky('[data-sticky]');
    var $payNowTop = $('.pay-now-top');
    if ($payNowTop.length && !$('#pay_now').isInViewport()) {
        $payNowTop.removeClass('hide');
        $('.pay-now-top').on('click', function(e) {
            e.preventDefault();
            $('html,body').animate({
                    scrollTop: $("#online_payment_form").offset().top
                },
                'slow');
        });
    }

    $('#online_payment_form').appFormValidator();

    var online_payments = $('.online-payment-radio');
    if (online_payments.length == 1) {
        online_payments.find('input').prop('checked', true);
    }
});
</script>