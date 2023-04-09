<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get statement short_url
 * @since  Version 2.7.3
 * @param  object $statement
 * @return string Url
 */
function get_statement_shortlink($statement)
{
    $long_url = site_url("statement/{$statement->id}/{$statement->hash}");
    if (!get_option('bitly_access_token')) {
        return $long_url;
    }

    // Check if statement has short link, if yes return short link
    if (!empty($statement->short_link)) {
        return $statement->short_link;
    }

    // Create short link and return the newly created short link
    $short_link = app_generate_short_link([
        'long_url' => $long_url,
        'title'    => format_statement_number($statement->id),
    ]);

    if ($short_link) {
        $CI = &get_instance();
        $CI->db->where('id', $statement->id);
        $CI->db->update(db_prefix() . 'statements', [
            'short_link' => $short_link,
        ]);

        return $short_link;
    }

    return $long_url;
}

/**
 * Get statement total left for paying if not payments found the original total from the statement will be returned
 * @since  Version 1.0.1
 * @param  mixed $id     statement id
 * @param  mixed $statement_total
 * @return mixed  total left
 */
function get_statement_total_left_to_pay($id, $statement_total = null)
{
    $CI = &get_instance();

    if ($statement_total === null) {
        $CI->db->select('total')
            ->where('id', $id);
        $statement_total = $CI->db->get(db_prefix() . 'statements')->row()->total;
    }

    if (!class_exists('remittances_model')) {
        $CI->load->model('statements/remittances_model');
    }

    if (!class_exists('credit_notes_model')) {
        $CI->load->model('credit_notes_model');
    }

    $payments = $CI->remittances_model->get_statement_payments($id);
    $credits  = $CI->credit_notes_model->get_applied_invoice_credits($id);

    $payments = array_merge($payments, $credits);

    $totalPayments = 0;

    $bcadd = function_exists('bcadd');

    foreach ($payments as $payment) {
        if ($bcadd) {
            $totalPayments = bcadd($totalPayments, $payment['amount'], get_decimal_places());
        } else {
            $totalPayments += $payment['amount'];
        }
    }

    if (function_exists('bcsub')) {
        return bcsub($statement_total, $totalPayments, get_decimal_places());
    }

    return number_format($statement_total - $totalPayments, get_decimal_places(), '.', '');
}

/**
 * Check if statement email template for overdue notices is enabled
 * @return boolean
 */
function is_statements_email_overdue_notice_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'statement-overdue-notice', 'active' => 1]) > 0;
}

/**
 * Check if statement email template for due notices is enabled
 *
 * @since  2.8.0
 *
 * @return boolean
 */
function is_statements_email_due_notice_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'statement-due-notice', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending statement overdue notices
 * Will be either email or SMS
 * @return boolean
 */
function is_statements_overdue_reminders_enabled()
{
    return is_statements_email_overdue_notice_enabled() || is_sms_trigger_active(SMS_TRIGGER_INVOICE_OVERDUE);
}

/**
 * Check if there are sources for sending statement due notices
 * Will be either email or SMS
 *
 * @since  2.8.0
 *
 * @return boolean
 */
function is_statements_due_reminders_enabled()
{
    return is_statements_email_due_notice_enabled() || is_sms_trigger_active(SMS_TRIGGER_INVOICE_DUE);
}

/**
 * Check statement restrictions - hash, clientid
 * @since  Version 1.0.1
 * @param  mixed $id   statement id
 * @param  string $hash statement hash
 */
function check_statement_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('statements_model');
    if (!$hash || !$id) {
        show_404();
    }
    if (!is_client_logged_in() && !is_staff_logged_in()) {
        if (get_option('view_statement_only_logged_in') == 1) {
            redirect_after_login_to_current_url();
            redirect(site_url('authentication/login'));
        }
    }
    $statement = $CI->statements_model->get($id);
    if (!$statement || ($statement->hash != $hash)) {
        show_404();
    }

    // Do one more check
    if (!is_staff_logged_in()) {
        if (get_option('view_statement_only_logged_in') == 1) {
            if ($statement->clientid != get_client_user_id()) {
                show_404();
            }
        }
    }
}

/**
 * Format statement status
 * @param  integer  $status
 * @param  string  $classes additional classes
 * @param  boolean $label   To include in html label or not
 * @return mixed
 */
