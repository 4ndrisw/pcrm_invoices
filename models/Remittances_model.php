<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Remittances_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('statements_model');
    }

    /**
     * Get payment by ID
     * @param  mixed $id payment id
     * @return object
     */
    public function get($id)
    {
        $this->db->select('*,' . db_prefix() . 'statementpaymentrecords.id as paymentid');
        $this->db->join(db_prefix() . 'remittance_modes', db_prefix() . 'remittance_modes.id = ' . db_prefix() . 'statementpaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix() . 'statementpaymentrecords.id', 'asc');
        $this->db->where(db_prefix() . 'statementpaymentrecords.id', $id);
        $payment = $this->db->get(db_prefix() . 'statementpaymentrecords')->row();
        if (!$payment) {
            return false;
        }
        // Since version 1.0.1
        $this->load->model('remittance_modes_model');
        $payment_gateways = $this->remittance_modes_model->get_payment_gateways(true);
        if (is_null($payment->id)) {
            foreach ($payment_gateways as $gateway) {
                if ($payment->paymentmode == $gateway['id']) {
                    $payment->name = $gateway['name'];
                }
            }
        }

        return $payment;
    }

    /**
     * Get all statement payments
     * @param  mixed $statementid statementid
     * @return array
     */
    public function get_statement_payments($statementid)
    {
        $this->db->select('*,' . db_prefix() . 'statementpaymentrecords.id as paymentid');
        $this->db->join(db_prefix() . 'remittance_modes', db_prefix() . 'remittance_modes.id = ' . db_prefix() . 'statementpaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix() . 'statementpaymentrecords.id', 'asc');
        $this->db->where('statementid', $statementid);
        $payments = $this->db->get(db_prefix() . 'statementpaymentrecords')->result_array();
        // Since version 1.0.1
        $this->load->model('statements/remittance_modes_model');
        $payment_gateways = $this->remittance_modes_model->get_payment_gateways(true);
        $i                = 0;
        foreach ($payments as $payment) {
            if (is_null($payment['id'])) {
                foreach ($payment_gateways as $gateway) {
                    if ($payment['paymentmode'] == $gateway['id']) {
                        $payments[$i]['id']   = $gateway['id'];
                        $payments[$i]['name'] = $gateway['name'];
                    }
                }
            }
            $i++;
        }

        return $payments;
    }

    /**
     * Process statement payment offline or online
     * @since  Version 1.0.1
     * @param  array $data $_POST data
     * @return boolean
     */
    public function process_payment($data, $statementid = '')
    {
        // Offline payment mode from the admin side
        log_activity(json_encode($data));
        if (is_numeric($data['paymentmode'])) {
            if (is_staff_logged_in()) {
                $id = $this->add($data);

                return $id;
            }

            return false;

        // Is online payment mode request by client or staff
        } elseif (!is_numeric($data['paymentmode']) && !empty($data['paymentmode'])) {
            // This request will come from admin area only
            // If admin clicked the button that dont want to pay the statement from the getaways only want
            if (is_staff_logged_in() && has_permission('payments', '', 'create')) {
                if (isset($data['do_not_redirect'])) {
                    $id = $this->add($data);

                    return $id;
                }
            }

            if (!is_numeric($statementid)) {
                if (!isset($data['statementid'])) {
                    die('No statement specified');
                }
                $statementid = $data['statementid'];
            }

            if (isset($data['do_not_send_email_template'])) {
                unset($data['do_not_send_email_template']);
                $this->session->set_userdata([
                    'do_not_send_email_template' => true,
                ]);
            }

            $statement = $this->statements_model->get($statementid);
            // Check if request coming from admin area and the user added note so we can insert the note also when the payment is recorded
            if (isset($data['note']) && $data['note'] != '') {
                $this->session->set_userdata([
                    'payment_admin_note' => $data['note'],
                ]);
            }

            if (get_option('allow_payment_amount_to_be_modified') == 0) {
                $data['amount'] = get_statement_total_left_to_pay($statementid, $statement->total);
            }

            $data['statementid'] = $statementid;
            $data['statement']   = $statement;
            $data              = hooks()->apply_filters('before_process_gateway_func', $data);

            $this->load->model('remittance_modes_model');
            $gateway = $this->remittance_modes_model->get($data['paymentmode']);

            $gateway->instance->process_payment($data);
        }

        return false;
    }

    /**
     * Check whether payment exist by transaction id for the given statement
     *
     * @param  int $transactionId
     * @param  int|null $statementId
     *
     * @return bool
     */
    public function transaction_exists($transactionId, $statementId = null)
    {
        return total_rows('statementpaymentrecords', array_filter([
            'transactionid' => $transactionId,
            'statementid'     => $statementId,
        ])) > 0;
    }

    /**
     * Record new payment
     * @param array $data payment data
     * @return boolean
     */
    public function add($data, $subscription = false)
    {
        // Check if field do not redirect to payment processor is set so we can unset from the database
        if (isset($data['do_not_redirect'])) {
            unset($data['do_not_redirect']);
        }

        if ($subscription != false) {
            $after_success = get_option('after_subscription_payment_captured');

            if ($after_success == 'nothing' || $after_success == 'send_statement') {
                $data['do_not_send_email_template'] = true;
            }
        }

        if (isset($data['do_not_send_email_template'])) {
            unset($data['do_not_send_email_template']);
            $do_not_send_email_template = true;
        } elseif ($this->session->has_userdata('do_not_send_email_template')) {
            $do_not_send_email_template = true;
            $this->session->unset_userdata('do_not_send_email_template');
        }

        if (is_staff_logged_in()) {
            if (isset($data['date'])) {
                $data['date'] = to_sql_date($data['date']);
            } else {
                $data['date'] = date('Y-m-d H:i:s');
            }
            if (isset($data['note'])) {
                $data['note'] = nl2br($data['note']);
            } elseif ($this->session->has_userdata('payment_admin_note')) {
                $data['note'] = nl2br($this->session->userdata('payment_admin_note'));
                $this->session->unset_userdata('payment_admin_note');
            }
        } else {
            $data['date'] = date('Y-m-d H:i:s');
        }

        $data['daterecorded'] = date('Y-m-d H:i:s');
        $data                 = hooks()->apply_filters('before_payment_recorded', $data);

        $this->db->insert(db_prefix() . 'statementpaymentrecords', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $statement      = $this->statements_model->get($data['statementid']);
            $force_update = false;

            if (!class_exists('Statements_model', false)) {
                $this->load->model('statements_model');
            }

            if ($statement->status == Statements_model::STATUS_DRAFT) {
                $force_update = true;
                // update statement number for statement with draft - V2.7.2
                $this->statements_model->change_statement_number_when_status_draft($statement->id);
            }

            update_statement_status($data['statementid'], $force_update);

            $activity_lang_key = 'statement_activity_payment_made_by_staff';
            if (!is_staff_logged_in()) {
                $activity_lang_key = 'statement_activity_payment_made_by_client';
            }

            $this->statements_model->log_statement_activity($data['statementid'], $activity_lang_key, !is_staff_logged_in() ? true : false, serialize([
                app_format_money($data['amount'], $statement->currency_name),
                '<a href="' . admin_url('payments/payment/' . $insert_id) . '" target="_blank">#' . $insert_id . '</a>',
            ]));

            log_activity('Payment Recorded [ID:' . $insert_id . ', Statement Number: ' . format_statement_number($statement->id) . ', Total: ' . app_format_money($data['amount'], $statement->currency_name) . ']');

            // Send email to the client that the payment is recorded
            $payment               = $this->get($insert_id);
            $payment->statement_data = $this->statements_model->get($payment->statementid);
            set_mailing_constant();
            $paymentpdf           = remittancepdf($payment);
            $payment_pdf_filename = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';
            $attach               = $paymentpdf->Output($payment_pdf_filename, 'S');

            if (!isset($do_not_send_email_template)
                || ($subscription != false && $after_success == 'send_statement_and_receipt')
                || ($subscription != false && $after_success == 'send_statement')
            ) {
                $template_name        = 'statement_payment_recorded_to_customer';
                $pdfStatementAttachment = false;
                $attachPaymentReceipt = true;
                $emails_sent          = [];

                $where = ['active' => 1, 'statement_emails' => 1];

                if ($subscription != false) {
                    $where['is_primary'] = 1;
                    $template_name       = 'subscription_payment_succeeded';

                    if ($after_success == 'send_statement_and_receipt' || $after_success == 'send_statement') {
                        $statement_number = format_statement_number($payment->statementid);
                        set_mailing_constant();
                        $pdfStatement           = statement_pdf($payment->statement_data);
                        $pdfStatementAttachment = $pdfStatement->Output($statement_number . '.pdf', 'S');

                        if ($after_success == 'send_statement') {
                            $attachPaymentReceipt = false;
                        }
                    }
                    // Is from settings: Send Payment Receipt
                } else {
                    if (get_option('attach_statement_to_payment_receipt_email') == 1) {
                        $statement_number = format_statement_number($payment->statementid);
                        set_mailing_constant();
                        $pdfStatement           = statement_pdf($payment->statement_data);
                        $pdfStatementAttachment = $pdfStatement->Output($statement_number . '.pdf', 'S');
                    }
                }

                $contacts = $this->clients_model->get_contacts($statement->clientid, $where);

                foreach ($contacts as $contact) {
                    $template = mail_template(
                        $template_name,
                        $contact,
                        $statement,
                        $subscription,
                        $payment->paymentid
                    );

                    if ($attachPaymentReceipt) {
                        $template->add_attachment([
                                'attachment' => $attach,
                                'filename'   => $payment_pdf_filename,
                                'type'       => 'application/pdf',
                            ]);
                    }

                    if ($pdfStatementAttachment) {
                        $template->add_attachment([
                            'attachment' => $pdfStatementAttachment,
                            'filename'   => str_replace('/', '-', $statement_number) . '.pdf',
                            'type'       => 'application/pdf',
                        ]);
                    }
                    $merge_fields = $template->get_merge_fields();

                    if ($template->send()) {
                        array_push($emails_sent, $contact['email']);
                    }

                    $this->app_sms->trigger(SMS_TRIGGER_PAYMENT_RECORDED, $contact['phonenumber'], $merge_fields);
                }

                if (count($emails_sent) > 0) {
                    $additional_activity_data = serialize([
                       implode(', ', $emails_sent),
                     ]);
                    $activity_lang_key = 'statement_activity_record_payment_email_to_customer';
                    if ($subscription != false) {
                        $activity_lang_key = 'statement_activity_subscription_payment_succeeded';
                    }
                    $this->statements_model->log_statement_activity($statement->id, $activity_lang_key, false, $additional_activity_data);
                }
            }

            $this->db->where('staffid', $statement->addedfrom);
            $this->db->or_where('staffid', $statement->sale_agent);
            $staff_statement = $this->db->get(db_prefix() . 'staff')->result_array();

            $notifiedUsers = [];
            foreach ($staff_statement as $member) {
                if (get_option('notification_when_customer_pay_statement') == 1) {
                    if (is_staff_logged_in() && $member['staffid'] == get_staff_user_id()) {
                        continue;
                    }
                    // E.q. had permissions create not don't have, so we must re-check this
                    if (user_can_view_statement($statement->id, $member['staffid'])) {
                        $notified = add_notification([
                        'fromcompany'     => true,
                        'touserid'        => $member['staffid'],
                        'description'     => 'not_statement_payment_recorded',
                        'link'            => 'payments/payment/' . $insert_id,
                        'additional_data' => serialize([
                            format_statement_number($statement->id),
                        ]),
                    ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                        send_mail_template(
                            'statement_payment_recorded_to_staff',
                            $member['email'],
                            $member['staffid'],
                            $statement,
                            $attach,
                            $payment->id
                        );
                    }
                }
            }

            pusher_trigger_notification($notifiedUsers);

            hooks()->do_action('after_payment_added', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Update payment
     * @param  array $data payment data
     * @param  mixed $id   paymentid
     * @return boolean
     */
    public function update($data, $id)
    {
        $payment      = $this->get($id);
        $updated      = false;
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);

        $data = hooks()->apply_filters('before_payment_updated', $data, $id);

        $this->db->where('id', $id);
        $this->db->update('statementpaymentrecords', $data);

        if ($this->db->affected_rows() > 0) {
            if ($data['amount'] != $payment->amount) {
                update_statement_status($payment->statementid);
            }

            $updated = true;
        }

        hooks()->do_action('after_payment_updated', [
            'id'      => $id,
            'data'    => $data,
            'payment' => $payment,
            'updated' => &$updated,
        ]);

        if ($updated) {
            log_activity('Payment Updated [Number:' . $id . ']');
        }

        return $updated;
    }

    /**
     * Delete payment from database
     * @param  mixed $id paymentid
     * @return boolean
     */
    public function delete($id)
    {
        $current         = $this->get($id);
        $current_statement = $this->statements_model->get($current->statementid);
        $statementid       = $current->statementid;
        hooks()->do_action('before_payment_deleted', [
            'paymentid' => $id,
            'statementid' => $statementid,
        ]);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'statementpaymentrecords');
        if ($this->db->affected_rows() > 0) {
            update_statement_status($statementid);
            $this->statements_model->log_statement_activity($statementid, 'statement_activity_payment_deleted', false, serialize([
                $current->paymentid,
                app_format_money($current->amount, $current_statement->currency_name),
            ]));
            log_activity('Payment Deleted [ID:' . $id . ', Statement Number: ' . format_statement_number($current->id) . ']');

            hooks()->do_action('after_payment_deleted', [
                'paymentid' => $id,
                'statementid' => $statementid,
            ]);

            return true;
        }

        return false;
    }

    public function add_batch_payment($paymentsData)
    {
        $sendBatchPaymentEmail = true;
        if (isset($paymentsData['do_not_send_statement_payment_recorded'])) {
            $sendBatchPaymentEmail = false;
        }

        $paymentIds = [];
        foreach ($paymentsData['statement'] as $data) {
            if (empty($data['statementid']) || empty($data['amount']) || empty($data['date']) || empty('paymentmode')) {
                continue;
            }

            $data['date']         = to_sql_date($data['date']);
            $data['daterecorded'] = date('Y-m-d H:i:s');
            $data                 = hooks()->apply_filters('before_payment_recorded', $data);

            $this->db->insert(db_prefix() . 'statementpaymentrecords', $data);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                $paymentIds[] = $insert_id;
                $statement      = $this->statements_model->get($data['statementid']);
                $force_update = false;

                if (!class_exists('Statements_model', false)) {
                    $this->load->model('statements_model');
                }

                if ($statement->status == Statements_model::STATUS_DRAFT) {
                    $force_update = true;
                    // update statement number for statement with draft - V2.7.2
                    $this->statements_model->change_statement_number_when_status_draft($statement->id);
                }
                update_statement_status($data['statementid'], $force_update);

                $this->statements_model->log_statement_activity(
                    $data['statementid'],
                    'statement_activity_payment_made_by_staff',
                    false,
                    serialize([
                        app_format_money($data['amount'], $statement->currency_name),
                        '<a href="' . admin_url('payments/payment/' . $insert_id) . '" target="_blank">#' . $insert_id . '</a>',
                    ])
                );
                log_activity('Payment Recorded [ID:' . $insert_id . ', Statement Number: ' . format_statement_number($statement->id) . ', Total: ' . app_format_money(
                    $data['amount'],
                    $statement->currency_name
                ) . ']');
            }
            hooks()->do_action('after_payment_added', $insert_id);
        }

        if (count($paymentIds) > 0 && $sendBatchPaymentEmail) {
            $this->send_batch_payment_notification_to_customers($paymentIds);
        }

        return count($paymentIds);
    }

    private function send_batch_payment_notification_to_customers($paymentIds)
    {
        $paymentData = $this->db
            ->select(db_prefix() . 'statementpaymentrecords.*,' . db_prefix() . 'statements.currency,' . db_prefix() . 'statements.clientId,' . db_prefix() . 'statements.hash')
            ->join(db_prefix() . 'statements', 'statementpaymentrecords.statementid=statements.id')
            ->where_in('statementpaymentrecords.id', $paymentIds)
            ->get(db_prefix() . 'statementpaymentrecords')
            ->result();

        // used collection groupBy as a workaround for mysql8.0 only full group mode
        $paymentData = collect($paymentData)->groupBy('clientId');

        foreach ($paymentData as $clientId => $payments) {
            $contacts = $this->get_contacts_for_payment_emails($clientId);
            foreach ($contacts as $contact) {
                if (count($payments) === 1) {
                    $this->send_statement_payment_recorded($payments[0]->id, $contact);
                } else {
                    $template = mail_template('statement_batch_payments', $payments, $contact);
                    foreach ($payments as $payment) {
                        $payment               = $this->get($payment->id);
                        $payment->statement_data = $this->statements_model->get($payment->statementid);
                        $template              = $this->_add_payment_mail_attachments_to_template($template, $payment);
                    }
                    $template->send();
                }
            }
        }
    }

    public function send_statement_payment_recorded($id, $contact)
    {
        if (!class_exists('Statements_model', false)) {
            $this->load->model('statements_model');
        }

        // to get structure matching payment_pdf()
        $payment               = $this->get($id);
        $payment->statement_data = $this->statements_model->get($payment->statementid);
        $template              = mail_template('statement_payment_recorded_to_customer', (array) $contact, $payment->statement_data, false, $id);
        $template              = $this->_add_payment_mail_attachments_to_template($template, $payment);

        return $template->send();
    }

    private function _add_payment_mail_attachments_to_template($template, $payment)
    {
        set_mailing_constant();

        $paymentPDF = payment_pdf($payment);
        $filename   = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';
        $attach     = $paymentPDF->Output($filename, 'S');
        $template->add_attachment([
            'attachment' => $attach,
            'filename'   => $filename,
            'type'       => 'application/pdf',
        ]);

        if (get_option('attach_statement_to_payment_receipt_email') == 1) {
            $statement_number = format_statement_number($payment->statementid);
            set_mailing_constant();
            $pdfStatement           = statement_pdf($payment->statement_data);
            $pdfStatementAttachment = $pdfStatement->Output($statement_number . '.pdf', 'S');

            $template->add_attachment([
                'attachment' => $pdfStatementAttachment,
                'filename'   => str_replace('/', '-', $statement_number) . '.pdf',
                'type'       => 'application/pdf',
            ]);
        }

        return $template;
    }

    private function get_contacts_for_payment_emails($client_id)
    {
        if (!class_exists('Clients_model', false)) {
            $this->load->model('clients_model');
        }

        return $this->clients_model->get_contacts($client_id, [
            'active' => 1, 'statement_emails' => 1,
        ]);
    }
}
