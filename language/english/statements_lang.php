<?php


# Version 1.1.0

# Invoice General
$lang['statement_status_paid']                   = 'Paid';
$lang['statement_status_unpaid']                 = 'Unpaid';
$lang['statement_status_overdue']                = 'Overdue';
$lang['statement_status_not_paid_completely']    = 'Partially Paid';
$lang['statement_pdf_heading']                   = 'INVOICE';
$lang['statement_table_item_heading']            = 'Item';
$lang['statement_table_quantity_heading']        = 'Qty';
$lang['statement_table_rate_heading']            = 'Rate';
$lang['statement_table_tax_heading']             = 'Tax';
$lang['statement_table_amount_heading']          = 'Amount';
$lang['statement_subtotal']                      = 'Sub Total';
$lang['statement_adjustment']                    = 'Adjustment';
$lang['statement_total']                         = 'Total';
$lang['statement_bill_to']                       = 'Bill To';
$lang['statement_data_date']                     = 'Invoice Date:';
$lang['statement_data_duedate']                  = 'Due Date:';
$lang['statement_received_payments']             = 'Transactions';
$lang['statement_no_payments_found']             = 'No payments found for this invoice';
$lang['statement_note']                          = 'Note:';
$lang['statement_payments_table_number_heading'] = 'Payment #';
$lang['statement_payments_table_mode_heading']   = 'Payment Mode';
$lang['statement_payments_table_date_heading']   = 'Date';
$lang['statement_payments_table_amount_heading'] = 'Amount';

$lang['client_statements_tab']                     = 'Invoices';
$lang['contracts_statements_tab']                  = 'Contracts';

$lang['email_template_statements_fields_heading'] = 'Invoices';
$lang['email_template_clients_fields_heading']  = 'Customers';


# Invoice Items
$lang['statement_items']                     = 'Invoice Items';
$lang['statement_item']                      = 'Invoice Item';
$lang['new_statement_item']                  = 'New Item';
$lang['statement_item_lowercase']            = 'statement item';
$lang['statement_items_list_description']    = 'Description';
$lang['statement_items_list_rate']           = 'Rate';
$lang['statement_item_add_edit_description'] = 'Description';
$lang['statement_item_add_edit_rate']        = 'Rate';
$lang['statement_item_edit_heading']         = 'Edit Item';
$lang['statement_item_add_heading']          = 'Add New Item';

$lang['payments_table_statementnumber_heading'] = 'Invoice #';

$lang['payment_edit_for_statement']             = 'Payment for Invoice';

# Invoices
$lang['statements']                                = 'Invoices';
$lang['statement']                                 = 'Invoice';
$lang['statement_lowercase']                       = 'statement';
$lang['create_new_statement']                      = 'Create New Invoice';
$lang['view_statement']                            = 'View Invoice';
$lang['statement_payment_recorded']                = 'Invoice Payment Recorded';
$lang['statement_payment_record_failed']           = 'Failed to Record Invoice Payment';
$lang['statement_sent_to_client_success']          = 'The invoice is sent successfully to the client';
$lang['statement_sent_to_client_fail']             = 'Problem while sending the invoice';
$lang['statement_reminder_send_problem']           = 'Problem sending invoice overdue reminder';
$lang['statement_overdue_reminder_sent']           = 'Invoice Overdue Reminder Successfully Sent';
$lang['statement_details']                         = 'Invoice Details';
$lang['statement_view']                            = 'View Invoice';
$lang['statement_select_customer']                 = 'Customer';
$lang['statement_add_edit_number']                 = 'Invoice Number';
$lang['statement_add_edit_date']                   = 'Invoice Date';
$lang['statement_add_edit_duedate']                = 'Due Date';
$lang['statement_add_edit_currency']               = 'Currency';
$lang['statement_add_edit_client_note']            = 'Client Note';
$lang['statement_add_edit_admin_note']             = 'Admin Note';
$lang['statements_toggle_table_tooltip']           = 'Toggle Table';
$lang['edit_statement_tooltip']                    = 'Edit Invoice';
$lang['delete_statement_tooltip']                  = 'Delete Invoice. Note: All payments regarding to this invoice will be deleted (if any).';
$lang['statement_sent_to_email_tooltip']           = 'Send to Email';
$lang['statement_already_send_to_client_tooltip']  = 'This invoice is already sent to the client %s';
$lang['send_overdue_notice_tooltip']             = 'Send Overdue Notice';
$lang['statement_view_activity_tooltip']           = 'Activity Log';
$lang['statement_record_payment']                  = 'Record Payment';
$lang['statement_send_to_client_modal_heading']    = 'Send invoice to client';
$lang['statement_send_to_client_attach_pdf']       = 'Attach Invoice PDF';
$lang['statement_send_to_client_preview_template'] = 'Preview Email Template';
$lang['statement_dt_table_heading_number']         = 'Invoice #';
$lang['statement_dt_table_heading_date']           = 'Date';
$lang['statement_dt_table_heading_client']         = 'Customer';
$lang['statement_dt_table_heading_duedate']        = 'Due Date';
$lang['statement_dt_table_heading_amount']         = 'Amount';
$lang['statement_dt_table_heading_status']         = 'Status';
$lang['record_payment_for_statement']              = 'Record Payment for';
$lang['record_payment_amount_received']          = 'Amount Received';
$lang['record_payment_date']                     = 'Payment Date';
$lang['record_payment_leave_note']               = 'Leave a note';
$lang['statement_payments_received']               = 'Payments Received';
$lang['statement_record_payment_note_placeholder'] = 'Admin Note';
$lang['no_payments_found']                       = 'No Payments found for this invoice';