function format_statement_status($status, $classes = '', $label = true)
{
    if (!class_exists('Statements_model', false)) {
        get_instance()->load->model('statements_model');
    }

    $id          = $status;
    $label_class = get_statement_status_label($status);
    if ($status == Statements_model::STATUS_UNPAID) {
        $status = _l('statement_status_unpaid');
    } elseif ($status == Statements_model::STATUS_PAID) {
        $status = _l('statement_status_paid');
    } elseif ($status == Statements_model::STATUS_PARTIALLY) {
        $status = _l('statement_status_not_paid_completely');
    } elseif ($status == Statements_model::STATUS_OVERDUE) {
        $status = _l('statement_status_overdue');
    } elseif ($status == Statements_model::STATUS_CANCELLED) {
        $status = _l('statement_status_cancelled');
    } else {
        // status 6
        $status = _l('statement_status_draft');
    }
    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status statement-status-' . $id . '">' . $status . '</span>';
    }

    return $status;
}
/**
 * Return statement status label class baed on twitter bootstrap classses
 * @param  mixed $status statement status id
 * @return string
 */
function get_statement_status_label($status)
{
    if (!class_exists('Statements_model', false)) {
        get_instance()->load->model('statements_model');
    }

    $label_class = '';
    if ($status == Statements_model::STATUS_UNPAID) {
        $label_class = 'danger';
    } elseif ($status == Statements_model::STATUS_PAID) {
        $label_class = 'success';
    } elseif ($status == Statements_model::STATUS_PARTIALLY) {
        $label_class = 'warning';
    } elseif ($status == Statements_model::STATUS_OVERDUE) {
        $label_class = 'warning';
    } elseif ($status == Statements_model::STATUS_CANCELLED || $status == Statements_model::STATUS_DRAFT) {
        $label_class = 'default';
    } else {
        if (!is_numeric($status)) {
            if ($status == 'not_sent') {
                $label_class = 'default';
            }
        }
    }

    return $label_class;
}

/**
 * Check whether the given statement is overdue
 *
 * @since 2.7.1
 *
 * @param  Object|array  $statement
 *
 * @return boolean
 */
function is_statement_overdue($statement)
{
    if (!class_exists('Statements_model', false)) {
        get_instance()->load->model('statements_model');
    }

    $statement = (object) $statement;

    if (!$statement->duedate) {
        return false;
    }

    if ($statement->status == Statements_model::STATUS_OVERDUE) {
        return true;
    }

    return $statement->status == Statements_model::STATUS_PARTIALLY && get_total_days_overdue($statement->duedate) > 0;
}

/**
 * Function used in statement PDF, this function will return RGBa color for PDF dcouments
 * @param  mixed $status_id current statement status id
 * @return string
 */
function statement_status_color_pdf($status_id)
{
    $statusColor = '';

    if (!class_exists('Statements_model', false)) {
        get_instance()->load->model('statements_model');
    }

    if ($status_id == Statements_model::STATUS_UNPAID) {
        $statusColor = '252, 45, 66';
    } elseif ($status_id == Statements_model::STATUS_PAID) {
        $statusColor = '0, 191, 54';
    } elseif ($status_id == Statements_model::STATUS_PARTIALLY) {
        $statusColor = '255, 111, 0';
    } elseif ($status_id == Statements_model::STATUS_OVERDUE) {
        $statusColor = '255, 111, 0';
    } elseif ($status_id == Statements_model::STATUS_CANCELLED || $status_id == Statements_model::STATUS_DRAFT) {
        $statusColor = '114, 123, 144';
    }

    return hooks()->apply_filters('statement_status_pdf_color', $statusColor, $status_id);
}

/**
 * Update statement status
 * @param  mixed $id statement id
 * @return mixed statement updates status / if no update return false
 * @return boolean $prevent_logging do not log changes if the status is updated for the statement activity log
 */
