<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="<?php if (!isset($statement) || (isset($statement) && count($statements_to_merge) == 0 && (isset($statement) && !isset($statement_from_project) && count($expenses_to_bill) == 0 || $statement->status == Statements_model::STATUS_CANCELLED))) {
    echo ' hide';
} ?>" id="statement_top_info">
    <div class="alert alert-info">
        <div class="row">
            <div id="merge" class="col-md-6">
                <?php
              if (isset($statement)) {
                  $this->load->view('admin/statements/merge_statement', ['statements_to_merge' => $statements_to_merge]);
              }
            ?>
            </div>
            <!--  When invoicing from project area the expenses are not visible here because you can select to bill expenses while trying to statement project -->
            <?php if (!isset($statement_from_project)) { ?>
            <div id="expenses_to_bill" class="col-md-6">
                <?php if (isset($statement) && $statement->status != Statements_model::STATUS_CANCELLED) {
                $this->load->view('admin/statements/bill_expenses', ['expenses_to_bill' => $expenses_to_bill]);
            } ?>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<div class="panel_s statement accounting-template">
    <div class="additional"></div>
    <div class="panel-body">
        <?php hooks()->do_action('before_render_statement_template', $statement ?? null); ?>
        <?php if (isset($statement)) {
                echo form_hidden('merge_current_statement', $statement->id);
            } ?>
        <div class="row">
            <div class="col-md-6">
                <div class="f_client_id">
                    <div class="form-group select-placeholder">
                        <label for="clientid" class="control-label"><?php echo _l('statement_select_customer'); ?></label>
                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if (isset($statement) && empty($statement->clientid)) {
                echo ' customer-removed';
            } ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php $selected = (isset($statement) ? $statement->clientid : '');
                 if ($selected == '') {
                     $selected = (isset($customer_id) ? $customer_id: '');
                 }
                 if ($selected != '') {
                     $rel_data = get_relation_data('customer', $selected);
                     $rel_val  = get_relation_values($rel_data, 'customer');
                     echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                 } ?>
                        </select>
                    </div>
                </div>
                <?php
            if (!isset($statement_from_project)) { ?>
                <div class="form-group select-placeholder projects-wrapper<?php if ((!isset($statement)) || (isset($statement) && !customer_has_projects($statement->clientid))) {
                echo (isset($customer_id) && (!isset($project_id) || !$project_id)) ?  ' hide' : '';
            } ?>">
                    <label for="project_id"><?php echo _l('project'); ?></label>
                    <div id="project_ajax_search_wrapper">
                        <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true"
                            data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php
                    if (!isset($project_id)) {
                        $project_id = '';
                    }
                    if (isset($statement) && $statement->project_id != 0) {
                        $project_id = $statement->project_id;
                    }
                    if ($project_id) {
                        echo '<option value="' . $project_id . '" selected>' . get_project_name_by_id($project_id) . '</option>';
                    }
                   ?>
                        </select>
                    </div>
                </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-12">
                        <hr class="hr-10" />
                        <a href="#" class="edit_shipping_billing_info" data-toggle="modal"
                            data-target="#billing_and_shipping_details"><i class="fa-regular fa-pen-to-square"></i></a>
                        <?php include_once(module_views_path ('statements','admin/statements/billing_and_shipping_template.php')); ?>
                    </div>
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('statement_bill_to'); ?></p>
                        <address>
                            <span class="billing_street">
                                <?php $billing_street = (isset($statement) ? $statement->billing_street : '--'); ?>
                                <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                                <?php echo $billing_street; ?></span><br>
                            <span class="billing_city">
                                <?php $billing_city = (isset($statement) ? $statement->billing_city : '--'); ?>
                                <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                                <?php echo $billing_city; ?></span>,
                            <span class="billing_state">
                                <?php $billing_state = (isset($statement) ? $statement->billing_state : '--'); ?>
                                <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                                <?php echo $billing_state; ?></span>
                            <br />
                            <span class="billing_country">
                                <?php $billing_country = (isset($statement) ? get_country_short_name($statement->billing_country) : '--'); ?>
                                <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                                <?php echo $billing_country; ?></span>,
                            <span class="billing_zip">
                                <?php $billing_zip = (isset($statement) ? $statement->billing_zip : '--'); ?>
                                <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                                <?php echo $billing_zip; ?></span>
                        </address>
                    </div>
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('ship_to'); ?></p>
                        <address>
                            <span class="shipping_street">
                                <?php $shipping_street = (isset($statement) ? $statement->shipping_street : '--'); ?>
                                <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                                <?php echo $shipping_street; ?></span><br>
                            <span class="shipping_city">
                                <?php $shipping_city = (isset($statement) ? $statement->shipping_city : '--'); ?>
                                <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                                <?php echo $shipping_city; ?></span>,
                            <span class="shipping_state">
                                <?php $shipping_state = (isset($statement) ? $statement->shipping_state : '--'); ?>
                                <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                                <?php echo $shipping_state; ?></span>
                            <br />
                            <span class="shipping_country">
                                <?php $shipping_country = (isset($statement) ? get_country_short_name($statement->shipping_country) : '--'); ?>
                                <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                                <?php echo $shipping_country; ?></span>,
                            <span class="shipping_zip">
                                <?php $shipping_zip = (isset($statement) ? $statement->shipping_zip : '--'); ?>
                                <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                                <?php echo $shipping_zip; ?></span>
                        </address>
                    </div>
                </div>
                <?php
               $next_statement_number = get_option('next_statement_number');
               $format              = get_option('statement_number_format');

               if (isset($statement)) {
                   $format = $statement->number_format;
               }

               $prefix = get_option('statement_prefix');

               if ($format == 1) {
                   $__number = $next_statement_number;
                   if (isset($statement)) {
                       $__number = $statement->number;
                       $prefix   = '<span id="prefix">' . $statement->prefix . '</span>';
                   }
               } elseif ($format == 2) {
                   if (isset($statement)) {
                       $__number = $statement->number;
                       $prefix   = $statement->prefix;
                       $prefix   = '<span id="prefix">' . $prefix . '</span><span id="prefix_year">' . date('Y', strtotime($statement->date)) . '</span>/';
                   } else {
                       $__number = $next_statement_number;
                       $prefix   = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
                   }
               } elseif ($format == 3) {
                   if (isset($statement)) {
                       $yy       = date('y', strtotime($statement->date));
                       $__number = $statement->number;
                       $prefix   = '<span id="prefix">' . $statement->prefix . '</span>';
                   } else {
                       $yy       = date('y');
                       $__number = $next_statement_number;
                   }
               } elseif ($format == 4) {
                   if (isset($statement)) {
                       $yyyy     = date('Y', strtotime($statement->date));
                       $mm       = date('m', strtotime($statement->date));
                       $__number = $statement->number;
                       $prefix   = '<span id="prefix">' . $statement->prefix . '</span>';
                   } else {
                       $yyyy     = date('Y');
                       $mm       = date('m');
                       $__number = $next_statement_number;
                   }
               }

               $_is_draft            = (isset($statement) && $statement->status == Statements_model::STATUS_DRAFT) ? true : false;
               $_statement_number      = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
               $isedit               = isset($statement) ? 'true' : 'false';
               $data_original_number = isset($statement) ? $statement->number : 'false';

               ?>
                <div class="form-group">
                    <label for="number">
                        <?php echo _l('statement_add_edit_number'); ?>
                        <i class="fa-regular fa-circle-question" data-toggle="tooltip"
                            data-title="<?php echo _l('statement_number_not_applied_on_draft') ?>"
                            data-placement="top"></i>
                    </label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <?php if (isset($statement)) { ?>
                            <a href="#" onclick="return false;" data-toggle="popover"
                                data-container='._transaction_form' data-html="true"
                                data-content="<label class='control-label'><?php echo _l('settings_sales_statement_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $statement->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('statements/update_number_settings/' . $statement->id); ?>' class='btn btn-primary btn-block mtop15'><?php echo _l('submit'); ?></button>">
                                <i class="fa fa-cog"></i>
                            </a>
                            <?php }
                    echo $prefix;
                  ?>
                        </span>
                        <input type="text" name="number" class="form-control"
                            value="<?php echo ($_is_draft) ? 'DRAFT' : $_statement_number; ?>"
                            data-isedit="<?php echo $isedit; ?>"
                            data-original-number="<?php echo $data_original_number; ?>"
                            <?php echo ($_is_draft) ? 'disabled' : '' ?>>
                        <?php if ($format == 3) { ?>
                        <span class="input-group-addon">
                            <span id="prefix_year" class="format-n-yy"><?php echo $yy; ?></span>
                        </span>
                        <?php } elseif ($format == 4) { ?>
                        <span class="input-group-addon">
                            <span id="prefix_month" class="format-mm-yyyy"><?php echo $mm; ?></span>
                            /
                            <span id="prefix_year" class="format-mm-yyyy"><?php echo $yyyy; ?></span>
                        </span>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php $value = (isset($statement) ? _d($statement->date) : _d(date('Y-m-d')));
                  $date_attrs        = [];
                  if (isset($statement) && $statement->recurring > 0 && $statement->last_recurring_date != null) {
                      $date_attrs['disabled'] = true;
                  }
                  ?>
                        <?php echo render_date_input('date', 'statement_add_edit_date', $value, $date_attrs); ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                  $value = '';
                  if (isset($statement)) {
                      $value = _d($statement->duedate);
                  } else {
                      if (get_option('statement_due_after') != 0) {
                          $value = _d(date('Y-m-d', strtotime('+' . get_option('statement_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                      }
                  }
                   ?>
                        <?php echo render_date_input('duedate', 'statement_add_edit_duedate', $value); ?>
                    </div>
                </div>
                <?php if (is_statements_overdue_reminders_enabled()) { ?>
                <div class="form-group">
                    <div class="checkbox checkbox-danger">
                        <input type="checkbox" <?php if (isset($statement) && $statement->cancel_overdue_reminders == 1) {
                       echo 'checked';
                   } ?> id="cancel_overdue_reminders" name="cancel_overdue_reminders">
                        <label
                            for="cancel_overdue_reminders"><?php echo _l('cancel_overdue_reminders_statement') ?></label>
                    </div>
                </div>
                <?php } ?>
                <?php $rel_id = (isset($statement) ? $statement->id : false); ?>
                <?php
                  if (isset($custom_fields_rel_transfer)) {
                      $rel_id = $custom_fields_rel_transfer;
                  }
               ?>
                <?php echo render_custom_fields('statement', $rel_id); ?>
            </div>
            <div class="col-md-6">
                <div class="tw-ml-3">
                    <div class="form-group">
                        <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                            <?php echo _l('tags'); ?></label>
                        <input type="text" class="tagsinput" id="tags" name="tags"
                            value="<?php echo(isset($statement) ? prep_tags_input(get_tags_in($statement->id, 'statement')) : ''); ?>"
                            data-role="tagsinput">
                    </div>
                    <div class="form-group mbot15<?= count($payment_modes) > 0 ? ' select-placeholder' : ''; ?>">
                        <label for="allowed_payment_modes"
                            class="control-label"><?php echo _l('statement_add_edit_allowed_payment_modes'); ?></label>
                        <br />
                        <?php if (count($payment_modes) > 0) { ?>
                        <select class="selectpicker"
                            data-toggle="<?php echo $this->input->get('allowed_payment_modes'); ?>"
                            name="allowed_payment_modes[]" data-actions-box="true" multiple="true" data-width="100%"
                            data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php foreach ($payment_modes as $mode) {
                   $selected = '';
                   if (isset($statement)) {
                       if ($statement->allowed_payment_modes) {
                           $inv_modes = unserialize($statement->allowed_payment_modes);
                           if (is_array($inv_modes)) {
                               foreach ($inv_modes as $_allowed_payment_mode) {
                                   if ($_allowed_payment_mode == $mode['id']) {
                                       $selected = ' selected';
                                   }
                               }
                           }
                       }
                   } else {
                       if ($mode['selected_by_default'] == 1) {
                           $selected = ' selected';
                       }
                   } ?>
                            <option value="<?php echo $mode['id']; ?>" <?php echo $selected; ?>>
                                <?php echo $mode['name']; ?></option>
                            <?php
               } ?>
                        </select>
                        <?php } else { ?>
                        <p class="tw-text-neutral-500">
                            <?php echo _l('statement_add_edit_no_payment_modes_found'); ?>
                        </p>
                        <a class="btn btn-primary btn-sm" href="<?php echo admin_url('paymentmodes'); ?>">
                            <?php echo _l('new_payment_mode'); ?>
                        </a>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                        $currency_attr = ['disabled' => true, 'data-show-subtext' => true];
                        $currency_attr = apply_filters_deprecated('statement_currency_disabled', [$currency_attr], '2.3.0', 'statement_currency_attributes');

                        foreach ($currencies as $currency) {
                            if ($currency['isdefault'] == 1) {
                                $currency_attr['data-base'] = $currency['id'];
                            }
                            if (isset($statement)) {
                                if ($currency['id'] == $statement->currency) {
                                    $selected = $currency['id'];
                                }
                            } else {
                                if ($currency['isdefault'] == 1) {
                                    $selected = $currency['id'];
                                }
                            }
                        }
                        $currency_attr = hooks()->apply_filters('statement_currency_attributes', $currency_attr);
                        ?>
                            <?php echo render_select('currency', $currencies, ['id', 'name', 'symbol'], 'statement_add_edit_currency', $selected, $currency_attr); ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                        $i        = 0;
                        $selected = '';
                        foreach ($staff as $member) {
                            if (isset($statement)) {
                                if ($statement->sale_agent == $member['staffid']) {
                                    $selected = $member['staffid'];
                                }
                            }
                            $i++;
                        }
                        echo render_select('sale_agent', $staff, ['staffid', ['firstname', 'lastname']], 'sale_agent_string', $selected);
                        ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group select-placeholder"
                                <?php if (isset($statement) && !empty($statement->is_recurring_from)) { ?>
                                data-toggle="tooltip"
                                data-title="<?php echo _l('create_recurring_from_child_error_message', [_l('statement_lowercase'), _l('statement_lowercase'), _l('statement_lowercase')]); ?>"
                                <?php } ?>>
                                <label for="recurring" class="control-label">
                                    <?php echo _l('statement_add_edit_recurring'); ?>
                                </label>
                                <select class="selectpicker" data-width="100%" name="recurring"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" <?php
                        // The problem is that this statement was generated from previous recurring statement
                        // Then this new statement you set it as recurring but the next statement date was still taken from the previous statement.
                        if (isset($statement) && !empty($statement->is_recurring_from)) {
                            echo 'disabled';
                        } ?>>
                                    <?php for ($i = 0; $i <= 12; $i++) { ?>
                                    <?php
                              $selected = '';
                              if (isset($statement)) {
                                  if ($statement->custom_recurring == 0) {
                                      if ($statement->recurring == $i) {
                                          $selected = 'selected';
                                      }
                                  }
                              }
                              if ($i == 0) {
                                  $reccuring_string = _l('statement_add_edit_recurring_no');
                              } elseif ($i == 1) {
                                  $reccuring_string = _l('statement_add_edit_recurring_month', $i);
                              } else {
                                  $reccuring_string = _l('statement_add_edit_recurring_months', $i);
                              }
                              ?>
                                    <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                        <?php echo $reccuring_string; ?></option>
                                    <?php } ?>
                                    <option value="custom" <?php if (isset($statement) && $statement->recurring != 0 && $statement->custom_recurring == 1) {
                                  echo 'selected';
                              } ?>><?php echo _l('recurring_custom'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group select-placeholder">
                                <label for="discount_type"
                                    class="control-label"><?php echo _l('discount_type'); ?></label>
                                <select name="discount_type" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value="" selected><?php echo _l('no_discount'); ?></option>
                                    <option value="before_tax" <?php
                              if (isset($statement)) {
                                  if ($statement->discount_type == 'before_tax') {
                                      echo 'selected';
                                  }
                              } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                                    <option value="after_tax" <?php if (isset($statement)) {
                                  if ($statement->discount_type == 'after_tax') {
                                      echo 'selected';
                                  }
                              } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="recurring_custom <?php if ((isset($statement) && $statement->custom_recurring != 1) || (!isset($statement))) {
                                  echo 'hide';
                              } ?>">
                            <div class="col-md-6">
                                <?php $value = (isset($statement) && $statement->custom_recurring == 1 ? $statement->recurring : 1); ?>
                                <?php echo render_input('repeat_every_custom', '', $value, 'number', ['min' => 1]); ?>
                            </div>
                            <div class="col-md-6">
                                <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker"
                                    data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value="day" <?php if (isset($statement) && $statement->custom_recurring == 1 && $statement->recurring_type == 'day') {
                                  echo 'selected';
                              } ?>><?php echo _l('statement_recurring_days'); ?></option>
                                    <option value="week" <?php if (isset($statement) && $statement->custom_recurring == 1 && $statement->recurring_type == 'week') {
                                  echo 'selected';
                              } ?>><?php echo _l('statement_recurring_weeks'); ?></option>
                                    <option value="month" <?php if (isset($statement) && $statement->custom_recurring == 1 && $statement->recurring_type == 'month') {
                                  echo 'selected';
                              } ?>><?php echo _l('statement_recurring_months'); ?></option>
                                    <option value="year" <?php if (isset($statement) && $statement->custom_recurring == 1 && $statement->recurring_type == 'year') {
                                  echo 'selected';
                              } ?>><?php echo _l('statement_recurring_years'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div id="cycles_wrapper" class="<?php if (!isset($statement) || (isset($statement) && $statement->recurring == 0)) {
                                  echo ' hide';
                              }?>">
                            <div class="col-md-12">
                                <?php $value = (isset($statement) ? $statement->cycles : 0); ?>
                                <div class="form-group recurring-cycles">
                                    <label for="cycles"><?php echo _l('recurring_total_cycles'); ?>
                                        <?php if (isset($statement) && $statement->total_cycles > 0) {
                                  echo '<small>' . _l('cycles_passed', $statement->total_cycles) . '</small>';
                              }
                            ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" <?php if ($value == 0) {
                                echo ' disabled';
                            } ?> name="cycles" id="cycles" value="<?php echo $value; ?>" <?php if (isset($statement) && $statement->total_cycles > 0) {
                                echo 'min="' . ($statement->total_cycles) . '"';
                            } ?>>
                                        <div class="input-group-addon">
                                            <div class="checkbox">
                                                <input type="checkbox" <?php if ($value == 0) {
                                echo ' checked';
                            } ?> id="unlimited_cycles">
                                                <label
                                                    for="unlimited_cycles"><?php echo _l('cycles_infinity'); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $value = (isset($statement) ? $statement->adminnote : ''); ?>
                    <?php echo render_textarea('adminnote', 'statement_add_edit_admin_note', $value); ?>

                </div>
            </div>
        </div>
    </div>

    <hr class="hr-panel-separator" />

    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <?php $this->load->view('admin/statement_items/item_select'); ?>
            </div>
            <?php if (!isset($statement_from_project) && isset($billable_tasks)) { ?>
            <div class="col-md-3">
                <div class="form-group select-placeholder input-group-select form-group-select-task_select popover-250">
                    <div class="input-group input-group-select">
                        <select name="task_select" data-live-search="true" id="task_select"
                            class="selectpicker no-margin _select_input_group" data-width="100%"
                            data-none-selected-text="<?php echo _l('bill_tasks'); ?>">
                            <option value=""></option>
                            <?php foreach ($billable_tasks as $task_billable) { ?>
                            <option value="<?php echo $task_billable['id']; ?>"
                                <?php if ($task_billable['started_timers'] == true) { ?>disabled class="text-danger"
                                data-subtext="<?php echo _l('statement_task_billable_timers_found'); ?>" <?php } else {
                                $task_rel_data  = get_relation_data($task_billable['rel_type'], $task_billable['rel_id']);
                                $task_rel_value = get_relation_values($task_rel_data, $task_billable['rel_type']); ?>
                                data-subtext="<?php echo $task_billable['rel_type'] == 'project' ? '' : $task_rel_value['name']; ?>" <?php
                            } ?>><?php echo $task_billable['name']; ?></option>
                            <?php } ?>
                        </select>
                        <div class="input-group-addon input-group-addon-bill-tasks-help">
                            <?php
                    if (isset($statement) && !empty($statement->project_id)) {
                        $help_text = _l('showing_billable_tasks_from_project') . ' ' . get_project_name_by_id($statement->project_id);
                    } else {
                        $help_text = _l('statement_task_item_project_tasks_not_included');
                    }
                                echo '<span class="pointer popover-invoker" data-container=".form-group-select-task_select"
                      data-trigger="click" data-placement="top" data-toggle="popover" data-content="' . $help_text . '">
                      <i class="fa-regular fa-circle-question"></i></span>'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                            } ?>
            <div class="col-md-<?php if (!isset($statement_from_project)) {
                                echo 5;
                            } else {
                                echo 8;
                            } ?> text-right show_quantity_as_wrapper">
                <div class="mtop10">
                    <span><?php echo _l('show_quantity_as'); ?> </span>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" value="1" id="sq_1" name="show_quantity_as"
                            data-text="<?php echo _l('statement_table_quantity_heading'); ?>" <?php if (isset($statement) && $statement->show_quantity_as == 1) {
                                echo 'checked';
                            } elseif (!isset($hours_quantity) && !isset($qty_hrs_quantity)) {
                                echo'checked';
                            } ?>>
                        <label for="sq_1"><?php echo _l('quantity_as_qty'); ?></label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" value="2" id="sq_2" name="show_quantity_as"
                            data-text="<?php echo _l('statement_table_hours_heading'); ?>" <?php if (isset($statement) && $statement->show_quantity_as == 2 || isset($hours_quantity)) {
                                echo 'checked';
                            } ?>>
                        <label for="sq_2"><?php echo _l('quantity_as_hours'); ?></label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" value="3" id="sq_3" name="show_quantity_as"
                            data-text="<?php echo _l('statement_table_quantity_heading'); ?>/<?php echo _l('statement_table_hours_heading'); ?>" <?php if (isset($statement) && $statement->show_quantity_as == 3 || isset($qty_hrs_quantity)) {
                                echo 'checked';
                            } ?>>
                        <label
                            for="sq_3"><?php echo _l('statement_table_quantity_heading'); ?>/<?php echo _l('statement_table_hours_heading'); ?></label>
                    </div>
                </div>
            </div>
        </div>
        <?php if (isset($statement_from_project)) {
                                echo '<hr class="no-mtop" />';
                            } ?>
        <div class="table-responsive s_table">
            <table class="table statement-items-table items table-main-statement-edit has-calculations no-mtop">
                <thead>
                    <tr>
                        <th></th>
                        <th width="20%" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1"
                                aria-hidden="true" data-toggle="tooltip"
                                data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i>
                            <?php echo _l('statement_table_item_heading'); ?></th>
                        <th width="25%" align="left"><?php echo _l('statement_table_item_description'); ?></th>
                        <?php
                  $custom_fields = get_custom_fields('items');
                  foreach ($custom_fields as $cf) {
                      echo '<th width="15%" align="left" class="custom_field">' . $cf['name'] . '</th>';
                  }
                     $qty_heading = _l('statement_table_quantity_heading');
                     if (isset($statement) && $statement->show_quantity_as == 2 || isset($hours_quantity)) {
                         $qty_heading = _l('statement_table_hours_heading');
                     } elseif (isset($statement) && $statement->show_quantity_as == 3) {
                         $qty_heading = _l('statement_table_quantity_heading') . '/' . _l('statement_table_hours_heading');
                     }
                     ?>
                        <th width="10%" align="right" class="qty"><?php echo $qty_heading; ?></th>
                        <th width="15%" align="right"><?php echo _l('statement_table_rate_heading'); ?></th>
                        <th width="20%" align="right"><?php echo _l('statement_table_tax_heading'); ?></th>
                        <th width="10%" align="right"><?php echo _l('statement_table_amount_heading'); ?></th>
                        <th align="center"><i class="fa fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="main">
                        <td></td>
                        <td>
                            <textarea name="description" class="form-control" rows="4"
                                placeholder="<?php echo _l('item_description_placeholder'); ?>"></textarea>
                        </td>
                        <td>
                            <textarea name="long_description" rows="4" class="form-control"
                                placeholder="<?php echo _l('item_long_description_placeholder'); ?>"></textarea>
                        </td>
                        <?php echo render_custom_fields_items_table_add_edit_preview(); ?>
                        <td>
                            <input type="number" name="quantity" min="0" value="1" class="form-control"
                                placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
                            <input type="text" placeholder="<?php echo _l('unit'); ?>" data-toggle="tooltip"
                                data-title="e.q kg, lots, packs" name="unit"
                                class="form-control input-transparent text-right">
                        </td>
                        <td>
                            <input type="number" name="rate" class="form-control"
                                placeholder="<?php echo _l('item_rate_placeholder'); ?>">
                        </td>
                        <td>
                            <?php
                        $default_tax = unserialize(get_option('default_tax'));
                        $select      = '<select class="selectpicker display-block tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . _l('no_tax') . '">';
                      //  $select .= '<option value=""'.(count($default_tax) == 0 ? ' selected' : '').'>'._l('no_tax').'</option>';
                        foreach ($taxes as $tax) {
                            $selected = '';
                            if (is_array($default_tax)) {
                                if (in_array($tax['name'] . '|' . $tax['taxrate'], $default_tax)) {
                                    $selected = ' selected ';
                                }
                            }
                            $select .= '<option value="' . $tax['name'] . '|' . $tax['taxrate'] . '"' . $selected . 'data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
                        }
                        $select .= '</select>';
                        echo $select;
                        ?>
                        </td>
                        <td></td>
                        <td>
                            <?php
                        $new_item = 'undefined';
                        if (isset($statement)) {
                            $new_item = true;
                        }
                        ?>
                            <button type="button"
                                onclick="add_item_to_table('undefined','undefined',<?php echo $new_item; ?>); return false;"
                                class="btn pull-right btn-primary"><i class="fa fa-check"></i></button>
                        </td>
                    </tr>
                    <?php if (isset($statement) || isset($add_items)) {
                            $i               = 1;
                            $items_indicator = 'newitems';
                            if (isset($statement)) {
                                $add_items       = $statement->items;
                                $items_indicator = 'items';
                            }
                            foreach ($add_items as $item) {
                                $manual    = false;
                                $table_row = '<tr class="sortable item">';
                                $table_row .= '<td class="dragger">';
                                if (!is_numeric($item['qty'])) {
                                    $item['qty'] = 1;
                                }
                                $statement_item_taxes = get_statement_item_taxes($item['id']);
                                // passed like string
                                if ($item['id'] == 0) {
                                    $statement_item_taxes = $item['taxname'];
                                    $manual             = true;
                                }
                                $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                                $amount = $item['rate'] * $item['qty'];
                                $amount = app_format_number($amount);
                                // order input
                                $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                                $table_row .= '</td>';
                                $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea></td>';
                                $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea></td>';

                                $table_row .= render_custom_fields_items_table_in($item, $items_indicator . '[' . $i . ']');

                                $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';

                                $unit_placeholder = '';
                                if (!$item['unit']) {
                                    $unit_placeholder = _l('unit');
                                    $item['unit']     = '';
                                }

                                $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control input-transparent text-right" value="' . $item['unit'] . '">';

                                $table_row .= '</td>';
                                $table_row .= '<td class="rate"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control"></td>';
                                $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $statement_item_taxes, 'statement', $item['id'], true, $manual) . '</td>';
                                $table_row .= '<td class="amount" align="right">' . $amount . '</td>';
                                $table_row .= '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                                if (isset($item['task_id'])) {
                                    if (!is_array($item['task_id'])) {
                                        $table_row .= form_hidden('billed_tasks[' . $i . '][]', $item['task_id']);
                                    } else {
                                        foreach ($item['task_id'] as $task_id) {
                                            $table_row .= form_hidden('billed_tasks[' . $i . '][]', $task_id);
                                        }
                                    }
                                } elseif (isset($item['expense_id'])) {
                                    $table_row .= form_hidden('billed_expenses[' . $i . '][]', $item['expense_id']);
                                }
                                $table_row .= '</tr>';
                                echo $table_row;
                                $i++;
                            }
                        }
                  ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-8 col-md-offset-4">
            <table class="table text-right">
                <tbody>
                    <tr id="subtotal">
                        <td>
                            <span class="bold tw-text-neutral-700"><?php echo _l('statement_subtotal'); ?> :</span>
                        </td>
                        <td class="subtotal">
                        </td>
                    </tr>
                    <tr id="discount_area">
                        <td>
                            <div class="row">
                                <div class="col-md-7">
                                    <span class="bold tw-text-neutral-700">
                                        <?php echo _l('statement_discount'); ?>
                                    </span>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group" id="discount-total">

                                        <input type="number"
                                            value="<?php echo(isset($statement) ? $statement->discount_percent : 0); ?>"
                                            class="form-control pull-left input-discount-percent<?php if (isset($statement) && !is_sale_discount($statement, 'percent') && is_sale_discount_applied($statement)) {
                      echo ' hide';
                  } ?>" min="0" max="100" name="discount_percent">

                                        <input type="number" data-toggle="tooltip"
                                            data-title="<?php echo _l('numbers_not_formatted_while_editing'); ?>"
                                            value="<?php echo(isset($statement) ? $statement->discount_total : 0); ?>"
                                            class="form-control pull-left input-discount-fixed<?php if (!isset($statement) || (isset($statement) && !is_sale_discount($statement, 'fixed'))) {
                      echo ' hide';
                  } ?>" min="0" name="discount_total">

                                        <div class="input-group-addon">
                                            <div class="dropdown">
                                                <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                    <span class="discount-total-type-selected">
                                                        <?php if (!isset($statement) || isset($statement) && (is_sale_discount($statement, 'percent') || !is_sale_discount_applied($statement))) {
                      echo '%';
                  } else {
                      echo _l('discount_fixed_amount');
                  }
                                        ?>
                                                    </span>
                                                    <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu" id="discount-total-type-dropdown"
                                                    aria-labelledby="dropdown_menu_tax_total_type">
                                                    <li>
                                                        <a href="#" class="discount-total-type discount-type-percent<?php if (!isset($statement) || (isset($statement) && is_sale_discount($statement, 'percent')) || (isset($statement) && !is_sale_discount_applied($statement))) {
                                            echo ' selected';
                                        } ?>">%</a>
                                                    </li>
                                                    <li><a href="#" class="discount-total-type discount-type-fixed<?php if (isset($statement) && is_sale_discount($statement, 'fixed')) {
                                            echo ' selected';
                                        } ?>">
                                                            <?php echo _l('discount_fixed_amount'); ?>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="discount-total"></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col-md-7">
                                    <span
                                        class="bold tw-text-neutral-700"><?php echo _l('statement_adjustment'); ?></span>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" data-toggle="tooltip"
                                        data-title="<?php echo _l('numbers_not_formatted_while_editing'); ?>" value="<?php if (isset($statement)) {
                                            echo $statement->adjustment;
                                        } else {
                                            echo 0;
                                        } ?>" class="form-control pull-left" name="adjustment">
                                </div>
                            </div>
                        </td>
                        <td class="adjustment"></td>
                    </tr>
                    <tr>
                        <td><span class="bold tw-text-neutral-700"><?php echo _l('statement_total'); ?> :</span>
                        </td>
                        <td class="total">
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>


        <div id="removed-items"></div>
        <div id="billed-tasks"></div>
        <div id="billed-expenses"></div>
        <?php echo form_hidden('task_id'); ?>
        <?php echo form_hidden('expense_id'); ?>

    </div>

    <hr class="hr-panel-separator" />

    <div class="panel-body">
        <?php $value = (isset($statement) ? $statement->clientnote : get_option('predefined_clientnote_statement')); ?>
        <?php echo render_textarea('clientnote', 'statement_add_edit_client_note', $value); ?>
        <?php $value = (isset($statement) ? $statement->terms : get_option('predefined_terms_statement')); ?>
        <?php echo render_textarea('terms', 'terms_and_conditions', $value, [], [], 'mtop15'); ?>
    </div>

    <?php hooks()->do_action('after_render_statement_template', isset($statement) ? $statement : false); ?>
</div>

<div class="btn-bottom-pusher"></div>
<div class="btn-bottom-toolbar text-right">
    <?php if (!isset($statement)) { ?>
    <button class="btn-tr btn btn-default mright5 text-right statement-form-submit save-as-draft transaction-submit">
        <?php echo _l('save_as_draft'); ?>
    </button>
    <?php } ?>
    <div class="btn-group dropup">
        <button type="button"
            class="btn-tr btn btn-primary statement-form-submit transaction-submit"><?php echo _l('submit'); ?></button>
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right width200">
            <li>
                <a href="#" class="statement-form-submit save-and-send transaction-submit">
                    <?php echo _l('save_and_send'); ?>
                </a>
            </li>
            <?php if (!isset($statement)) { ?>
            <li>
                <a href="#" class="statement-form-submit save-and-send-later transaction-submit">
                    <?php echo _l('save_and_send_later'); ?>
                </a>
            </li>
            <li>
                <a href="#" class="statement-form-submit save-and-record-payment transaction-submit">
                    <?php echo _l('save_and_record_payment'); ?>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>