$lang['report_sales_base_currency_select_explanation']    = 'You need to select currency because you have invoices with different currency';
$lang['reports_sales_dt_customers_total_statements']        = 'Total Invoices';
$lang['settings_cron_send_overdue_reminder']                       = 'Send invoice overdue reminder';
$lang['settings_cron_send_overdue_reminder_tooltip']               = 'Send overdue email to client when invoice status updated to overdue from Cron Job';
$lang['automatically_send_statement_overdue_reminder_after']         = 'Auto send reminder after (days)';
$lang['automatically_resend_statement_overdue_reminder_after']       = 'Auto re-send reminder after (days)';
$lang['settings_sales_statement_prefix']                             = 'Invoice Number Prefix';
$lang['settings_sales_require_client_logged_in_to_view_statement']   = 'Require client to be logged in to view invoice';
$lang['settings_sales_next_statement_number']                        = 'Next Invoice Number';
$lang['settings_sales_next_statement_number_tooltip']                = 'Set this field to 1 if you want to start from beginning';
$lang['settings_sales_decrement_statement_number_on_delete']         = 'Decrement invoice number on delete';
$lang['settings_sales_decrement_statement_number_on_delete_tooltip'] = 'Do you want to decrement the invoice number when the last invoice is deleted? eq. If is set this option to YES and before invoice delete the next invoice number is 15 the next invoice number will decrement to 14. If is set to NO the number will remain to 15.  If you have setup delete only on last invoice to NO you should set this option to NO too to keep the next invoice number not decremented.';
$lang['settings_sales_statement_number_format']                      = 'Invoice Number Format';
$lang['settings_sales_statement_number_format_year_based']           = 'Year Based';
$lang['settings_sales_statement_number_format_number_based']         = 'Number Based (000001)';
$lang['settings_sales_company_info_note']                          = 'These information will be displayed on invoices/estimates/payments and other PDF documents where company info is required';
$lang['user_sent_overdue_reminder'] = '%s sent invoice overdue reminder';
# Home
$lang['clients_quick_statement_info']           = 'Quick Invoices Info';
$lang['clients_home_currency_select_tooltip'] = 'You need to select currency because you have invoices with different currency';

# Invoices
$lang['clients_statement_html_btn_download'] = 'Download';
$lang['clients_my_statements']               = 'Invoices';
$lang['clients_statement_dt_number']         = 'Invoice #';
$lang['clients_statement_dt_date']           = 'Date';
$lang['clients_statement_dt_duedate']        = 'Due Date';
$lang['clients_statement_dt_amount']         = 'Amount';
$lang['clients_statement_dt_status']         = 'Status';

