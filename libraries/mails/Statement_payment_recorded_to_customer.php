<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Statement_payment_recorded_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $contact;

    protected $statement;

    protected $subscription;

    protected $payment_id;

    public $slug = 'statement-payment-recorded';

    public $rel_type = 'statement';

    public function __construct($contact, $statement, $subscription, $payment_id)
    {
        parent::__construct();

        $this->contact      = $contact;
        $this->statement      = $statement;
        $this->subscription = $subscription;
        $this->payment_id   = $payment_id;
        // For SMS
        if ($this->subscription) {
            $this->set_merge_fields('subscriptions_merge_fields', $this->subscription);
        }

        $this->set_merge_fields('client_merge_fields', $this->statement->clientid, $this->contact['id']);
        $this->set_merge_fields('statement_merge_fields', $this->statement->id, $this->payment_id);
    }

    public function build()
    {
        $this->to($this->contact['email'])
        ->set_rel_id($this->statement->id);
    }
}
