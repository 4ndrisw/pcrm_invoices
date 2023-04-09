<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Statement_overdue_notice extends App_mail_template
{
    protected $for = 'customer';

    protected $statement;

    protected $contact;

    public $slug = 'statement-overdue-notice';

    public $rel_type = 'statement';

    public function __construct($statement, $contact)
    {
        parent::__construct();

        $this->statement = $statement;
        $this->contact = $contact;

        // For SMS
        $this->set_merge_fields('client_merge_fields', $this->statement->clientid, $this->contact['id']);
        $this->set_merge_fields('statement_merge_fields', $this->statement->id);
    }

    public function build()
    {
        $this->to($this->contact['email'])
        ->set_rel_id($this->statement->id);
    }
}
