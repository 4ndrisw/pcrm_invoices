<?php defined('BASEPATH') or exit('No direct script access allowed');

$table_data = array(
  _l('statement_dt_table_heading_number'),
  _l('statement_dt_table_heading_amount'),
  _l('statement_total_tax'),
  array(
    'name'=>_l('statement_estimate_year'),
    'th_attrs'=>array('class'=>'not_visible')
  ),
  _l('statement_dt_table_heading_date'),
  array(
    'name'=>_l('statement_dt_table_heading_client'),
    'th_attrs'=>array('class'=>(isset($client) ? 'not_visible' : ''))
  ),
  _l('project'),
  _l('tags'),
  _l('statement_dt_table_heading_duedate'),
  _l('statement_dt_table_heading_status'));
$custom_fields = get_custom_fields('statement',array('show_on_table'=>1));
foreach($custom_fields as $field){
  array_push($table_data, [
   'name' => $field['name'],
   'th_attrs' => array('data-type'=>$field['type'], 'data-custom-field'=>1)
 ]);
}
$table_data = hooks()->apply_filters('statements_table_columns', $table_data);
render_datatable($table_data, (isset($class) ? $class : 'statements'));
?>