$lang['clients_nav_statements']  = 'Invoices';
$lang['payment_table_statement_number']                  = 'Invoice Number';
$lang['payment_table_statement_date']                    = 'Invoice Date';
$lang['payment_table_statement_amount_total']            = 'Invoice Amount';
# Invoices
$lang['view_statement_as_customer_tooltip']                                     = 'View invoice as customer';
$lang['statement_add_edit_recurring']                                           = 'Recurring Invoice?';
$lang['statement_add_edit_recurring_no']                                        = 'No';
$lang['statement_add_edit_recurring_month']                                     = 'Every %s month';
$lang['statement_add_edit_recurring_months']                                    = 'Every %s months';
$lang['statements_list_all']                                                    = 'All';
$lang['statements_list_not_have_payment']                                       = 'Invoices with no payment records';
$lang['statements_list_recurring']                                              = 'Recurring Invoices';
$lang['statements_list_made_payment_by']                                        = 'Made Payment by %s';
$lang['statements_create_statement_from_recurring_only_on_paid_statements']         = 'Create new invoice from recurring invoice only if the invoice is with status paid?';
$lang['statements_create_statement_from_recurring_only_on_paid_statements_tooltip'] = 'If this field is set to YES and the recurring invoices is not with status PAID, the new invoice will NOT be created.';
$lang['view_statement_pdf_link_pay']                                            = 'Pay Invoice';

# Payment modes
$lang['payment_mode_add_edit_description']         = 'Bank Accounts / Description';
$lang['payment_mode_add_edit_description_tooltip'] = 'You can set here bank accounts information. Will be shown on HTML Invoice';

# Payments
$lang['payment_for_statement'] = 'Payment for Invoice';
$lang['payment_total']       = 'Total: %s';

# Invoice
$lang['statement_html_online_payment']             = 'Online Payment';
$lang['statement_html_online_payment_button_text'] = 'Pay Now';
$lang['statement_html_payment_modes_not_selected'] = 'Please Select Payment Mode';
$lang['statement_html_amount_blank']               = 'Total amount cant be blank or zero';
$lang['statement_html_offline_payment']            = 'Offline Payment';
$lang['statement_html_amount']                     = 'Amount';
# Invoice
$lang['statement_add_edit_advanced_options']                = 'Advanced Options';
$lang['statement_add_edit_allowed_payment_modes']           = 'Allowed payment modes for this invoice';
$lang['statement_add_edit_recurring_statements_from_statement'] = 'Created invoices from this recurring invoice';
$lang['statement_add_edit_no_payment_modes_found']          = 'No payment modes found.';
$lang['statement_html_total_pay']                           = 'Total: %s';
$lang['client_zip_statements']      = 'ZIP Invoices';
$lang['settings_delete_only_on_last_statement']                       = 'Delete invoice allowed only on last invoice';
$lang['settings_delete_only_on_last_estimate']                      = 'Delete estimate allowed only on last invoice';
$lang['settings_sales_heading_statement']                             = 'Invoice';
$lang['settings_sales_heading_estimates']                           = 'Estimates';
$lang['settings_sales_cron_statement_heading']                        = 'Invoice';
# Invoice General
$lang['statement_discount'] = 'Discount';
$lang['settings_estimate_auto_convert_to_statement_on_client_accept']   = 'Auto convert the estimate to invoice after client accept';
$lang['estimate_statementd_date']                   = 'Estimate Invoiced on %s';
$lang['estimate_convert_to_statement']              = 'Convert to Invoice';
$lang['clients_estimate_statementd_successfully'] = 'Thank you for accepting the estimate. Please review the created invoice for the estimate';
$lang['clients_estimate_accepted_not_statementd'] = 'Thank you for accepting this estimate';
$lang['statement_item_long_description'] = 'Long Description';
$lang['calendar_statement']           = 'Invoice';
$lang['settings_show_sale_agent_on_statements']       = 'Show Sale Agent On Invoice';

