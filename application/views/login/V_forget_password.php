<!DOCTYPE html>
<html lang="en">
	<head>
		<title>INDOTAMA</title>
		<?php $this->load->view('req/mm_css.php');?>
	</head>
	<body>
		<!--Preloader-->
		<div class="preloader-it">
			<div class="la-anim-1"></div>
		</div>
		<!--/Preloader-->
		
		<div class="wrapper pa-0">
			<!-- Main Content -->
			<div class="page-wrapper pa-0 ma-0 auth-page">
				<div class="container-fluid">
					<!-- Row -->
					<div class="table-struct full-width full-height">
						<div class="table-cell vertical-align-middle auth-form-wrap">
							<div class="auth-form  ml-auto mr-auto no-float card-view pt-30 pb-30">
								<div class="row">
									<div class="col-sm-12 col-xs-12">
										<div class="sp-logo-wrap text-center pa-0 mb-30">
											<a href="<?php echo base_url()?>">
												<!--<img class="brand-img mr-10" src="../img/logo.png" alt="brand"/>-->
												<span class="brand-text">INDOTAMA</span>
											</a>
										</div>
										<div class="mb-30">
											<h3 class="text-center txt-dark mb-10">Need help with your password?</h3>
											<h6 class="text-center txt-grey nonecase-font">Enter the email you use for this system, and we’ll help you create a new password.</h6>
										</div>	
										<div class="form-wrap">
										<?php if($this->session->msg != ""):?>
										<div class = "alert alert-danger">
											<?php echo $this->session->msg;?>
										</div>
										<?php endif;?>
										<p style="color:white; font-weight:bolder"><?php if(isset($message)) echo $message; ?></p>
											<form action="<?php echo base_url(); ?>login/forget_password_method" method="post">
												<div class="form-group">
													<label class="control-label mb-10" for="exampleInputEmail_2">Email address</label>
													<input type="email" class="form-control" required="" id="exampleInputEmail_2" name="user_email" placeholder="johndoe@example.com">
												</div>
												
												<div class="form-group text-center">
													<button type="submit" class="col-lg-12 btn btn-primary btn-rounded">Reset</button>
												</div>

												<div class="form-group text-center">
													<br>
													<br>
													<a href="<?php echo base_url() ?>99dea78007133396a7b8ed70578ac6ae" class="txt-primary block mb-10 font-12">Back to login page</a>
												</div>
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