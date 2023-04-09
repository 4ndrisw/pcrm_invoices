<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Statement_batch_payments extends App_mail_template
{
    public $slug = 'statements-batch-payments';
    public $rel_type = 'statement';
    protected $for = 'customer';
    protected $contact;
    private $payments;

    /**
     * @param object $payments
     * @param array $contact
     */
    public function __construct($payments, $contact)
    {
        parent::__construct();
        $this->contact = $contact;
        $this->payments = $payments;
    }

    public function build()
    {
        $this->to($this->contact['email'])
            ->set_merge_fields('client_merge_fields', $this->contact['userid'], $this->contact['id'])
            ->set_merge_fields('statement_batch_payments_merge_fields', $this->payments);
    }
}
