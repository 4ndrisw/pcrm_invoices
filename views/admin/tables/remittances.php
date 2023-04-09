<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('payments', '', 'delete');

$aColumns = [
    db_prefix() . 'statementpaymentrecords.id as id',
    'statementid',
    'paymentmode',
    'transactionid',
    get_sql_select_client_company(),
    'amount',
    db_prefix() . 'statementpaymentrecords.date as date',
    ];

$join = [
    'LEFT JOIN ' . db_prefix() . 'statements ON ' . db_prefix() . 'statements.id = ' . db_prefix() . 'statementpaymentrecords.statementid',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'statements.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'statements.currency',
    'LEFT JOIN ' . db_prefix() . 'payment_modes ON ' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'statementpaymentrecords.paymentmode',
    ];

$where = [];
if ($clientid != '') {
    array_push($where, 'AND ' . db_prefix() . 'clients.userid=' . $this->ci->db->escape_str($clientid));
}

if (!has_permission('payments', '', 'view')) {
    $whereUser = '';
    $whereUser .= 'AND (statementid IN (SELECT id FROM ' . db_prefix() . 'statements WHERE (addedfrom=' . get_staff_user_id() . ' AND addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "statements" AND capability="view_own")))';
    if (get_option('allow_staff_view_statements_assigned') == 1) {
        $whereUser .= ' OR statementid IN (SELECT id FROM ' . db_prefix() . 'statements WHERE sale_agent=' . get_staff_user_id() . ')';
    }
    $whereUser .= ')';
    array_push($where, $whereUser);
}

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'statementpaymentrecords';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'clientid',
    db_prefix() . 'currencies.name as currency_name',
    db_prefix() . 'payment_modes.name as payment_mode_name',
    db_prefix() . 'payment_modes.id as paymentmodeid',
    'paymentmethod',
    ]);

$output  = $result['output'];
$rResult = $result['rResult'];

$this->ci->load->model('statements/remittance_modes_model');
$payment_gateways = $this->ci->remittance_modes_model->get_payment_gateways(true);

foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('payments/payment/' . $aRow['id']);


    $options = icon_btn('payments/payment/' . $aRow['id'], 'fa-regular fa-pen-to-square');

    if ($hasPermissionDelete) {
        $options .= icon_btn('payments/delete/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete');
    }

    $numberOutput = '<a href="' . $link . '">' . $aRow['id'] . '</a>';

    $numberOutput .= '<div class="row-options">';
    $numberOutput .= '<a href="' . $link . '">' . _l('view') . '</a>';
    if ($hasPermissionDelete) {
        $numberOutput .= ' | <a href="' . admin_url('payments/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = '<a href="' . admin_url('statements/list_statements/' . $aRow['statementid']) . '">' . format_statement_number($aRow['statementid']) . '</a>';

    $outputPaymentMode = $aRow['payment_mode_name'];

    // Since version 1.0.1
    if (is_null($aRow['paymentmodeid'])) {
        foreach ($payment_gateways as $gateway) {
            if ($aRow['paymentmode'] == $gateway['id']) {
                $outputPaymentMode = $gateway['name'];
            }
        }
    }

    if (!empty($aRow['paymentmethod'])) {
        $outputPaymentMode .= ' - ' . $aRow['paymentmethod'];
    }
    $row[] = $outputPaymentMode;

    $row[] = $aRow['transactionid'];

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    $row[] = app_format_money($aRow['amount'], $aRow['currency_name']);

    $row[] = _d($aRow['date']);

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}