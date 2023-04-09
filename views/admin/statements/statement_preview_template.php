<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ((credits_can_be_applied_to_invoice($statement->status) && $credits_available > 0)) { ?>
<div class="alert alert-warning mbot5">
    <?php echo _l('x_credits_available', app_format_money($credits_available, $customer_currency->name)); ?>
    <br />
    <a href="#" data-toggle="modal" data-target="#apply_credits"><?php echo _l('apply_credits'); ?></a>
</div>
<?php } ?>
<?php if (count($statements_to_merge) > 0) { ?>
<div class="panel_s no-padding mbot5 mergeable-statements">
    <div class="panel-heading">
        <h4 class="panel-title">
            <?php echo _l('statements_available_for_merging'); ?>
        </h4>
    </div>
    <div class="panel-body">
        <?php foreach ($statements_to_merge as $_inv) { ?>
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 last:tw-mb-0">
            <div>
                <a href="<?php echo admin_url('statements/list_statements/' . $_inv->id); ?>" target="_blank"
                    class="tw-font-medium"><?php echo format_statement_number($_inv->id); ?></a> -
                <span class="tw-text-neutral-500">
                    <?php echo app_format_money($_inv->total, $_inv->currency_name); ?>
                </span>
            </div>
            <?php echo format_statement_status($_inv->status); ?>
        </div>
        <?php } ?>
    </div>
</div>
<?php } ?>
<?php echo form_hidden('_attachment_sale_id', $statement->id); ?>
<?php echo form_hidden('_attachment_sale_type', 'statement'); ?>
<div class="col-md-12 no-padding">
    <div class="panel_s">
        <div class="panel-body">
            <div class="horizontal-scrollable-tabs preview-tabs-top panel-full-width-tabs">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                    <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab_statement" aria-controls="tab_statement" role="tab" data-toggle="tab">
                                <?php echo _l('statement'); ?>
                            </a>
                        </li>
                        <?php if (count($statement->payments) > 0) { ?>
                        <li role="presentation">
                            <a href="#statement_payments_received" aria-controls="statement_payments_received" role="tab"
                                data-toggle="tab">
                                <?php echo _l('payments'); ?>
                                <span class="badge"><?php echo count($statement->payments); ?>
                                </span>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (count($applied_credits) > 0) { ?>
                        <li role="presentation">
                            <a href="#statement_applied_credits" aria-controls="statement_applied_credits" role="tab"
                                data-toggle="tab">
                                <?php echo _l('applied_credits'); ?> <span
                                    class="badge"><?php echo count($applied_credits); ?></span>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (count($statement_recurring_statements) > 0 || $statement->recurring != 0) { ?>
                        <li role="presentation">
                            <a href="#tab_child_statements" aria-controls="tab_child_statements" role="tab"
                                data-toggle="tab">
                                <?php echo _l('child_statements'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li role="presentation">
                            <a href="#tab_tasks"
                                onclick="init_rel_tasks_table(<?php echo $statement->id; ?>,'statement'); return false;"
                                aria-controls="tab_tasks" role="tab" data-toggle="tab">
                                <?php echo _l('tasks'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                                <?php echo _l('statement_view_activity_tooltip'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_reminders"
                                onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $statement->id ; ?> + '/' + 'statement', undefined, undefined,undefined,[1,'asc']); return false;"
                                aria-controls="tab_reminders" role="tab" data-toggle="tab">
                                <?php echo _l('estimate_reminders'); ?>
                                <?php
                        $total_reminders = total_rows(
    db_prefix() . 'reminders',
    [
                          'isnotified' => 0,
                          'staff'      => get_staff_user_id(),
                          'rel_type'   => 'statement',
                          'rel_id'     => $statement->id,
                        ]
);
                        if ($total_reminders > 0) {
                            echo '<span class="badge">' . $total_reminders . '</span>';
                        }
                        ?>
                            </a>
                        </li>
                        <li role="presentation" class="tab-separator">
                            <a href="#tab_notes"
                                onclick="get_sales_notes(<?php echo $statement->id; ?>,'statements'); return false"
                                aria-controls="tab_notes" role="tab" data-toggle="tab">
                                <?php echo _l('estimate_notes'); ?> <span class="notes-total">
                                    <?php if ($totalNotes > 0) { ?>
                                    <span class="badge"><?php echo $totalNotes; ?></span>
                                    <?php } ?>
                                </span>
                            </a>
                        </li>
                        <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>"
                            class="tab-separator">
                            <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab"
                                data-toggle="tab">
                                <?php if (!is_mobile()) { ?>
                                <i class="fa-regular fa-envelope-open" aria-hidden="true"></i>
                                <?php } else { ?>
                                <?php echo _l('emails_tracking'); ?>
                                <?php } ?>
                            </a>
                        </li>
                        <li role="presentation" data-toggle="tooltip" title="<?php echo _l('view_tracking'); ?>"
                            class="tab-separator">
                            <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                                <?php if (!is_mobile()) { ?>
                                <i class="fa fa-eye"></i>
                                <?php } else { ?>
                                <?php echo _l('view_tracking'); ?>
                                <?php } ?>
                            </a>
                        </li>
                        <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>"
                            class="tab-separator toggle_view">
                            <a href="#" onclick="small_table_full_view(); return false;">
                                <i class="fa fa-expand"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mtop20">
                <div class="col-md-3">
                    <?php echo format_statement_status($statement->status, 'mtop5 inline-block'); ?>
                </div>
                <div class="col-md-9 _buttons">
                    <div class="visible-xs">
                        <div class="mtop10"></div>
                    </div>
                    <div class="pull-right">
                        <?php
                     $_tooltip              = _l('statement_sent_to_email_tooltip');
                     $_tooltip_already_send = '';
                     if ($statement->sent == 1 && is_date($statement->datesend)) {
                         $_tooltip_already_send = _l('statement_already_send_to_client_tooltip', time_ago($statement->datesend));
                     }
                     ?>
                        <?php if (has_permission('statements', '', 'edit')) { ?>
                        <a href="<?php echo admin_url('statements/statement/' . $statement->id); ?>" data-toggle="tooltip"
                            title="<?php echo _l('edit_statement_tooltip'); ?>" class="btn btn-default btn-with-tooltip"
                            data-placement="bottom"><i class="fa-regular fa-pen-to-square"></i></a>
                        <?php } ?>
                        <div class="btn-group">
                            <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><?php if (is_mobile()) {
                         echo ' PDF';
                     } ?> <span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li class="hidden-xs"><a
                                        href="<?php echo admin_url('statements/pdf/' . $statement->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a>
                                </li>
                                <li class="hidden-xs"><a
                                        href="<?php echo admin_url('statements/pdf/' . $statement->id . '?output_type=I'); ?>"
                                        target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                <li><a
                                        href="<?php echo admin_url('statements/pdf/' . $statement->id); ?>"><?php echo _l('download'); ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo admin_url('statements/pdf/' . $statement->id . '?print=true'); ?>"
                                        target="_blank">
                                        <?php echo _l('print'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php if (!empty($statement->clientid)) { ?>
                        <span<?php if ($statement->status == Statements_model::STATUS_CANCELLED) { ?> data-toggle="tooltip"
                            data-title="<?php echo _l('statement_cancelled_email_disabled'); ?>" <?php } ?>>
                            <a href="#" class="statement-send-to-client btn-with-tooltip btn btn-default<?php if ($statement->status == Statements_model::STATUS_CANCELLED) {
                         echo ' disabled';
                     } ?>" data-toggle="tooltip" title="<?php echo $_tooltip; ?>" data-placement="bottom"><span
                                    data-toggle="tooltip" data-title="<?php echo $_tooltip_already_send; ?>"><i
                                        class="fa-regular fa-envelope"></i></span></a>
                            </span>
                            <?php } ?>
                            <!-- Single button -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-default pull-left dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo _l('more'); ?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="<?php echo site_url('statement/' . $statement->id . '/' . $statement->hash) ?>"
                                            target="_blank"><?php echo _l('view_statement_as_customer_tooltip'); ?></a>
                                    </li>
                                    <li>
                                        <?php hooks()->do_action('after_statement_view_as_client_link', $statement); ?>
                                        <?php if (is_statement_overdue($statement) && is_statements_overdue_reminders_enabled()) { ?>
                                        <a
                                            href="<?php echo admin_url('statements/send_overdue_notice/' . $statement->id); ?>">
                                            <?php echo _l('send_overdue_notice_tooltip'); ?>
                                        </a>
                                        <?php } ?>
                                    </li>
                                    <?php if ($statement->status != Statements_model::STATUS_CANCELLED
                           && has_permission('credit_notes', '', 'create')
                           && !empty($statement->clientid)) {?>
                                    <li>
                                        <a href="<?php echo admin_url('credit_notes/credit_note_from_statement/' . $statement->id); ?>"
                                            id="statement_create_credit_note"
                                            data-status="<?php echo $statement->status; ?>"><?php echo _l('create_credit_note'); ?></a>
                                    </li>
                                    <?php } ?>
                                    <li>
                                        <a href="#" data-toggle="modal"
                                            data-target="#sales_attach_file"><?php echo _l('statement_attach_file'); ?></a>
                                    </li>
                                    <?php if (has_permission('statements', '', 'create')) { ?>
                                    <li>
                                        <a
                                            href="<?php echo admin_url('statements/copy/' . $statement->id); ?>"><?php echo _l('statement_copy'); ?></a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($statement->sent == 0) { ?>
                                    <li>
                                        <a
                                            href="<?php echo admin_url('statements/mark_as_sent/' . $statement->id); ?>"><?php echo _l('statement_mark_as_sent'); ?></a>
                                    </li>
                                    <?php } ?>
                                    <?php if (has_permission('statements', '', 'edit') || has_permission('statements', '', 'create')) { ?>
                                    <li>
                                        <?php if ($statement->status != Statements_model::STATUS_CANCELLED
                              && $statement->status != Statements_model::STATUS_PAID
                              && $statement->status != Statements_model::STATUS_PARTIALLY) { ?>
                                        <a
                                            href="<?php echo admin_url('statements/mark_as_cancelled/' . $statement->id); ?>"><?php echo _l('statement_mark_as', _l('statement_status_cancelled')); ?></a>
                                        <?php } elseif ($statement->status == Statements_model::STATUS_CANCELLED) { ?>
                                        <a
                                            href="<?php echo admin_url('statements/unmark_as_cancelled/' . $statement->id); ?>"><?php echo _l('statement_unmark_as', _l('statement_status_cancelled')); ?></a>
                                        <?php } ?>
                                    </li>
                                    <?php } ?>
                                    <?php if (!in_array($statement->status, [Statements_model::STATUS_PAID, Statements_model::STATUS_CANCELLED, Statements_model::STATUS_DRAFT])
                           && has_permission('statements', '', 'edit')
                           && $statement->duedate
                           && is_statements_overdue_reminders_enabled()) { ?>
                                    <li>
                                        <?php if ($statement->cancel_overdue_reminders == 1) { ?>
                                        <a
                                            href="<?php echo admin_url('statements/resume_overdue_reminders/' . $statement->id); ?>"><?php echo _l('resume_overdue_reminders'); ?></a>
                                        <?php } else { ?>
                                        <a
                                            href="<?php echo admin_url('statements/pause_overdue_reminders/' . $statement->id); ?>"><?php echo _l('pause_overdue_reminders'); ?></a>
                                        <?php } ?>
                                    </li>
                                    <?php } ?>
                                    <?php
                           if ((get_option('delete_only_on_last_statement') == 1 && is_last_statement($statement->id)) || (get_option('delete_only_on_last_statement') == 0)) { ?>
                                    <?php if (has_permission('statements', '', 'delete')) { ?>
                                    <li data-toggle="tooltip" data-title="<?php echo _l('delete_statement_tooltip'); ?>">
                                        <a href="<?php echo admin_url('statements/delete/' . $statement->id); ?>"
                                            class="text-danger delete-text _delete"><?php echo _l('delete_statement'); ?></a>
                                    </li>
                                    <?php } ?>
                                    <?php } ?>
                                    <?php hooks()->do_action('after_statement_preview_more_menu'); ?>
                                </ul>
                            </div>
                            <?php if (has_permission('payments', '', 'create') && abs($statement->total) > 0) { ?>
                            <a href="#" onclick="record_remittance(<?php echo $statement->id; ?>); return false;" class="mleft10 pull-right btn btn-success<?php if ($statement->status == Statements_model::STATUS_PAID || $statement->status == Statements_model::STATUS_CANCELLED) {
                               echo ' disabled';
                           } ?>">
                                <i class="fa fa-plus-square"></i> <?php echo _l('payment'); ?></a>
                            <?php } ?>
                    </div>
                </div>
                <?php
                  if (is_statement_overdue($statement)) { ?>
                <div class="col-md-12">
                    <p class="text-danger tw-mt-2.5 tw-mb-0">
                        <?php echo _l('statement_is_overdue', get_total_days_overdue($statement->duedate)); ?>
                    </p>
                </div>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-separator" />
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="tab_statement">
                    <?php if ($statement->status == Statements_model::STATUS_CANCELLED && $statement->recurring > 0) { ?>
                    <div class="alert alert-info">
                        Recurring statement with status Cancelled <b>is still ongoing recurring statement</b>. If you want
                        to stop this recurring statement you should update the statement recurring field to <b>No</b>.
                    </div>
                    <?php } ?>
                    <?php $this->load->view('admin/statements/statement_preview_html'); ?>
                </div>
                <?php if (count($statement->payments) > 0) { ?>
                <div class="tab-pane" role="tabpanel" id="statement_payments_received">
                    <?php include_once(module_views_path('statements','admin/statements/statement_payments_table.php')); ?>
                </div>
                <?php } ?>
                <?php if (count($applied_credits) > 0) { ?>
                <div class="tab-pane" role="tabpanel" id="statement_applied_credits">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover no-mtop">
                            <thead>
                                <th><span class="bold"><?php echo _l('credit_note'); ?> #</span></th>
                                <th><span class="bold"><?php echo _l('credit_date'); ?></span></th>
                                <th><span class="bold"><?php echo _l('credit_amount'); ?></span></th>
                            </thead>
                            <tbody>
                                <?php foreach ($applied_credits as $credit) { ?>
                                <tr>
                                    <td>
                                        <a
                                            href="<?php echo admin_url('credit_notes/list_credit_notes/' . $credit['credit_id']); ?>"><?php echo format_credit_note_number($credit['credit_id']); ?></a>
                                    </td>
                                    <td><?php echo _d($credit['date']); ?></td>
                                    <td><?php echo app_format_money($credit['amount'], $statement->currency_name) ?>
                                        <?php if (has_permission('credit_notes', '', 'delete')) { ?>
                                        <a href="<?php echo admin_url('credit_notes/delete_statement_applied_credit/' . $credit['id'] . '/' . $credit['credit_id'] . '/' . $statement->id); ?>"
                                            class="pull-right text-danger _delete"><i class="fa fa-trash"></i></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php } ?>
                <div role="tabpanel" class="tab-pane" id="tab_tasks">
                    <?php init_relation_tasks_table(['data-new-rel-id' => $statement->id, 'data-new-rel-type' => 'statement']); ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="tab_reminders">
                    <a href="#" class="btn btn-default" data-toggle="modal"
                        data-target=".reminder-modal-statement-<?php echo $statement->id; ?>"><i
                            class="fa-regular fa-bell"></i>
                        <?php echo _l('statement_set_reminder_title'); ?></a>
                    <hr />
                    <?php render_datatable([ _l('reminder_description'), _l('reminder_date'), _l('reminder_staff'), _l('reminder_is_notified')], 'reminders'); ?>
                    <?php $this->load->view('admin/includes/modals/reminder', ['id' => $statement->id, 'name' => 'statement', 'members' => $members, 'reminder_title' => _l('statement_set_reminder_title')]); ?>
                </div>
                <?php if (count($statement_recurring_statements) > 0 || $statement->recurring != 0) { ?>
                <div role="tabpanel" class="tab-pane" id="tab_child_statements">
                    <?php if (count($statement_recurring_statements)) { ?>
                    <p class="tw-text-lg tw-font-medium">
                        <?php echo _l('statement_add_edit_recurring_statements_from_statement'); ?></p>
                    <ul class="list-group">
                        <?php foreach ($statement_recurring_statements as $recurring) { ?>
                        <li class="list-group-item">
                            <a href="<?php echo admin_url('statements/list_statements/' . $recurring->id); ?>"
                                class="tw-font-semibold"
                                onclick="init_statement(<?php echo $recurring->id; ?>); return false;"
                                target="_blank"><?php echo format_statement_number($recurring->id); ?>
                                <span
                                    class="pull-right bold"><?php echo app_format_money($recurring->total, $recurring->currency_name); ?></span>
                            </a>
                            <br />
                            <span class="inline-block tw-mt-1">
                                <?php echo '<span class="bold">' . _d($recurring->date) . '</span>'; ?><br />
                                <?php echo format_statement_status($recurring->status, '', false); ?>
                            </span>
                        </li>
                        <?php } ?>
                    </ul>
                    <?php } else { ?>
                    <p class="bold"><?php echo _l('no_child_found', _l('statements')); ?></p>
                    <?php } ?>
                </div>
                <?php } ?>
                <div role="tabpanel" class="tab-pane ptop10" id="tab_emails_tracking">
                    <?php
                  $this->load->view(
                               'admin/includes/emails_tracking',
                               [
                     'tracked_emails' => get_tracked_emails($statement->id, 'statement'), ]
                           );
                  ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="tab_notes">
                    <?php echo form_open(admin_url('statements/add_note/' . $statement->id), ['id' => 'sales-notes', 'class' => 'statement-notes-form']); ?>
                    <?php echo render_textarea('description'); ?>
                    <div class="text-right">
                        <button type="submit"
                            class="btn btn-primary mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
                    </div>
                    <?php echo form_close(); ?>
                    <hr />
                    <div class="mtop20" id="sales_notes_area"></div>
                </div>
                <div role="tabpanel" class="tab-pane ptop10" id="tab_activity">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="activity-feed">
                                <?php foreach ($activity as $activity) {
                      $_custom_data = false; ?>
                                <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                                    <div class="date">
                                        <span class="text-has-action" data-toggle="tooltip"
                                            data-title="<?php echo _dt($activity['date']); ?>">
                                            <?php echo time_ago($activity['date']); ?>
                                        </span>
                                    </div>
                                    <div class="text">
                                        <?php if (is_numeric($activity['staffid']) && $activity['staffid'] != 0) { ?>
                                        <a href="<?php echo admin_url('profile/' . $activity['staffid']); ?>">
                                            <?php echo staff_profile_image($activity['staffid'], ['staff-profile-xs-image pull-left mright5']);
                                 ?>
                                        </a>
                                        <?php } ?>
                                        <?php
                                 $additional_data = '';
                      if (!empty($activity['additional_data'])) {
                          $additional_data = unserialize($activity['additional_data']);
                          $i               = 0;
                          foreach ($additional_data as $data) {
                              if (strpos($data, '<original_status>') !== false) {
                                  $original_status     = get_string_between($data, '<original_status>', '</original_status>');
                                  $additional_data[$i] = format_statement_status($original_status, '', false);
                              } elseif (strpos($data, '<new_status>') !== false) {
                                  $new_status          = get_string_between($data, '<new_status>', '</new_status>');
                                  $additional_data[$i] = format_statement_status($new_status, '', false);
                              } elseif (strpos($data, '<custom_data>') !== false) {
                                  $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                  unset($additional_data[$i]);
                              }
                              $i++;
                          }
                      }
                      $_formatted_activity = _l($activity['description'], $additional_data);
                      if ($_custom_data !== false) {
                          $_formatted_activity .= ' - ' . $_custom_data;
                      }
                      if (!empty($activity['full_name'])) {
                          $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                      }
                      echo $_formatted_activity;
                      if (is_admin()) {
                          echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity(' . $activity['id'] . '); return false;"><i class="fa fa-remove"></i></a>';
                      } ?>
                                    </div>
                                </div>
                                <?php
                  } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
                    <?php
                  $views_activity = get_views_tracking('statement', $statement->id);
                  if (count($views_activity) === 0) {
                      echo '<h4 class="tw-m-0 tw-text-base tw-font-medium tw-text-neutral-500">' . _l('not_viewed_yet', _l('statement_lowercase')) . '</h4>';
                  }
                  foreach ($views_activity as $activity) { ?>
                    <p class="text-success no-margin">
                        <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
                    </p>
                    <p class="text-muted">
                        <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
                    </p>
                    <hr />
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/statements/statement_send_to_client'); ?>
<?php $this->load->view('admin/credit_notes/apply_statement_credits'); ?>
<?php $this->load->view('admin/credit_notes/statement_create_credit_note_confirm'); ?>
<script>
init_items_sortable(true);
init_btn_with_tooltips();
init_datepicker();
init_selectpicker();
init_form_reminder();
init_tabs_scrollable();
<?php if ($record_payment) { ?>
record_payment(<?php echo $statement->id; ?>);
<?php } elseif ($send_later) { ?>
schedule_statement_send(<?php echo $statement->id; ?>);
<?php } ?>
</script>
<?php hooks()->do_action('after_statement_preview_template_rendered', $statement); ?>