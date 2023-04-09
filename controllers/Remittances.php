<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Invoices_model $statements_model
 * @property Payments_model $payments_model
 */
class Remittances extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('statements/Remittances_model');
    }

    public function batch_payment_modal() {
        $this->load->model('statements_model');
        $data['statements'] = $this->statements_model->get_unpaid_statements();
        $data['customers'] = $this->db->select('userid,' . get_sql_select_client_company())
            ->where_in('userid', collect($data['statements'])->pluck('clientid')->toArray())
            ->get(db_prefix() . 'clients')->result();
        $this->load->view('admin/payments/batch_payment_modal', $data);
    }

	public function add_batch_payment()
	{
		if ($this->input->method() !== 'post') {
			show_404();
		}

		if (!staff_can('create', 'payment')) {
			access_denied('Create Payment');
		}
		$totalAdded = $this->payments_model->add_batch_payment($this->input->post());
        if ($totalAdded > 0) {
            set_alert('success', _l('batch_payment_added_successfully', $totalAdded));
            return redirect(admin_url('payments'));
        }
        return redirect(admin_url('statements'));
	}

    /* In case if user go only on /payments */
    public function index()
    {
        $this->list_payments();
    }

    public function list_payments()
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            access_denied('payments');
        }

        $data['title'] = _l('payments');
        $this->load->view('admin/remittances/manage', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('statements','admin/tables/remittances'), [
            'clientid' => $clientid,
        ]);
    }

    /* Update payment data */
    public function payment($id = '')
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            access_denied('payments');
        }

        if (!$id) {
            redirect(admin_url('remittances'));
        }

        if ($this->input->post()) {
            if (!has_permission('payments', '', 'edit')) {
                access_denied('Update Payment');
            }
            $success = $this->remittances_model->update($this->input->post(), $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('payment')));
            }
            redirect(admin_url('statements/remittances/payment/' . $id));
        }
        $payment = $this->remittances_model->get($id);
        log_activity(json_encode($payment));

        if (!$payment) {
            show_404();
        }

        $this->load->model('statements/remittances_model');
        $payment->statement = $this->statements_model->get($payment->statementid);
        $template_name    = 'statement_payment_recorded_to_customer';

        $data = prepare_mail_preview_data($template_name, $payment->statement->clientid);

        $data['payment'] = $payment;
        $this->load->model('remittance_modes_model');
        $data['payment_modes'] = $this->remittance_modes_model->get('', [], true, true);

        $i = 0;
        foreach ($data['payment_modes'] as $mode) {
            if ($mode['active'] == 0 && $data['payment']->paymentmode != $mode['id']) {
                unset($data['payment_modes'][$i]);
            }
            $i++;
        }

        $data['title'] = _l('payment_receipt') . ' - ' . format_statement_number($data['payment']->statementid);
        $this->load->view('admin/payments/payment', $data);
    }

    /**
     * Generate payment pdf
     * @since  Version 1.0.1
     * @param  mixed $id Payment id
     */
    public function pdf($id)
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            access_denied('View Payment');
        }

        $payment = $this->remittances_model->get($id);

        if (!has_permission('payments', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && !user_can_view_statement($payment->statementid)) {
            access_denied('View Payment');
        }

        $this->load->model('statements_model');
        $payment->statement_data = $this->statements_model->get($payment->statementid);

        try {
            $paymentpdf = remittancespdf($payment);
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

        $paymentpdf->Output(mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid)) . '.pdf', $type);
    }

    /**
     * Send payment manually to customer contacts
     * @since  2.3.2
     * @param  mixed $id payment id
     * @return mixed
     */
    public function send_to_email($id)
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && get_option('allow_staff_view_statements_assigned') == '0') {
            access_denied('Send Payment');
        }

        $payment = $this->remittances_model->get($id);

        if (!has_permission('payments', '', 'view')
            && !has_permission('statements', '', 'view_own')
            && !user_can_view_statement($payment->statementid)) {
            access_denied('Send Payment');
        }

        $this->load->model('statements_model');
        $payment->statement_data = $this->statements_model->get($payment->statementid);
        set_mailing_constant();

        $paymentpdf = payment_pdf($payment);
        $filename   = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';

        $attach = $paymentpdf->Output($filename, 'S');

        $sent    = false;
        $sent_to = $this->input->post('sent_to');

        if (is_array($sent_to) && count($sent_to) > 0) {
            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    $contact = $this->clients_model->get_contact($contact_id);

                    $template = mail_template('statement_payment_recorded_to_customer', (array) $contact, $payment->statement_data, false, $payment->paymentid);

                    $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $filename,
                            'type'       => 'application/pdf',
                        ]);


                    if (get_option('attach_statement_to_payment_receipt_email') == 1) {
                        $statement_number = format_statement_number($payment->statementid);
                        set_mailing_constant();
                        $pdfInvoice           = statement_pdf($payment->statement_data);
                        $pdfInvoiceAttachment = $pdfInvoice->Output($statement_number . '.pdf', 'S');

                        $template->add_attachment([
                            'attachment' => $pdfInvoiceAttachment,
                            'filename'   => str_replace('/', '-', $statement_number) . '.pdf',
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        $sent = true;
                    }
                }
            }
        }

        // In case client use another language
        load_admin_language();
        set_alert($sent ? 'success' : 'danger', _l($sent ? 'payment_sent_successfully' : 'payment_sent_failed'));

        redirect(admin_url('payments/payment/' . $id));
    }

    /* Delete payment */
    public function delete($id)
    {
        if (!has_permission('payments', '', 'delete')) {
            access_denied('Delete Payment');
        }
        if (!$id) {
            redirect(admin_url('payments'));
        }
        $response = $this->payments_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('payment')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_lowercase')));
        }
        redirect(admin_url('payments'));
    }
}
