<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Statements
Description: Default module for defining statements
Version: 1.0.1
Requires at least: 2.3.*
*/

define('STATEMENTS_MODULE_NAME', 'statements');
define('STATEMENT_ATTACHMENTS_FOLDER', 'uploads/statements/');

hooks()->add_filter('before_statement_updated', '_format_data_statement_feature');
hooks()->add_filter('before_statement_added', '_format_data_statement_feature');

hooks()->add_action('after_cron_run', 'statements_notification');
hooks()->add_action('admin_init', 'statements_module_init_menu_items');
hooks()->add_action('admin_init', 'statements_permissions');
hooks()->add_action('clients_init', 'statements_clients_area_menu_items');

hooks()->add_action('staff_member_deleted', 'statements_staff_member_deleted');

hooks()->add_filter('migration_tables_to_replace_old_links', 'statements_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'statements_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'statements_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'statements_add_dashboard_widget');
hooks()->add_filter('module_statements_action_links', 'module_statements_action_links');


function statements_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'statements/widgets/statement_this_week',
        'container' => 'left-8',
    ];
    return $widgets;
}


function statements_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'statements', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function statements_global_search_result_output($output, $data)
{
    if ($data['type'] == 'statements') {
        $output = '<a href="' . admin_url('statements/statement/' . $data['result']['id']) . '">' . format_statement_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function statements_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('statements', '', 'view')) {

        // statements
        $CI->db->select()
           ->from(db_prefix() . 'statements')
           ->like(db_prefix() . 'statements.formatted_number', $q)->limit($limit);
        
        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'statements',
                'search_heading' => _l('statements'),
            ];
        
        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // statements
        $CI->db->select()->from(db_prefix() . 'statements')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'statements.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'statements.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'statements',
                'search_heading' => _l('statements'),
            ];
    }

    return $result;
}

function statements_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'statements',
                'field' => 'description',
            ];

    return $tables;
}

function statements_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('statements', $capabilities, _l('statements'));
    
    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('remittances', $capabilities, _l('remittances'));
}


/**
* Register activation module hook
*/
register_activation_hook(STATEMENTS_MODULE_NAME, 'statements_module_activation_hook');

function statements_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(STATEMENTS_MODULE_NAME, 'statements_module_deactivation_hook');

function statements_module_deactivation_hook()
{

     log_activity( 'Hello, world! . statements_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(STATEMENTS_MODULE_NAME, [STATEMENTS_MODULE_NAME]);

/**
 * Init statements module menu items in setup in admin_init hook
 * @return null
 */
function statements_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('statement'),
            'url'        => 'statements',
            'permission' => 'statements',
            'position'   => 57,
            ]);

    if (has_permission('statements', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('statements', [
                'slug'     => 'statements-tracking',
                'name'     => _l('statements'),
                'icon'     => 'fa fa-calendar',
                'href'     => admin_url('statements'),
                'position' => 12,
        ]);
    }
    if (has_permission('remittances', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('remittances', [
                'slug'     => 'remittances-tracking',
                'name'     => _l('remittances'),
                'icon'     => 'fa fa-calendar',
                'href'     => admin_url('statements/remittances'),
                'position' => 12,
        ]);
    }
}

function module_statements_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=statements') . '">' . _l('settings') . '</a>';

    return $actions;
}

function statements_clients_area_menu_items()
{   
    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('statements', [
                    'name'     => _l('statements'),
                    'href'     => site_url('statements/list'),
                    'position' => 15,
        ]);
    }
}

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function statements_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('statements', [
        'name'     => _l('settings_group_statements'),
        //'view'     => module_views_path(STATEMENTS_MODULE_NAME, 'admin/settings/includes/statements'),
        'view'     => 'statements/statements_settings',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(STATEMENTS_MODULE_NAME . '/statements');

//if(($CI->uri->segment(0)=='admin' && $CI->uri->segment(1)=='statements') || $CI->uri->segment(1)=='statements'){
    $CI->app_css->add(STATEMENTS_MODULE_NAME.'-css', base_url('modules/'.STATEMENTS_MODULE_NAME.'/assets/css/'.STATEMENTS_MODULE_NAME.'.css'));
    $CI->app_scripts->add(STATEMENTS_MODULE_NAME.'-js', base_url('modules/'.STATEMENTS_MODULE_NAME.'/assets/js/'.STATEMENTS_MODULE_NAME.'.js'));
//}


