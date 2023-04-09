<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('statement_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $statement_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . statement_status_color_pdf($status) . ');text-transform:uppercase;">' . format_statement_status($status, '', false) . '</span>';
}

if ($status != Statements_model::STATUS_PAID && $status != Statements_model::STATUS_CANCELLED && get_option('show_pay_link_to_statement_pdf') == 1
    && found_statement_mode($payment_modes, $statement->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('statement/' . $statement->id . '/' . $statement->hash) . '"><1b>' . _l('view_statement_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$organization_info = '<div style="color:#424242;">';

$organization_info .= format_organization_info();

$organization_info .= '</div>';

// Bill to
$statement_info = '<b>' . _l('statement_bill_to') . ':</b>';
$statement_info .= '<div style="color:#424242;">';
    //$statement_info .= format_customer_info($statement, 'statement', 'billing');
    $statement_info .= format_customer_info($statement, 'billing', 'billing');
$statement_info .= '</div>';

// ship to to
if ($statement->include_shipping == 1 && $statement->show_shipping_on_statement == 1) {
    $statement_info .= '<br /><b>' . _l('ship_to') . ':</b>';
    $statement_info .= '<div style="color:#424242;">';
    //$statement_info .= format_customer_info($statement, 'statement', 'shipping');
    $statement_info .= format_customer_info($statement, 'billing', 'shipping');
    $statement_info .= '</div>';
}

$statement_info .= '<br />' . _l('statement_data_date') . ' ' . _d($statement->date) . '<br />';

$statement_info = hooks()->apply_filters('statement_pdf_header_after_date', $statement_info, $statement);

if (!empty($statement->duedate)) {
    $statement_info .= _l('statement_data_duedate') . ' ' . _d($statement->duedate) . '<br />';
    $statement_info = hooks()->apply_filters('statement_pdf_header_after_due_date', $statement_info, $statement);
}

if ($statement->sale_agent != 0 && get_option('show_sale_agent_on_statements') == 1) {
    $statement_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($statement->sale_agent) . '<br />';
    $statement_info = hooks()->apply_filters('statement_pdf_header_after_sale_agent', $statement_info, $statement);
}

if ($statement->project_id != 0 && get_option('show_project_on_statement') == 1) {
    $statement_info .= _l('project') . ': ' . get_project_name_by_id($statement->project_id) . '<br />';
    $statement_info = hooks()->apply_filters('statement_pdf_header_after_project_name', $statement_info, $statement);
}

$statement_info = hooks()->apply_filters('statement_pdf_header_before_custom_fields', $statement_info, $statement);

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($statement->id, $field['id'], 'statement');
    if ($value == '') {
        continue;
    }
    $statement_info .= $field['name'] . ': ' . $value . '<br />';
}

$statement_info      = hooks()->apply_filters('statement_pdf_header_after_custom_fields', $statement_info, $statement);
$organization_info = hooks()->apply_filters('statementpdf_organization_info', $organization_info, $statement);
$statement_info      = hooks()->apply_filters('statement_pdf_info', $statement_info, $statement);

$left_info  = $swap == '1' ? $statement_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $statement_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_items_table_data($statement, 'statement', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('statement_subtotal') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($statement->subtotal, $statement->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($statement)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('statement_discount');
    if (is_sale_discount($statement, 'percent')) {
        $tbltotal .= ' (' . app_format_number($statement->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money($statement->discount_total, $statement->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . app_format_money($tax['total_tax'], $statement->currency_name) . '</td>
</tr>';
}

if ((int) $statement->adjustment != 0) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('statement_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($statement->adjustment, $statement->currency_name) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('statement_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($statement->total, $statement->currency_name) . '</td>
</tr>';

if (count($statement->payments) > 0 && get_option('show_total_paid_on_statement') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('statement_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix() . 'statementpaymentrecords', [
        'field' => 'amount',
        'where' => [
            'statementid' => $statement->id,
        ],
    ]), $statement->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_statement') == 1 && $credits_applied = total_credits_applied_to_statement($statement->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money($credits_applied, $statement->currency_name) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_statement') == 1 && $statement->status != Statements_model::STATUS_CANCELLED) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('statement_amount_due') . '</strong></td>
       <td align="right" width="15%">' . app_format_money($statement->total_left_to_pay, $statement->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($statement->total, $statement->currency_name), 0, 1, false, true, 'C', true);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (count($statement->payments) > 0 && get_option('show_transactions_on_statement_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('statement_received_payments') . ':', 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('statement_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('statement_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('statement_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('statement_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($statement->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . app_format_money($payment['amount'], $statement->currency_name) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (found_statement_mode($payment_modes, $statement->id, true, true)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('statement_html_offline_payment') . ':', 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);

    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id'])) {
            if (!is_payment_mode_allowed_for_statement($mode['id'], $statement->id)) {
                continue;
            }
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(2);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}

if (!empty($statement->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('statement_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $statement->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($statement->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions') . ':', 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $statement->terms, 0, 1, false, true, 'L', true);
}