function update_statement_status($id, $force_update = false, $prevent_logging = false)
{
    $CI = &get_instance();

    $CI->load->model('statements_model');
    $statement = $CI->statements_model->get($id);

    $original_status = $statement->status;

    if (($original_status == Statements_model::STATUS_DRAFT && $force_update == false)
        || ($original_status == Statements_model::STATUS_CANCELLED && $force_update == false)
    ) {
        return false;
    }

    $CI->db->select('amount')
        ->where('statementid', $id)
        ->order_by(db_prefix() . 'statementpaymentrecords.id', 'asc');
    $payments = $CI->db->get(db_prefix() . 'statementpaymentrecords')->result_array();

    if (!class_exists('credit_notes_model')) {
        $CI->load->model('credit_notes_model');
    }

    $credits = $CI->credit_notes_model->get_applied_invoice_credits($id);
    // Merge credits applied with payments, credits in this function are casted as payments directly to statement
    // This merge will help to update the status
    $payments = array_merge($payments, $credits);

    $totalPayments = [];
    $status        = Statements_model::STATUS_UNPAID;

    // Check if the first payments is equal to statement total
    if (isset($payments[0])) {
        if ($payments[0]['amount'] == $statement->total) {
            // Paid status
            $status = Statements_model::STATUS_PAID;
        } else {
            foreach ($payments as $payment) {
                array_push($totalPayments, $payment['amount']);
            }

            $totalPayments = array_sum($totalPayments);

            if ((function_exists('bccomp')
                    ?  bccomp($statement->total, $totalPayments, get_decimal_places()) === 0
                    || bccomp($statement->total, $totalPayments, get_decimal_places()) === -1
                    : number_format(($statement->total - $totalPayments), get_decimal_places(), '.', '') == '0')
                || $totalPayments > $statement->total
            ) {
                // Paid status
                $status = Statements_model::STATUS_PAID;
            } elseif ($totalPayments == 0) {
                // Unpaid status
                $status = Statements_model::STATUS_UNPAID;
            } else {
                if ($statement->duedate != null) {
                    if ($totalPayments > 0) {
                        // Not paid completely status
                        $status = Statements_model::STATUS_PARTIALLY;
                    } elseif (date('Y-m-d', strtotime($statement->duedate)) < date('Y-m-d')) {
                        $status = Statements_model::STATUS_OVERDUE;
                    }
                } else {
                    // Not paid completely status
                    $status = Statements_model::STATUS_PARTIALLY;
                }
            }
        }
    } else {
        if ($statement->total == 0) {
            $status = Statements_model::STATUS_PAID;
        } else {
            if ($statement->duedate != null) {
                if (date('Y-m-d', strtotime($statement->duedate)) < date('Y-m-d')) {
                    // Overdue status
                    $status = Statements_model::STATUS_OVERDUE;
                }
            }
        }
    }

    $CI->db->where('id', $id);
    $CI->db->update(db_prefix() . 'statements', [
        'status' => $status,
    ]);

    if ($CI->db->affected_rows() > 0) {
        hooks()->do_action('statement_status_changed', ['statement_id' => $id, 'status' => $status]);

        if ($prevent_logging == true) {
            return $status;
        }

        $log = 'Statement Status Updated [Statement Number: ' . format_statement_number($statement->id) . ', From: ' . format_statement_status($original_status, '', false) . ' To: ' . format_statement_status($status, '', false) . ']';

        log_activity($log, null);

        $additional_activity = serialize([
            '<original_status>' . $original_status . '</original_status>',
            '<new_status>' . $status . '</new_status>',
        ]);

        $CI->statements_model->log_statement_activity($statement->id, 'statement_activity_status_updated', false, $additional_activity);

        return $status;
    }

    return false;
}


/**
 * Check if the statement id is last statement
 * @param  mixed  $id statement id
 * @return boolean
 */
function is_last_statement($id)
{
    $CI = &get_instance();
    $CI->db->select('id')->from(db_prefix() . 'statements')->order_by('id', 'desc')->limit(1);
    $query           = $CI->db->get();
    $last_statement_id = $query->row()->id;
    if ($last_statement_id == $id) {
        return true;
    }

    return false;
}

/**
 * Format statement number based on description
 * @param  mixed $id
 * @return string
 */
function format_statement_number($id)
{
    $CI = &get_instance();

    if (!is_object($id)) {
        $CI->db->select('date,number,prefix,number_format,status')
            ->from(db_prefix() . 'statements')
            ->where('id', $id);

        $statement = $CI->db->get()->row();
    } else {
        $statement = $id;

        $id = $statement->id;
    }

    if (!$statement) {
        return '';
    }

    if (!class_exists('Statements_model', false)) {
        get_instance()->load->model('statements_model');
    }

    if ($statement->status == Statements_model::STATUS_DRAFT) {
        $number = $statement->prefix . 'DRAFT';
    } else {
        $number = sales_number_format($statement->number, $statement->number_format, $statement->prefix, $statement->date);
    }

    return hooks()->apply_filters('format_statement_number', $number, [
        'id'      => $id,
        'statement' => $statement,
    ]);
}

/**
 * Function that return statement item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_statement_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'statement');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}

/**
 * Check if payment mode is allowed for specific statement
 * @param  mixed  $id payment mode id
 * @param  mixed  $statementid statement id
 * @return boolean
 */
