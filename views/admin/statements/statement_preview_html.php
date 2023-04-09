<?php defined('BASEPATH') or exit('No direct script access allowed');
if ($statement->status == Statements_model::STATUS_DRAFT) { ?>
<div class="alert alert-info">
    <?php echo _l('statement_draft_status_info'); ?>
</div>
<?php }
if (isset($statement->scheduled_email) && $statement->scheduled_email) { ?>
<div class="alert alert-warning">
    <?php echo _l('statement_will_be_sent_at', _dt($statement->scheduled_email->scheduled_at)); ?>
    <?php if (staff_can('edit', 'statements') || $statement->addedfrom == get_staff_user_id()) { ?>
    <a href="#" onclick="edit_statement_scheduled_email(<?php echo $statement->scheduled_email->id; ?>); return false;">
        <?php echo _l('edit'); ?>
    </a>
    <?php } ?>
</div>
<?php } ?>
<div id="statement-preview">
    <div class="row">
        <?php

      if ($statement->recurring > 0 || $statement->is_recurring_from != null) {
          $recurring_statement           = $statement;
          $show_recurring_statement_info = true;

          if ($statement->is_recurring_from != null) {
              $recurring_statement = $this->statements_model->get($statement->is_recurring_from);
              // Maybe recurring statement not longer recurring?
              if ($recurring_statement->recurring == 0) {
                  $show_recurring_statement_info = false;
              } else {
                  $next_recurring_date_compare = $recurring_statement->last_recurring_date;
              }
          } else {
              $next_recurring_date_compare = $recurring_statement->date;
              if ($recurring_statement->last_recurring_date) {
                  $next_recurring_date_compare = $recurring_statement->last_recurring_date;
              }
          }
          if ($show_recurring_statement_info) {
              if ($recurring_statement->custom_recurring == 0) {
                  $recurring_statement->recurring_type = 'MONTH';
              }
              $next_date = date('Y-m-d', strtotime('+' . $recurring_statement->recurring . ' ' . strtoupper($recurring_statement->recurring_type), strtotime($next_recurring_date_compare)));
          } ?>
        <div class="col-md-12">
            <div class="mbot10">
                <?php if ($statement->is_recurring_from == null
         && $recurring_statement->cycles > 0
         && $recurring_statement->cycles == $recurring_statement->total_cycles) { ?>
                <div class="alert alert-info no-mbot">
                    <?php echo _l('recurring_has_ended', _l('statement_lowercase')); ?>
                </div>
                <?php } elseif ($show_recurring_statement_info) { ?>
                <span class="label label-info">
                    <?php
               if ($recurring_statement->status == Statements_model::STATUS_DRAFT) {
                   echo '<i class="fa-solid fa-circle-exclamation fa-fw text-warning tw-mr-1" data-toggle="tooltip" title="' . _l('recurring_statement_draft_notice') . '"></i>';
               }
               echo _l('cycles_remaining'); ?>:
                    <b>
                        <?php
                  echo $recurring_statement->cycles == 0 ? _l('cycles_infinity') : $recurring_statement->cycles - $recurring_statement->total_cycles;
                  ?>
                    </b>
                </span>
                <?php
            if ($recurring_statement->cycles == 0 || $recurring_statement->cycles != $recurring_statement->total_cycles) {
                echo '<span class="label label-info tw-ml-3"><i class="fa-regular fa-circle-question fa-fw tw-mr-1" data-toggle="tooltip" data-title="' . _l('recurring_recreate_hour_notice', _l('statement')) . '"></i> ' . _l('next_statement_date', ' <b>' . _d($next_date) . '</b>') . '</span>';
            }
         } ?>
            </div>
            <?php if ($statement->is_recurring_from != null) { ?>
            <?php echo '<p class="text-muted' . ($show_recurring_statement_info ? ' mtop15': '') . '">' . _l('statement_recurring_from', '<a href="' . admin_url('statements/list_statements/' . $statement->is_recurring_from) . '" onclick="init_statement(' . $statement->is_recurring_from . ');return false;">' . format_statement_number($statement->is_recurring_from) . '</a></p>'); ?>
            <?php } ?>
        </div>
        <div class="clearfix"></div>
        <hr class="hr-10" />
        <?php
      } ?>
        <?php if ($statement->project_id != 0) { ?>
        <div class="col-md-12">
            <h4 class="font-medium mtop15 mbot20"><?php echo _l('related_to_project', [
         _l('statement_lowercase'),
         _l('project_lowercase'),
         '<a href="' . admin_url('projects/view/' . $statement->project_id) . '" target="_blank">' . $statement->project_data->name . '</a>',
         ]); ?></h4>
        </div>
        <?php } ?>
        <div class="col-md-6 col-sm-6">
            <h4 class="bold">
                <?php
         $tags = get_tags_in($statement->id, 'statement');
         if (count($tags) > 0) {
             echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="' . html_escape(implode(', ', $tags)) . '"></i>';
         }
        ?>
                <a href="<?php echo admin_url('statements/statement/' . $statement->id); ?>">
                    <span id="statement-number">
                        <?php echo format_statement_number($statement->id); ?>
                    </span>
                </a>
            </h4>
            <address>
                <?php echo format_organization_info(); ?>
            </address>
            <?php hooks()->do_action('after_left_panel_statement_preview_template', $statement); ?>
        </div>
        <div class="col-sm-6 text-right">
            <span class="bold"><?php echo _l('statement_bill_to'); ?></span>
            <address class="tw-text-neutral-500">
                <?php echo format_customer_info($statement, 'billing', 'billing', true); ?>
            </address>
            <?php if ($statement->include_shipping == 1 && $statement->show_shipping_on_statement == 1) { ?>
            <span class="bold"><?php echo _l('ship_to'); ?></span>
            <address class="tw-text-neutral-500">
                <?php echo format_customer_info($statement, 'billing', 'shipping'); ?>
            </address>
            <?php } ?>
            <p class="no-mbot">
                <span class="bold">
                    <?php echo _l('statement_data_date'); ?>
                </span>
                <?php echo _d($statement->date); ?>
            </p>
            <?php if (!empty($statement->duedate)) { ?>
            <p class="no-mbot">
                <span class="bold">
                    <?php echo _l('statement_data_duedate'); ?>
                </span>
                <?php echo _d($statement->duedate); ?>
            </p>
            <?php } ?>
            <?php if ($statement->sale_agent != 0 && get_option('show_sale_agent_on_statements') == 1) { ?>
            <p class="no-mbot">
                <span class="bold"><?php echo _l('sale_agent_string'); ?>: </span>
                <?php echo get_staff_full_name($statement->sale_agent); ?>
            </p>
            <?php } ?>
            <?php if ($statement->project_id != 0 && get_option('show_project_on_statement') == 1) { ?>
            <p class="no-mbot">
                <span class="bold"><?php echo _l('project'); ?>:</span>
                <?php echo get_project_name_by_id($statement->project_id); ?>
            </p>
            <?php } ?>
            <?php $pdf_custom_fields = get_custom_fields('statement', ['show_on_pdf' => 1]);
   foreach ($pdf_custom_fields as $field) {
       $value = get_custom_field_value($statement->id, $field['id'], 'statement');
       if ($value == '') {
           continue;
       } ?>
            <p class="no-mbot">
                <span class="bold"><?php echo $field['name']; ?>: </span>
                <?php echo $value; ?>
            </p>
            <?php
   } ?>
            <?php hooks()->do_action('after_right_panel_statement_preview_template', $statement); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <?php
         $items = get_items_table_data($statement, 'statement', 'html', true);
         echo $items->table();
         ?>
            </div>
        </div>
        <div class="col-md-5 col-md-offset-7">
            <table class="table text-right">
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
                                <?php } ?>
                            </span>
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
                            <span class="bold tw-text-neutral-700"><?php echo _l('statement_adjustment'); ?></span>
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
                            <span class="bold tw-text-neutral-700"><?php echo _l('statement_total_paid'); ?></span>
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
                            <span class="<?php echo $statement->total_left_to_pay > 0 ? 'text-danger ': ''; ?> bold">
                                <?php echo _l('statement_amount_due'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?php echo $statement->total_left_to_pay > 0 ? 'text-danger ': ''; ?>">
                                <?php echo app_format_money($statement->total_left_to_pay, $statement->currency_name); ?>
                            </span>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (count($statement->attachments) > 0) { ?>
    <div class="clearfix"></div>
    <hr />
    <p class="bold text-muted"><?php echo _l('statement_files'); ?></p>
    <?php foreach ($statement->attachments as $attachment) {
                  $attachment_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);
                  if (!empty($attachment['external'])) {
                      $attachment_url = $attachment['external_link'];
                  } ?>
    <div class="mbot15 row inline-block full-width" data-attachment-id="<?php echo $attachment['id']; ?>">
        <div class="col-md-8">
            <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
            <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
            <br />
            <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
        </div>
        <div class="col-md-4 text-right tw-space-x-2">
            <?php if ($attachment['visible_to_customer'] == 0) {
                      $icon    = 'fa-toggle-off';
                      $tooltip = _l('show_to_customer');
                  } else {
                      $icon    = 'fa-toggle-on';
                      $tooltip = _l('hide_from_customer');
                  } ?>
            <a href="#" data-toggle="tooltip"
                onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $statement->id; ?>,this); return false;"
                data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?> fa-lg"
                    aria-hidden="true"></i></a>
            <?php if ($attachment['staffid'] == get_staff_user_id() || is_admin()) { ?>
            <a href="#" class="text-danger"
                onclick="delete_statement_attachment(<?php echo $attachment['id']; ?>); return false;"><i
                    class="fa fa-times fa-lg"></i></a>
            <?php } ?>
        </div>
    </div>
    <?php
              } ?>
    <?php } ?>
    <hr />
    <?php if ($statement->clientnote != '') { ?>
    <div class="col-md-12 row mtop15">
        <p class="bold text-muted"><?php echo _l('statement_note'); ?></p>
        <p><?php echo $statement->clientnote; ?></p>
    </div>
    <?php } ?>
    <?php if ($statement->terms != '') { ?>
    <div class="col-md-12 row mtop15">
        <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
        <p><?php echo $statement->terms; ?></p>
    </div>
    <?php } ?>
</div>