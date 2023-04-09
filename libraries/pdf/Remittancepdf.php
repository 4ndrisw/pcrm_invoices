<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Remittancepdf extends App_pdf
{
    protected $payment;

    public function __construct($payment, $tag = '')
    {
        $GLOBALS['payment_pdf'] = $payment;

        $this->load_language($payment->statement_data->clientid);

        parent::__construct();

        if (!class_exists('Statements_model', false)) {
            $this->ci->load->model('Statements_model');
        }

        $this->payment = $payment;
        $this->tag     = $tag;

        $this->SetTitle(_l('payment') . ' #' . $this->payment->paymentid);
    }

    public function prepare()
    {
        $amountDue = ($this->payment->statement_data->status != Statements_model::STATUS_PAID && $this->payment->statement_data->status != Statements_model::STATUS_CANCELLED ? true : false);

        $this->set_view_vars([
            'payment'   => $this->payment,
            'amountDue' => $amountDue,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'payment';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_paymentpdf.php';
        $actualPath = module_views_path('statements','themes/' . active_clients_theme() . '/views/remittance/remittance_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