function is_payment_mode_allowed_for_statement($id, $statementid)
{
    $CI = &get_instance();
    $CI->db->select('' . db_prefix() . 'currencies.name as currency_name,allowed_payment_modes')->from(db_prefix() . 'statements')->join(db_prefix() . 'currencies', '' . db_prefix() . 'currencies.id = ' . db_prefix() . 'statements.currency', 'left')->where(db_prefix() . 'statements.id', $statementid);
    $statement       = $CI->db->get()->row();
    $allowed_modes = $statement->allowed_payment_modes;
    if (!is_null($allowed_modes)) {
        $allowed_modes = unserialize($allowed_modes);
        if (count($allowed_modes) == 0) {
            return false;
        }
        foreach ($allowed_modes as $mode) {
            if ($mode == $id) {
                // is offline payment mode
                if (is_numeric($id)) {
                    return true;
                }
                // check currencies
                $currencies = explode(',', get_option('paymentmethod_' . $id . '_currencies'));
                foreach ($currencies as $currency) {
                    $currency = trim($currency);
                    if (mb_strtoupper($currency) == mb_strtoupper($statement->currency_name)) {
                        return true;
                    }
                }

                return false;
            }
        }
    } else {
        return false;
    }

    return false;
}
/**
 * Check if statement mode exists in statement
 * @since  Version 1.0.1
 * @param  array  $modes     all statement modes
 * @param  mixed  $statementid statement id
 * @param  boolean $offline   should check offline or online modes
 * @return boolean
 */