$lang['statement_attach_file']           = 'Attach File';
$lang['statement_mark_as_sent']          = 'Mark as Sent';
$lang['statement_marked_as_sent']        = 'Invoice marked as sent successfully';
$lang['statement_marked_as_sent_failed'] = 'Failed to mark invoice as sent';

$lang['expense_converted_to_statement']                 = 'Expense successfully converted to invoice';
$lang['expense_converted_to_statement_fail']            = 'Failed to convert this expense to invoice check error log.';
$lang['expenses_list_statementd']                       = 'Invoiced';

$lang['expense_statement_delete_not_allowed']           = 'You cant delete this expense. The expense is already invoiced.';
$lang['expense_convert_to_statement']                   = 'Convert To Invoice';
$lang['expenses_list_unbilled']                       = 'Not Invoiced';
$lang['expense_statement_not_created']                  = 'Invoice Not Created';
$lang['expense_not_billed']                           = 'Invoice Not Paid';
$lang['expense_already_statementd']                     = 'This expense is already invoiced';
$lang['expense_recurring_auto_create_statement']        = 'Auto Create Invoice';
$lang['expense_recurring_send_custom_on_renew']       = 'Send the invoice to customer email when expense re-created';
$lang['expense_recurring_autocreate_statement_tooltip'] = 'If this option is checked the invoice for the customer will be auto created when the expense will be renewed.';

$lang['custom_field_statement']     = 'Invoice';
# Invoices General
$lang['statement_estimate_general_options'] = 'General Options';
$lang['statement_table_item_description']   = 'Description';
$lang['statement_recurring_indicator']      = 'Recurring';
$lang['statement_copy']              = 'Copy Invoice';
$lang['statement_copy_success']      = 'Invoice copied successfully';
$lang['statement_copy_fail']         = 'Failed to copy invoice';
$lang['statement_due_after_help']    = 'Set zero to avoid calculation';
$lang['show_shipping_on_statement']  = 'Show shipping details in invoice';
$lang['customer_update_address_info_on_statements']              = 'Update the shipping/billing info on all previous invoices/estimates';
$lang['customer_update_address_info_on_statements_help']         = 'If you check this field shipping and billing info will be updated to all invoices and estimates. Note: Invoices with status paid won\'t be affected.';
$lang['expense_list_statement']  = 'Invoiced';
$lang['expense_list_billed']   = 'Billed';
$lang['expense_list_unbilled'] = 'Not Invoiced';
$lang['settings_sales_statement_due_after']                                    = 'Invoice due after (days)';
$lang['show_statements_on_calendar']                                           = 'Invoices';
$lang['bulk_export_pdf_statements']      = 'Invoices';
$lang['customer_permission_statement']  = 'Invoices';
# Invoices
$lang['delete_statement'] = 'Delete';
$lang['show_statement_estimate_status_on_pdf']                      = 'Show invoice/estimate status on PDF';
$lang['proposal_convert_statement']               = 'Invoice';
$lang['proposal_convert_to_statement']            = 'Convert to Invoice';
$lang['proposal_converted_to_statement_success']  = 'Proposal converted to invoice successfully';
$lang['proposal_converted_to_statement_fail']     = 'Failed to convert proposal to invoice';
$lang['customer_have_statements_by']       = 'Contains invoices by status %s';
$lang['not_recurring_statements_cron_activity_heading']             = 'Recurring Invoices Cron Job Activity';
$lang['not_statement_created']                                      = 'Invoice Created:';
$lang['not_statement_renewed']                                      = 'Renewed Invoice:';
$lang['not_expense_renewed']                                      = 'Renewed Expense:';
$lang['not_statement_sent_to_customer']                             = 'Invoice Sent to Customer: %s';
$lang['not_statement_sent_yes']                                     = 'Yes';
$lang['not_statement_sent_not']                                     = 'No';
$lang['not_action_taken_from_recurring_statement']                  = 'Action taken from recurring invoice:';
$lang['estimate_activity_converted']                              = 'converted this estimate to invoice.<br /> %s';
$lang['statement_estimate_activity_removed_item']                   = 'removed item <b>%s</b>';
$lang['statement_activity_number_changed']                          = 'Invoice number changed from %s to %s';
$lang['statement_estimate_activity_updated_item_short_description'] = 'updated item short description from %s to %s';
$lang['statement_estimate_activity_updated_item_long_description']  = 'updated item long description from <b>%s</b> to <b>%s</b>';
$lang['statement_estimate_activity_updated_item_rate']              = 'updated item rate from %s to %s';
$lang['statement_estimate_activity_updated_qty_item']               = 'updated quantity on item <b>%s</b> from %s to %s';
$lang['statement_estimate_activity_added_item']                     = 'added new item <b>%s</b>';
$lang['statement_estimate_activity_sent_to_client']                 = 'sent estimate to client';
$lang['statement_activity_status_updated']                          = 'Invoice status updated from %s to %s';
$lang['statement_activity_created']                                 = 'created the invoice';
$lang['statement_activity_from_expense']                            = 'converted to invoice from expense';
$lang['statement_activity_recurring_created']                       = '[Recurring] Invoice created by CRON';
$lang['statement_activity_recurring_from_expense_created']          = '[Invoice From Expense] Invoice created by CRON';
$lang['statement_activity_sent_to_client_cron']                     = 'Invoice sent to customer by CRON';
$lang['statement_activity_sent_to_client']                          = 'sent invoice to customer';
$lang['statement_activity_marked_as_sent']                          = 'marked invoice as sent';
$lang['statement_activity_payment_deleted']                         = 'deleted payment for the invoice. Payment #%s, total amount %s';
$lang['statement_activity_payment_made_by_client']                  = 'Client made payment for the invoice from total <b>%s</b> - %s';
$lang['statement_activity_payment_made_by_staff']                   = 'recorded payment from total <b>%s</b> - %s';
$lang['statement_activity_added_attachment']                        = 'Added attachment';
$lang['report_statement_number']            = 'Invoice #';
$lang['report_statement_customer']          = 'Customer';
$lang['report_statement_date']              = 'Date';
$lang['report_statement_duedate']           = 'Due Date';
$lang['report_statement_amount']            = 'Amount';
$lang['report_statement_amount_with_tax']   = 'Amount with tax';
$lang['report_statement_amount_open']       = 'Amount open';
$lang['report_statement_status']            = 'Status';
$lang['home_statement_overview']        = 'Invoice overview';
$lang['zip_statements']         = 'Zip Invoices';
$lang['statement_total_paid']   = 'Total Paid';
$lang['statement_amount_due']   = 'Amount Due';
$lang['task_is_billed']         = 'This task is billed on invoice with number %s';

