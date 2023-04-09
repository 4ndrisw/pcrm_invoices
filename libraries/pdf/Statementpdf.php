<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Statementpdf extends App_pdf
{
    protected $statement;

    private $statement_number;

    public function __construct($statement, $tag = '')
    {
        $this->load_language($statement->clientid);
        $statement                = hooks()->apply_filters('statement_html_pdf_data', $statement);
        $GLOBALS['statement_pdf'] = $statement;

        parent::__construct();

        if (!class_exists('Invoices_model', false)) {
            $this->ci->load->model('statements_model');
        }

        $this->tag            = $tag;
        $this->statement        = $statement;
        $this->statement_number = format_statement_number($this->statement->id);

        $this->SetTitle($this->statement_number);
    }

    public function prepare()
    {
        $this->with_number_to_word($this->statement->clientid);

        $this->set_view_vars([
            'status'         => $this->statement->status,
            'statement_number' => $this->statement_number,
            'payment_modes'  => $this->get_payment_modes(),
            'statement'        => $this->statement,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'statement';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_statementpdf.php';
        //$actualPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/statementpdf.php';
        $actualPath = module_views_path('statements','themes/' . active_clients_theme() . '/views/statement/statement_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }

    private function get_payment_modes()
    {
        $this->ci->load->model('payment_modes_model');
        $payment_modes = $this->ci->payment_modes_model->get();

        // In case user want to include {statement_number} or {client_id} in PDF offline mode description
        foreach ($payment_modes as $key => $mode) {
            if (isset($mode['description'])) {
                $payment_modes[$key]['description'] = str_replace('{statement_number}', $this->statement_number, $mode['description']);
                $payment_modes[$key]['description'] = str_replace('{client_id}', $this->statement->clientid, $mode['description']);
            }
        }

        return $payment_modes;
    }
}
