<!DOCTYPE html>
<html lang="en">
	<head>
		<?php $this->load->view('req/mm_css.php');?>
	</head>
	<body>
		<!--Preloader-->
		<div class="preloader-it">
			<div class="la-anim-1"></div>
		</div>
		<!--/Preloader-->

		<div class="wrapper pa-0 ">
			<!-- Main Content -->
			<div class="page-wrapper pa-0 ma-0 auth-page" style = "background-image: url('<?php echo base_url();?>asset/src/images/login_bg.jpg');background-size:cover;">
				<div class="container-fluid">
					<!-- Row -->
					<div class="table-struct full-width full-height">
						<div class="table-cell vertical-align-middle auth-form-wrap">
							<div class="auth-form  ml-auto mr-auto no-float card-view pt-30 pb-30">
								<div class="row">
									<div class="col-sm-12 col-xs-12">
										<div class="sp-logo-wrap text-center">
											<span class="brand-text">INDOTAMA</span>
											<p align = "center">General Suppliers/Traders in Oil & Gas Safety</p>
										</div>
										<?php if($this->session->msg != ""):?>
											<div class = "alert alert-danger">
												<?php echo $this->session->msg;?>
											</div>
										<?php endif;?>
										<?php if($this->session->success_send != ""):?>
											<div class = "alert alert-success">
											<?php echo $this->session->success_send;?>
											</div>
										<?php endif;?>
										<?php if($this->session->status != ""):?>
											<div class = "alert alert-success">
												Password succesfully changed!
											</div>
										<?php endif;?>
										<div class="form-wrap mt-40">
											<form action="<?php echo base_url(); ?>login/login_method" method="post">
												<div class="form-group">
													<label class="control-label mb-10" for="exampleInputEmail_2">Email</label>
													<input type="text" name="user_name" class="form-control" id="exampleInputEmail_2" placeholder="johndoe@example.com">
												</div>
												<div class="form-group">
													<label class="pull-left control-label mb-10" for="exampleInputpwd_2">Password</label>
													<input type="password" class="form-control" id="exampleInputpwd_2" name="user_pass" placeholder="Enter password">
												</div>
												<div class="form-group text-center">
													<button type="submit" class="btn btn-primary btn-sm">LOG IN</button>
												</div>
												<div class="form-group text-center">
													<a href="<?php echo base_url() ?>login/forget_password" class="capitalize-font txt-primary block mb-10 font-12" href="forget.php">forgot password</a>
												</div>
												<p style="color:white; font-weight:bolder"><?php if(isset($message)) echo $message; ?></p>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- /Row -->
				</div>

			</div>
			<!-- /Main Content -->

		</div>
		<!-- /#wrapper -->

		<!-- JavaScript -->
		<?php $this->load->view('req/mm_js.php');?>
		
	</body>
</html>