$lang['statement_task_item_project_tasks_not_included'] = 'Projects tasks are not included in this list.';
$lang['statement_table_hours_heading']                  = 'Hours';
$lang['statement_estimate_sent_to_email']               = 'Email to';
$lang['project_statement_timesheet_start_time']                = 'Start time: %s';
$lang['project_statement_timesheet_end_time']                  = 'End time: %s';
$lang['project_statement_timesheet_total_logged_time']         = 'Billable time: %s';
$lang['project_statementd_successfully']                       = 'Project Invoiced Successfully';
$lang['project_statements']                                    = 'Invoices';
$lang['statement_project_info']                                = 'Project Invoice Info';
$lang['statement_project']                                     = 'Invoice Project';
$lang['statement_project_data_single_line']                    = 'Single line';
$lang['statement_project_data_task_per_item']                  = 'Task per item';
$lang['statement_project_data_timesheets_individually']        = 'All timesheets individually';
$lang['statement_project_item_name_data']                      = 'Item name';
$lang['statement_project_description_data']                    = 'Description';
$lang['statement_project_projectname_taskname']                = 'Project name + Task name';
$lang['statement_project_all_tasks_total_logged_time']         = 'All tasks + total logged time per task';
$lang['statement_project_project_name_data']                   = 'Project name';
$lang['statement_project_timesheet_individually_data']         = 'Timesheet start time + end time + total logged time';
$lang['statement_project_total_logged_time_data']              = 'Total logged time';
$lang['project_activity_statementd_project']             = 'Invoiced project';
