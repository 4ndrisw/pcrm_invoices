<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mystatement extends ClientsController
{
    public function index($id, $hash)
    {
        check_statement_restrictions($id, $hash);
        $statement = $this->statements_model->get($id);

        $statement = hooks()->apply_filters('before_client_view_statement', $statement);

        if (!is_client_logged_in()) {
            load_client_language($statement->clientid);
        }

        // Handle Statement PDF generator
        if ($this->input->post('statementpdf')) {
            try {
                $pdf = statement_pdf($statement);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            $statement_number = format_statement_number($statement->id);
            $companyname    = get_option('statement_company_name');
            if ($companyname != '') {
                $statement_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }
            $pdf->Output(mb_strtoupper(slug_it($statement_number), 'UTF-8') . '.pdf', 'D');
            die();
        }

        // Handle $_POST payment
        if ($this->input->post('make_payment')) {
            $this->load->model('payments_model');
            if (!$this->input->post('paymentmode')) {
                set_alert('warning', _l('statement_html_payment_modes_not_selected'));
                redirect(site_url('statement/' . $id . '/' . $hash));
            } elseif ((!$this->input->post('amount') || $this->input->post('amount') == 0) && get_option('allow_payment_amount_to_be_modified') == 1) {
                set_alert('warning', _l('statement_html_amount_blank'));
                redirect(site_url('statement/' . $id . '/' . $hash));
            }
            $this->payments_model->process_payment($this->input->post(), $id);
        }

        if ($this->input->post('paymentpdf')) {
            $payment = $this->payments_model->get($this->input->post('paymentpdf'));
            // Confirm that the payment is related to the statement.
            if ($payment->statementid == $id) {
                $payment->statement_data = $this->statements_model->get($payment->statementid);
                $paymentpdf            = payment_pdf($payment);
                $paymentpdf->Output(mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf', 'D');
                die;
            }
        }

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $this->load->library('app_number_to_word', [
            'clientid' => $statement->clientid,
        ], 'numberword');
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['payments']      = $this->payments_model->get_statement_payments($id);
        $data['payment_modes'] = $this->payment_modes_model->get();
        $data['title']         = format_statement_number($statement->id);
        $this->disableNavigation();
        $this->disableSubMenu();
        $data['hash']      = $hash;
        $data['statement']   = hooks()->apply_filters('statement_html_pdf_data', $statement);
        $data['bodyclass'] = 'viewstatement';
        $this->data($data);
        $this->view('statementhtml');
        add_views_tracking('statement', $id);
        hooks()->do_action('statement_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }
}
