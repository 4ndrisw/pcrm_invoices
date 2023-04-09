<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Statement_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $statement;

    protected $contact;

    public $slug = 'statement-send-to-client';

    public $rel_type = 'statement';

    public function __construct($statement, $contact, $cc = '')
    {
        parent::__construct();

        $this->statement = $statement;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->statements_model->get_attachments($this->statement->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('statement') . $this->statement->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_rel_id($this->statement->id)
        ->set_merge_fields('client_merge_fields', $this->statement->clientid, $this->contact->id)
        ->set_merge_fields('statement_merge_fields', $this->statement->id);
    }
}