function found_statement_mode($modes, $statementid, $offline = true, $show_on_pdf = false)
{
    $CI = &get_instance();
    $CI->db->select('' . db_prefix() . 'currencies.name as currency_name,allowed_payment_modes')->from(db_prefix() . 'statements')->join(db_prefix() . 'currencies', '' . db_prefix() . 'currencies.id = ' . db_prefix() . 'statements.currency', 'left')->where(db_prefix() . 'statements.id', $statementid);
    $statement = $CI->db->get()->row();
    if (!is_null($statement->allowed_payment_modes)) {
        $statement->allowed_payment_modes = unserialize($statement->allowed_payment_modes);
        if (count($statement->allowed_payment_modes) == 0) {
            return false;
        }
        foreach ($modes as $mode) {
            if ($offline == true) {
                if (is_numeric($mode['id']) && is_array($statement->allowed_payment_modes)) {
                    foreach ($statement->allowed_payment_modes as $allowed_mode) {
                        if ($allowed_mode == $mode['id']) {
                            if ($show_on_pdf == false) {
                                return true;
                            }
                            if ($mode['show_on_pdf'] == 1) {
                                return true;
                            }

                            return false;
                        }
                    }
                }
            } else {
                if (!is_numeric($mode['id']) && !empty($mode['id'])) {
                    foreach ($statement->allowed_payment_modes as $allowed_mode) {
                        if ($allowed_mode == $mode['id']) {
                            // Check for currencies
                            $currencies = explode(',', get_option('paymentmethod_' . $mode['id'] . '_currencies'));
                            foreach ($currencies as $currency) {
                                $currency = trim($currency);
                                if (strtoupper($currency) == strtoupper($statement->currency_name)) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return false;
}

/**
 * This function do not work with cancelled status
 * Calculate statements percent by status
 * @param  mixed $status          estimate status
 * @param  mixed $total_statements in case the total is calculated in other place
 * @return array
 */
function get_statements_percent_by_status($status)
{
    $has_permission_view = has_permission('statements', '', 'view');
    $total_statements      = total_rows(db_prefix() . 'statements', 'status NOT IN(5)' . (!$has_permission_view ? ' AND (' . get_statements_where_sql_for_staff(get_staff_user_id()) . ')' : ''));

    $data            = [];
    $total_by_status = 0;
    if (!is_numeric($status)) {
        if ($status == 'not_sent') {
            $total_by_status = total_rows(db_prefix() . 'statements', 'sent=0 AND status NOT IN(2,5)' . (!$has_permission_view ? ' AND (' . get_statements_where_sql_for_staff(get_staff_user_id()) . ')' : ''));
        }
    } else {
        $total_by_status = total_rows(db_prefix() . 'statements', 'status = ' . $status . ' AND status NOT IN(5)' . (!$has_permission_view ? ' AND (' . get_statements_where_sql_for_staff(get_staff_user_id()) . ')' : ''));
    }
    $percent                 = ($total_statements > 0 ? number_format(($total_by_status * 100) / $total_statements, 2) : 0);
    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_statements;

    return $data;
}
/**
 * Check if staff member have assigned statements / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_statements($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-statements-' . $staff_id);

    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'statements', ['sale_agent' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-statements-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

/**
 * Load statements total templates
 * This is the template where is showing the panels Outstanding Statements, Paid Statements and Past Due statements
 * @return string
 */
function load_statements_total_template()
{
    $CI = &get_instance();
    $CI->load->model('statements_model');
    $_data = $CI->input->post();
    if (!$CI->input->post('customer_id')) {
        $multiple_currencies = call_user_func('is_using_multiple_currencies');
    } else {
        $_data['customer_id'] = $CI->input->post('customer_id');
        $multiple_currencies  = call_user_func('is_client_using_multiple_currencies', $CI->input->post('customer_id'));
    }

    if ($CI->input->post('project_id')) {
        $_data['project_id'] = $CI->input->post('project_id');
    }

    if ($multiple_currencies) {
        $CI->load->model('currencies_model');
        $data['statements_total_currencies'] = $CI->currencies_model->get();
    }

    $data['statements_years'] = $CI->statements_model->get_statements_years();

    if (
        count($data['statements_years']) >= 1
        && !\app\services\utilities\Arr::inMultidimensional($data['statements_years'], 'year', date('Y'))
    ) {
        array_unshift($data['statements_years'], ['year' => date('Y')]);
    }

    $data['total_result'] = $CI->statements_model->get_statements_total($_data);
    $data['_currency']    = $data['total_result']['currencyid'];

    $CI->load->view('admin/statements/statements_total_template', $data);
}

function get_statements_where_sql_for_staff($staff_id)
{
    $CI                                 = &get_instance();
    $has_permission_view_own            = has_permission('statements', '', 'view_own');
    $allow_staff_view_statements_assigned = get_option('allow_staff_view_statements_assigned');
    $whereUser                          = '';
    if ($has_permission_view_own) {
        $whereUser = '((' . db_prefix() . 'statements.addedfrom=' . $CI->db->escape_str($staff_id) . ' AND ' . db_prefix() . 'statements.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "statements" AND capability="view_own"))';
        if ($allow_staff_view_statements_assigned == 1) {
            $whereUser .= ' OR sale_agent=' . $CI->db->escape_str($staff_id);
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'sale_agent=' . $CI->db->escape_str($staff_id);
    }

    return $whereUser;
}

/**
 * Check if staff member can view statement
 * @param  mixed $id statement id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_statement($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('statements', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, sale_agent');
    $CI->db->from(db_prefix() . 'statements');
    $CI->db->where('id', $id);
    $statement = $CI->db->get()->row();

    if ((has_permission('statements', $staff_id, 'view_own') && $statement->addedfrom == $staff_id)
        || ($statement->sale_agent == $staff_id && get_option('allow_staff_view_statements_assigned') == '1')
    ) {
        return true;
    }

    return false;
}


/*
 * credit note
 */


/**
 * Check if credits can be applied to invoice based on the invoice status
 * @param  mixed $status_id invoice status id
 * @return boolean
 */


/**
 * Return array with invoices IDs statuses which can be applied credits
 * @return array
 */
function statements_statuses_available_for_credits()
{
    if (!class_exists('Statements_model', false)) {
        get_instance()->load->model('Statements,Statements_model');
    }

    return hooks()->apply_filters('invoices_statuses_available_for_credits', [
        Invoices_model::STATUS_UNPAID,
        Invoices_model::STATUS_PARTIALLY,
        Invoices_model::STATUS_DRAFT,
        Invoices_model::STATUS_OVERDUE,
    ]);
}


function credits_can_be_applied_to_statement($status_id)
{
    return in_array($status_id, statements_statuses_available_for_credits());
}


/**
 * Prepare general schedule pdf
 * @since  Version 1.0.2
 * @param  object $schedule schedule as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function remittancepdf($remittance, $tag = '')
{
    return app_pdf('remittance',  module_libs_path(STATEMENTS_MODULE_NAME) . 'pdf/Remittancepdf', $remittance, $tag);
}



/**
 * Prepare general schedule pdf
 * @since  Version 1.0.2
 * @param  object $schedule schedule as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function statementpdf($statement, $tag = '')
{
    return app_pdf('statement',  module_libs_path(STATEMENTS_MODULE_NAME) . 'pdf/Statementpdf', $statement, $tag);
}