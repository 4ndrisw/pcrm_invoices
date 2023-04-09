<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-12">
    <div class="tw-mb-2 sm:tw-mb-4">
        <div class="_buttons">
            <?php $this->load->view('admin/statements/statements_top_stats'); ?>
            <?php if (has_permission('statements', '', 'create')) { ?>
            <a href="<?php echo admin_url('statements/statement'); ?>"
                class="btn btn-primary pull-left new new-statement-list mright5">
                <i class="fa-regular fa-plus tw-mr-1"></i>
                <?php echo _l('create_new_statement'); ?>
            </a>
            <?php } ?>
            <?php if (!isset($project) && !isset($customer) && staff_can('create', 'payments')) { ?>
            <button id="add-batch-payment" onclick="add_batch_payment()" class="btn btn-primary pull-left">
                <i class="fa-solid fa-file-statement tw-mr-1"></i>
                <?php echo _l('batch_payments'); ?>
            </button>
            <?php } ?>
            <?php if (!isset($project)) { ?>
            <a href="<?php echo admin_url('statements/recurring'); ?>" class="btn btn-default pull-left mleft5">
                <i class="fa-solid fa-repeat tw-mr-1"></i>
                <?php echo _l('statements_list_recurring'); ?>
            </a>
            <?php } ?>
            <div class="display-block text-right">
                <div class="btn-group pull-right mleft4 statement-view-buttons btn-with-tooltip-group _filter_data"
                    data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu width300">
                        <li>
                            <a href="#" data-cview="all"
                                onclick="dt_custom_view('','.table-statements',''); return false;">
                                <?php echo _l('statements_list_all'); ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li class="<?php if ($this->input->get('filter') == 'not_sent') {
    echo 'active';
} ?>">
                            <a href="#" data-cview="not_sent"
                                onclick="dt_custom_view('not_sent','.table-statements','not_sent'); return false;">
                                <?php echo _l('not_sent_indicator'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-cview="not_have_payment"
                                onclick="dt_custom_view('not_have_payment','.table-statements','not_have_payment'); return false;">
                                <?php echo _l('statements_list_not_have_payment'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-cview="recurring"
                                onclick="dt_custom_view('recurring','.table-statements','recurring'); return false;">
                                <?php echo _l('statements_list_recurring'); ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <?php foreach ($statements_statuses as $status) { ?>
                        <li class="<?php if ($status == $this->input->get('status')) {
    echo 'active';
} ?>">
                            <a href="#" data-cview="statements_<?php echo $status; ?>"
                                onclick="dt_custom_view('statements_<?php echo $status; ?>','.table-statements','statements_<?php echo $status; ?>'); return false;"><?php echo format_statement_status($status, '', false); ?></a>
                        </li>
                        <?php } ?>
                        <?php if (count($statements_years) > 0) { ?>
                        <li class="divider"></li>
                        <?php foreach ($statements_years as $year) { ?>
                        <li class="active">
                            <a href="#" data-cview="year_<?php echo $year['year']; ?>"
                                onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-statements','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php } ?>
                        <?php if (count($statements_sale_agents) > 0) { ?>
                        <div class="clearfix"></div>
                        <li class="divider"></li>
                        <li class="dropdown-submenu pull-left">
                            <a href="#" tabindex="-1"><?php echo _l('sale_agent_string'); ?></a>
                            <ul class="dropdown-menu dropdown-menu-left">
                                <?php foreach ($statements_sale_agents as $agent) { ?>
                                <li>
                                    <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>"
                                        onclick="dt_custom_view(<?php echo $agent['sale_agent']; ?>,'.table-statements','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo $agent['full_name']; ?>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <?php if (count($payment_modes) > 0) { ?>
                        <li class="divider"></li>
                        <?php } ?>
                        <?php foreach ($payment_modes as $mode) {
    if (total_rows(db_prefix() . 'statementpaymentrecords', ['paymentmode' => $mode['id']]) == 0) {
        continue;
    } ?>
                        <li>
                            <a href="#" data-cview="statement_payments_by_<?php echo $mode['id']; ?>"
                                onclick="dt_custom_view('<?php echo $mode['id']; ?>','.table-statements','statement_payments_by_<?php echo $mode['id']; ?>'); return false;">
                                <?php echo _l('statements_list_made_payment_by', $mode['name']); ?>
                            </a>
                        </li>
                        <?php
} ?>
                    </ul>
                </div>
                <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs"
                    onclick="toggle_small_view('.table-statements','#statement'); return false;" data-toggle="tooltip"
                    title="<?php echo _l('statements_toggle_table_tooltip'); ?>"><i
                        class="fa fa-angle-double-left"></i></a>
                <a href="#" class="btn btn-default btn-with-tooltip statements-total"
                    onclick="slideToggle('#stats-top'); init_statements_total(true); return false;" data-toggle="tooltip"
                    title="<?php echo _l('view_stats_tooltip'); ?>"><i class="fa fa-bar-chart"></i></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12" id="small-table">
            <div class="panel_s">
                <div class="panel-body panel-table-full">
                    <!-- if statementid found in url -->
                    <?php echo form_hidden('statementid', $statementid); ?>
                    <?php $this->load->view('admin/statements/table_html'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-7 small-table-right-col">
            <div id="statement" class="hide">
            </div>
        </div>
    </div>
</div>