<?php

defined('BASEPATH') or exit('No direct script access allowed');


require_once('install/statements.php');
require_once('install/statement_activity.php');
require_once('install/statement_comments.php');
require_once('install/statementpaymentrecords.php');


$CI->db->query("
INSERT INTO `tblemailtemplates` ( `type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('statement', 'statement-send-to-client', 'english', 'Send Invoice to Customer', 'Invoice with number {statement_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">We have prepared the following statement for you: <strong># {statement_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>Invoice status</strong>: {statement_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the statement on the following link: <a href=\"{statement_link}\">{statement_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('statement', 'statement-payment-recorded', 'english', 'Invoice Payment Recorded (Sent to Customer)', 'Invoice Payment Recorded', '<span style=\"font-size: 12pt;\">Hello {contact_firstname}&nbsp;{contact_lastname}<br /><br /></span>Thank you for the payment. Find the payment details below:<br /><br />-------------------------------------------------<br /><br />Amount:&nbsp;<strong>{payment_total}<br /></strong>Date:&nbsp;<strong>{payment_date}</strong><br />Invoice number:&nbsp;<span style=\"font-size: 12pt;\"><strong># {statement_number}<br /><br /></strong></span>-------------------------------------------------<br /><br />You can always view the statement for this payment at the following link:&nbsp;<a href=\"{statement_link}\"><span style=\"font-size: 12pt;\">{statement_number}</span></a><br /><br />We are looking forward working with you.<br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('statement', 'statement-overdue-notice', 'english', 'Invoice Overdue Notice', 'Invoice Overdue Notice - {statement_number}', '<span style=\"font-size: 12pt;\">Hi {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">This is an overdue notice for statement <strong># {statement_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\">This statement was due: {statement_duedate}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the statement on the following link: <a href=\"{statement_link}\">{statement_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 0, 0),
('statement', 'statement-already-send', 'english', 'Invoice Already Sent to Customer', 'Invoice # {statement_number} ', '<span style=\"font-size: 12pt;\">Hi {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">At your request, here is the statement with number <strong># {statement_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the statement on the following link: <a href=\"{statement_link}\">{statement_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('statement', 'statement-payment-recorded-to-staff', 'english', 'Invoice Payment Recorded (Sent to Staff)', 'New Invoice Payment', '<span style=\"font-size: 12pt;\">Hi</span><br /><br /><span style=\"font-size: 12pt;\">Customer recorded payment for statement <strong># {statement_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the statement on the following link: <a href=\"{statement_link}\">{statement_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('statement', 'statement-due-notice', 'english', 'Invoice Due Notice', 'Your {statement_number} will be due soon', '<span style=\"font-size: 12pt;\">Hi {contact_firstname} {contact_lastname}<br /><br /></span>You statement <span style=\"font-size: 12pt;\"><strong># {statement_number} </strong>will be due on <strong>{statement_duedate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the statement on the following link: <a href=\"{statement_link}\">{statement_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 0, 0),
('statement', 'statements-batch-payments', 'english', 'Invoices Payments Recorded in Batch (Sent to Customer)', 'We have received your payments', 'Hello {contact_firstname} {contact_lastname}<br><br>Thank you for the payments. Please find the payments details below:<br><br>{batch_payments_list}<br><br>We are looking forward working with you.<br><br>Kind Regards,<br><br>{email_signature}', '{companyname} | CRM', '', 0, 0, 0),
('statement', 'statement-send-to-client', 'indonesia', 'Send Invoice to Customer [indonesia]', 'Invoice with number {statement_number} created', '', '{companyname} | CRM', NULL, 0, 1, 0),
('statement', 'statement-payment-recorded', 'indonesia', 'Invoice Payment Recorded (Sent to Customer) [indonesia]', 'Invoice Payment Recorded', '', '{companyname} | CRM', NULL, 0, 1, 0),
('statement', 'statement-overdue-notice', 'indonesia', 'Invoice Overdue Notice [indonesia]', 'Invoice Overdue Notice - {statement_number}', '', '{companyname} | CRM', NULL, 0, 0, 0),
('statement', 'statement-already-send', 'indonesia', 'Invoice Already Sent to Customer [indonesia]', 'Invoice # {statement_number} ', '', '{companyname} | CRM', NULL, 0, 1, 0),
('statement', 'statement-payment-recorded-to-staff', 'indonesia', 'Invoice Payment Recorded (Sent to Staff) [indonesia]', 'New Invoice Payment', '', '{companyname} | CRM', NULL, 0, 1, 0),
('statement', 'statement-due-notice', 'indonesia', 'Invoice Due Notice [indonesia]', 'Your {statement_number} will be due soon', '', '{companyname} | CRM', NULL, 0, 0, 0),
('statement', 'statements-batch-payments', 'indonesia', 'Invoices Payments Recorded in Batch (Sent to Customer) [indonesia]', 'We have received your payments', '', '{companyname} | CRM', NULL, 0, 0, 0);



");

// Add options for statements
add_option('delete_only_on_last_statement', 1);
add_option('statement_prefix', 'INV-');
add_option('next_statement_number', 1);
add_option('default_statement_assigned', 9);
add_option('statement_number_decrement_on_delete', 0);
add_option('statement_number_format', 4);
add_option('statement_year', date('Y'));
add_option('exclude_statement_from_client_area_with_draft_status', 1);
add_option('predefined_client_note_statement', '- Staf diatas untuk melakukan riksa uji pada peralatan tersebut.
- Staf diatas untuk membuat dokumentasi riksa uji sesuai kebutuhan.');
add_option('predefined_terms_statement', '- Pelaksanaan riksa uji harus mengikuti prosedur yang ditetapkan perusahaan pemilik alat.
- Dilarang membuat dokumentasi tanpa seizin perusahaan pemilik alat.
- Dokumen ini diterbitkan dari sistem CRM, tidak memerlukan tanda tangan dari PT. Cipta Mas Jaya');
add_option('statement_due_after', 1);
add_option('allow_staff_view_statements_assigned', 1);
add_option('show_assigned_on_statements', 1);
add_option('require_client_logged_in_to_view_statement', 0);

add_option('show_project_on_statement', 1);
add_option('statements_pipeline_limit', 1);
add_option('default_statements_pipeline_sort', 1);
add_option('statement_accept_identity_confirmation', 1);
add_option('statement_qrcode_size', '160');
add_option('statement_send_telegram_message', 0);


/*

DROP TABLE `tblstatements`, `tblstatement_activity`, `tblstatement_comments`, `tblstatementpaymentrecords`;

delete FROM `tbloptions` WHERE `name` LIKE '%statement%';
DELETE FROM `tblemailtemplates` WHERE `type` LIKE 'statement';



*/