<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			include_once(module_views_path('statements','admin/statements/filter_params.php'));
			$this->load->view('admin/statements/list_template');
			?>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<div id="modal-wrapper"></div>
<script>var hidden_columns = [2,6,7,8];</script>
<?php init_tail(); ?>
<script>
	$(function(){
		init_statement();
		init_statements_body();
	});
</script>
</body>
</html>
