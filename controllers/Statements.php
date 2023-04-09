<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Statements extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('statements/remittances_model');
        $this->load->model('statements_model');
        $this->load->model('credit_notes_model');
    }

    /* Get all statements in case user go on index page */
    public function index($id = '')
    {
        $this->list_statements($id);
    }

    /* List all statements datatables */
    public function list_statements($id = '')
    {
        if (!has_permission('statements', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            access_denied('statements');
        }

        close_setup_menu();

        $this->load->model('payment_modes_model');
        $data['payment_modes']        = $this->payment_modes_model->get('', [], true);
        $data['statementid']            = $id;
        $data['title']                = _l('statements');
        $data['statements_years']       = $this->statements_model->get_statements_years();
        $data['statements_sale_agents'] = $this->statements_model->get_sale_agents();
        $data['statements_statuses']    = $this->statements_model->get_statuses();
        $data['bodyclass']            = 'statements-total-manual';
        $this->load->view('admin/statements/manage', $data);
    }

    /* List all recurring statements */
    public function recurring($id = '')
    {
        if (!has_permission('statements', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            access_denied('statements');
        }

        close_setup_menu();

        $data['statementid']            = $id;
        $data['title']                = _l('statements_list_recurring');
        $data['statements_years']       = $this->statements_model->get_statements_years();
        $data['statements_sale_agents'] = $this->statements_model->get_sale_agents();
        $this->load->view('admin/statements/recurring/list', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('statements', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            ajax_access_denied();
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

        $this->app->get_table_data(($this->input->get('recurring') ? module_views_path('statements','admin/tables/recurring_statements') : module_views_path('statements','admin/tables/statements')), [
            'clientid' => $clientid,
            'data'     => $data,
        ]);
    }

    public function client_change_data($customer_id, $current_statement = '')
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('projects_model');
            $data                     = [];
            $data['billing_shipping'] = $this->clients_model->get_customer_billing_and_shipping_details($customer_id);
            $data['client_currency']  = $this->clients_model->get_customer_default_currency($customer_id);

            $data['customer_has_projects'] = customer_has_projects($customer_id);
            $data['billable_tasks']        = $this->tasks_model->get_billable_tasks($customer_id);

            if ($current_statement != '') {
                $this->db->select('status');
                $this->db->where('id', $current_statement);
                $current_statement_status = $this->db->get(db_prefix() . 'statements')->row()->status;
            }

            $_data['statements_to_merge'] = !isset($current_statement_status) || (isset($current_statement_status) && $current_statement_status != Statements_model::STATUS_CANCELLED) ? $this->statements_model->check_for_merge_statement($customer_id, $current_statement) : [];

            $data['merge_info'] = $this->load->view('admin/statements/merge_statement', $_data, true);

            $this->load->model('currencies_model');

            $__data['expenses_to_bill'] = !isset($current_statement_status) || (isset($current_statement_status) && $current_statement_status != Statements_model::STATUS_CANCELLED) ? $this->statements_model->get_expenses_to_bill($customer_id) : [];

            $data['expenses_bill_info'] = $this->load->view('admin/statements/bill_expenses', $__data, true);
            echo json_encode($data);
        }
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('statements', '', 'edit')) {
            $affected_rows = 0;

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'statements', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $affected_rows++;
            }

            if ($affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('statement'));
            }
        }
        echo json_encode($response);
        die;
    }

    public function validate_statement_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');

        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }

        if (total_rows('statements', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number' => $number,
            'status !=' => Statements_model::STATUS_DRAFT,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_statement($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'statement', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_statement($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'statement');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function pause_overdue_reminders($id)
    {
        if (has_permission('statements', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'statements', ['cancel_overdue_reminders' => 1]);
        }
        redirect(admin_url('statements/list_statements/' . $id));
    }

    public function resume_overdue_reminders($id)
    {
        if (has_permission('statements', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'statements', ['cancel_overdue_reminders' => 0]);
        }
        redirect(admin_url('statements/list_statements/' . $id));
    }

    public function mark_as_cancelled($id)
    {
        if (!has_permission('statements', '', 'edit') && !has_permission('statements', '', 'create')) {
            access_denied('statements');
        }

        $success = $this->statements_model->mark_as_cancelled($id);

        if ($success) {
            set_alert('success', _l('statement_marked_as_cancelled_successfully'));
        }

        redirect(admin_url('statements/list_statements/' . $id));
    }

    public function unmark_as_cancelled($id)
    {
        if (!has_permission('statements', '', 'edit') && !has_permission('statements', '', 'create')) {
            access_denied('statements');
        }
        $success = $this->statements_model->unmark_as_cancelled($id);
        if ($success) {
            set_alert('success', _l('statement_unmarked_as_cancelled'));
        }
        redirect(admin_url('statements/list_statements/' . $id));
    }

    public function copy($id)
    {
        if (!$id) {
            redirect(admin_url('statements'));
        }
        if (!has_permission('statements', '', 'create')) {
            access_denied('statements');
        }
        $new_id = $this->statements_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('statement_copy_success'));
            redirect(admin_url('statements/statement/' . $new_id));
        } else {
            set_alert('success', _l('statement_copy_fail'));
        }
        redirect(admin_url('statements/statement/' . $id));
    }

    public function get_merge_data($id)
    {
        $statement = $this->statements_model->get($id);
        $cf      = get_custom_fields('items');

        $i = 0;

        foreach ($statement->items as $item) {
            $statement->items[$i]['taxname']          = get_statement_item_taxes($item['id']);
            $statement->items[$i]['long_description'] = clear_textarea_breaks($item['long_description']);
            $this->db->where('item_id', $item['id']);
            $rel              = $this->db->get(db_prefix() . 'related_items')->result_array();
            $item_related_val = '';
            $rel_type         = '';
            foreach ($rel as $item_related) {
                $rel_type = $item_related['rel_type'];
                $item_related_val .= $item_related['rel_id'] . ',';
            }
            if ($item_related_val != '') {
                $item_related_val = substr($item_related_val, 0, -1);
            }
            $statement->items[$i]['item_related_formatted_for_input'] = $item_related_val;
            $statement->items[$i]['rel_type']                         = $rel_type;

            $statement->items[$i]['custom_fields'] = [];

            foreach ($cf as $custom_field) {
                $custom_field['value']                 = get_custom_field_value($item['id'], $custom_field['id'], 'items');
                $statement->items[$i]['custom_fields'][] = $custom_field;
            }
            $i++;
        }
        echo json_encode($statement);
    }

    public function get_bill_expense_data($id)
    {
        $this->load->model('expenses_model');
        $expense = $this->expenses_model->get($id);

        $expense->qty              = 1;
        $expense->long_description = clear_textarea_breaks($expense->description);
        $expense->description      = $expense->name;
        $expense->rate             = $expense->amount;
        if ($expense->tax != 0) {
            $expense->taxname = [];
            array_push($expense->taxname, $expense->tax_name . '|' . $expense->taxrate);
        }
        if ($expense->tax2 != 0) {
            array_push($expense->taxname, $expense->tax_name2 . '|' . $expense->taxrate2);
        }
        echo json_encode($expense);
    }

    /* Add new statement or update existing */
    public function statement($id = '')
    {
        if ($this->input->post()) {
            $statement_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('statements', '', 'create')) {
                    access_denied('statements');
                }

                if (hooks()->apply_filters('validate_statement_number', true)) {
                    $number = ltrim($statement_data['number'], '0');
                    if (total_rows('statements', [
                        'YEAR(date)' => date('Y', strtotime(to_sql_date($statement_data['date']))),
                        'number'     => $number,
                        'status !='  => Statements_model::STATUS_DRAFT,
                    ])) {
                        set_alert('warning', _l('statement_number_exists'));

                        redirect(admin_url('statements/statement'));
                    }
                }

                $id = $this->statements_model->add($statement_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('statement')));
                    $redUrl = admin_url('statements/list_statements/' . $id);

                    if (isset($statement_data['save_and_record_payment'])) {
                        $this->session->set_userdata('record_payment', true);
                    } elseif (isset($statement_data['save_and_send_later'])) {
                        $this->session->set_userdata('send_later', true);
                    }

                    redirect($redUrl);
                }
            } else {
                if (!has_permission('statements', '', 'edit')) {
                    access_denied('statements');
                }

                // If number not set, is draft
                if (hooks()->apply_filters('validate_statement_number', true) && isset($statement_data['number'])) {
                    $number = trim(ltrim($statement_data['number'], '0'));
                    if (total_rows('statements', [
                        'YEAR(date)' => date('Y', strtotime(to_sql_date($statement_data['date']))),
                        'number'     => $number,
                        'status !='  => Statements_model::STATUS_DRAFT,
                        'id !='      => $id,
                    ])) {
                        set_alert('warning', _l('statement_number_exists'));

                        redirect(admin_url('statements/statement/' . $id));
                    }
                }
                $success = $this->statements_model->update($statement_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('statement')));
                }

                redirect(admin_url('statements/list_statements/' . $id));
            }
        }
        if ($id == '') {
            $title                  = _l('create_new_statement');
            $data['billable_tasks'] = [];
        } else {
            $statement = $this->statements_model->get($id);

            if (!$statement || !user_can_view_statement($id)) {
                blank_page(_l('statement_not_found'));
            }

            $data['statements_to_merge'] = $this->statements_model->check_for_merge_statement($statement->clientid, $statement->id);
            $data['expenses_to_bill']  = $this->statements_model->get_expenses_to_bill($statement->clientid);

            $data['statement']        = $statement;
            $data['edit']           = true;
            $data['billable_tasks'] = $this->tasks_model->get_billable_tasks($statement->clientid, !empty($statement->project_id) ? $statement->project_id : '');

            $title = _l('edit', _l('statement_lowercase')) . ' - ' . format_statement_number($statement->id);
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('statement_items_model');

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->statement_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->statement_items_model->get_groups();

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['title']     = $title;
        $data['bodyclass'] = 'statement';
        $this->load->view('admin/statements/statement', $data);
    }

    /* Get all statement data used when user click on invoiec number in a datatable left side*/
    public function get_statement_data_ajax($id)
    {
        if (!has_permission('statements', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die(_l('statement_not_found'));
        }

        $statement = $this->statements_model->get($id);

        if (!$statement || !user_can_view_statement($id)) {
            echo _l('statement_not_found');
            die;
        }

        $template_name = 'statement_send_to_customer';

        if ($statement->sent == 1) {
            $template_name = 'statement_send_to_customer_already_sent';
        }

        $data = prepare_mail_preview_data($template_name, $statement->clientid);

        // Check for recorded payments
        $this->load->model('statements/remittances_model');
        $data['statements_to_merge']          = $this->statements_model->check_for_merge_statement($statement->clientid, $id);
        $data['members']                    = $this->staff_model->get('', ['active' => 1]);
        $data['payments']                   = $this->remittances_model->get_statement_payments($id);
        $data['activity']                   = $this->statements_model->get_statement_activity($id);
        $data['totalNotes']                 = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'statement']);
        $data['statement_recurring_statements'] = $this->statements_model->get_statement_recurring_statements($id);

        $data['applied_credits'] = $this->credit_notes_model->get_applied_invoice_credits($id);
        // This data is used only when credit can be applied to statement
        if (credits_can_be_applied_to_invoice($statement->status)) {
            $data['credits_available'] = $this->credit_notes_model->total_remaining_credits_by_customer($statement->clientid);

            if ($data['credits_available'] > 0) {
                $data['open_credits'] = $this->credit_notes_model->get_open_credits($statement->clientid);
            }

            $customer_currency = $this->clients_model->get_customer_default_currency($statement->clientid);
            $this->load->model('currencies_model');

            if ($customer_currency != 0) {
                $data['customer_currency'] = $this->currencies_model->get($customer_currency);
            } else {
                $data['customer_currency'] = $this->currencies_model->get_base_currency();
            }
        }

        $data['statement'] = $statement;

        $data['record_payment'] = false;
        $data['send_later']     = false;

        if ($this->session->has_userdata('record_payment')) {
            $data['record_payment'] = true;
            $this->session->unset_userdata('record_payment');
        } elseif ($this->session->has_userdata('send_later')) {
            $data['send_later'] = true;
            $this->session->unset_userdata('send_later');
        }

        $this->load->view('admin/statements/statement_preview_template', $data);
    }

    public function apply_credits($statement_id)
    {
        $total_credits_applied = 0;
        foreach ($this->input->post('amount') as $credit_id => $amount) {
            $success = $this->credit_notes_model->apply_credits($credit_id, [
            'statement_id' => $statement_id,
            'amount'     => $amount,
        ]);
            if ($success) {
                $total_credits_applied++;
            }
        }

        if ($total_credits_applied > 0) {
            update_statement_status($statement_id, true);
            set_alert('success', _l('statement_credits_applied'));
        }
        redirect(admin_url('statements/list_statements/' . $statement_id));
    }

    public function get_statements_total()
    {
        if ($this->input->post()) {
            load_statements_total_template();
        }
    }

    /* Record new inoice payment view */
    public function record_statement_payment_ajax($id)
    {
        $this->load->model('remittance_modes_model');
        $this->load->model('remittances_model');
        $data['payment_modes'] = $this->remittance_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $data['statement']  = $this->statements_model->get($id);
        $data['payments'] = $this->remittances_model->get_statement_payments($id);
        $this->load->view('admin/statements/record_payment_template', $data);
    }

    /* This is where statement payment record $_POST data is send */
    public function record_remittance()
    {
        if (!has_permission('payments', '', 'create')) {
            access_denied('Record Payment');
        }
        if ($this->input->post()) {
            $this->load->model('statements/Remittances_model');
            $id = $this->remittances_model->process_payment($this->input->post(), '');
            if ($id) {
                set_alert('success', _l('statement_payment_recorded'));
                redirect(admin_url('statements/remittance/' . $id));
            } else {
                set_alert('danger', _l('statement_payment_record_failed'));
            }
            redirect(admin_url('statements/list_statements/' . $this->input->post('statementid')));
        }
    }

    /* Send statement to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_statement($id);
        if (!$canView) {
            access_denied('Statements');
        } else {
            if (!has_permission('statements', '', 'view') && !has_permission('statements', '', 'view_own') && $canView == false) {
                access_denied('Statements');
            }
        }

        try {
            $statementData = [];
            if ($this->input->post('attach_statement')) {
                $statementData['attach'] = true;
                $statementData['from']   = to_sql_date($this->input->post('statement_from'));
                $statementData['to']     = to_sql_date($this->input->post('statement_to'));
            }

            $success = $this->statements_model->send_statement_to_client(
                $id,
                '',
                $this->input->post('attach_pdf'),
                $this->input->post('cc'),
                false,
                $statementData
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('statement_sent_to_client_success'));
        } else {
            set_alert('danger', _l('statement_sent_to_client_fail'));
        }
        redirect(admin_url('statements/list_statements/' . $id));
    }

    /* Delete statement payment*/
    public function delete_payment($id, $statementid)
    {
        if (!has_permission('payments', '', 'delete')) {
            access_denied('payments');
        }
        $this->load->model('payments_model');
        if (!$id) {
            redirect(admin_url('payments'));
        }
        $response = $this->payments_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('payment')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_lowercase')));
        }
        redirect(admin_url('statements/list_statements/' . $statementid));
    }

    /* Delete statement */
    public function delete($id)
    {
        if (!has_permission('statements', '', 'delete')) {
            access_denied('statements');
        }
        if (!$id) {
            redirect(admin_url('statements/list_statements'));
        }
        $success = $this->statements_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('statement')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('statement_lowercase')));
        }
        if (strpos($_SERVER['HTTP_REFERER'], 'list_statements') !== false) {
            redirect(admin_url('statements/list_statements'));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->statements_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /* Will send overdue notice to client */
    public function send_overdue_notice($id)
    {
        $canView = user_can_view_statement($id);
        if (!$canView) {
            access_denied('Statements');
        } else {
            if (!has_permission('statements', '', 'view') && !has_permission('statements', '', 'view_own') && $canView == false) {
                access_denied('Statements');
            }
        }

        $send = $this->statements_model->send_statement_overdue_notice($id);
        if ($send) {
            set_alert('success', _l('statement_overdue_reminder_sent'));
        } else {
            set_alert('warning', _l('statement_reminder_send_problem'));
        }
        redirect(admin_url('statements/list_statements/' . $id));
    }

    /* Generates statement PDF and senting to email of $send_to_email = true is passed */
    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('statements/list_statements'));
        }

        $canView = user_can_view_statement($id);
        if (!$canView) {
            access_denied('Statements');
        } else {
            if (!has_permission('statements', '', 'view') && !has_permission('statements', '', 'view_own') && $canView == false) {
                access_denied('Statements');
            }
        }

        $statement        = $this->statements_model->get($id);
        $statement        = hooks()->apply_filters('before_admin_view_statement_pdf', $statement);
        $statement_number = format_statement_number($statement->id);

        try {
            $pdf = statementpdf($statement);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($statement_number)) . '.pdf', $type);
    }

    public function mark_as_sent($id)
    {
        if (!$id) {
            redirect(admin_url('statements/list_statements'));
        }
        if (!user_can_view_statement($id)) {
            access_denied('Statement Mark As Sent');
        }

        $success = $this->statements_model->set_statement_sent($id, true);

        if ($success) {
            set_alert('success', _l('statement_marked_as_sent'));
        } else {
            set_alert('warning', _l('statement_marked_as_sent_failed'));
        }

        redirect(admin_url('statements/list_statements/' . $id));
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('statement_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('statement_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }
}