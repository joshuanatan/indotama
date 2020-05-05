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
			<div class="page-wrapper pa-0 ma-0 auth-page bg-gradient">
				<div class="container-fluid">
					<!-- Row -->
					<div class="table-struct full-width full-height">
						<div class="table-cell vertical-align-middle auth-form-wrap">
							<div class="auth-form  ml-auto mr-auto no-float card-view pt-30 pb-30">
								<div class="row">
									<div class="col-sm-12 col-xs-12">
										<div class="sp-logo-wrap text-center">
											<a href="index.html">
												<span class="brand-text">INDOTAMA SYSTEM</span>
											</a>
										</div>
										<div class="form-wrap mt-40">
											<form action="<?php echo base_url(); ?>HQ/login">
												<div class="form-group">
													<label class="control-label mb-10" for="exampleInputEmail_2">Username</label>
													<input type="email" class="form-control" id="exampleInputEmail_2" placeholder="MMSafety">
												</div>
												<div class="form-group">
													<label class="pull-left control-label mb-10" for="exampleInputpwd_2">Password</label>
													<a class="capitalize-font txt-primary block mb-10 pull-right font-12" href="forget.php">forgot password ?</a>
													<div class="clearfix"></div>
													<input type="password" class="form-control" id="exampleInputpwd_2" placeholder="Enter password">
												</div>
												<div class="form-group text-center">
													<button type="submit" class="btn btn-primary  btn-rounded">LOG IN</button>
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
