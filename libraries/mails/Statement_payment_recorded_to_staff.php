<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Statement_payment_recorded_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $staff_email;

    protected $statement;

    protected $staffid;

    protected $payment_pdf;

    protected $payment_id;

    public $slug = 'statement-payment-recorded-to-staff';

    public $rel_type = 'staff';

    public function __construct($staff_email, $staffid, $statement, $payment_pdf, $payment_id)
    {
        parent::__construct();

        $this->staff_email = $staff_email;
        $this->staffid     = $staffid;
        $this->statement     = $statement;
        $this->payment_pdf = $payment_pdf;
        $this->payment_id  = $payment_id;
    }

    public function build()
    {
        $this->add_attachment([
                        'attachment' => $this->payment_pdf,
                        'filename'   => _l('payment') . '-' . $this->payment_id . '.pdf',
                        'type'       => 'application/pdf',
                    ]);


        $this->to($this->staff_email)
        ->set_rel_id($this->staffid)
        ->set_merge_fields('client_merge_fields',
            $this->statement->clientid,
            !is_client_logged_in() ? '' : get_contact_user_id()
        )
        ->set_merge_fields('statement_merge_fields', $this->statement->id, $this->payment_id);
    }
}
