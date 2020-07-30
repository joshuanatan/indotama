<?php
$page_title = "Dashboard Cabang";
$this->session->id_user;
$breadcrumb = array(
	"Dashboard"
);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Data table CSS -->
		<link href="<?php echo base_url(); ?>vendors/bower_components/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
		<link href="<?php echo base_url(); ?>vendors/bower_components/datatables.net-responsive/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css" />
		<?php $this->load->view('req/mm_css.php');?>
	</head>

	<body>
		<div class="preloader-it">
			<div class="la-anim-1"></div>
		</div>
		<div class="wrapper theme-1-active pimary-color-pink">
		<?php $this->load->view('req/mm_menubar');?>
			<div class="page-wrapper">
				<div class="container-fluid pt-25">
					Welcome to Indotama System
				</div>
			</div>
		</div>
	</body>
</html>

<?php $this->load->view('req/mm_js.php');?>
<?php $this->load->view("_core_script/menubar_func");?>
<script>
window.onload = function() {
	if(!window.location.hash) {
		window.location = window.location + '#loaded';
		window.location.reload();
	}
}
</script>
