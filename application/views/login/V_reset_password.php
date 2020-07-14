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
											<a href="index.html">
												<!--<img class="brand-img mr-10" src="../img/logo.png" alt="brand"/>-->
												<span class="brand-text">INDOTAMA</span>
											</a>
										</div>
										<div class="mb-30">
											<h3 class="text-center txt-dark mb-10">Reset Password</h3>
										</div>	
										<div class="form-wrap">
										<p style="color:white; font-weight:bolder"><?php if(isset($message)) echo $message; ?></p>
											<form action="<?php echo base_url(); ?>login/pass_reset" method="post" name="theForm">
												<input type="hidden" name="id_pk_user" value="<?php echo $id_pk_user ?>">
												<div class="form-group">
													<label class="pull-left control-label mb-10" for="exampleInputpwd_2">New Password</label>
													<input type="password" name="user_pass" id="passbaru" oninput="cekPassword()" class="form-control" required="" placeholder="Enter New pwd" min="8">
												</div>
												<div class="form-group">
													<label class="pull-left control-label mb-10" for="exampleInputpwd_3">Confirm Password</label>
													<input type="password" name="passkonfir" id="passkonfir" oninput="cekPassword()" class="form-control" required="" placeholder="Re-Enter pwd">
													<p style="font-size:10px; color:red" id="cekpasnya"></p>
												</div>
												<div class="form-group text-center">
													<button type="submit" id="simpanpassword"  onclick="submitlah()" style="display:none" class="btn btn-primary btn-rounded">Reset</button>
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
	<script>
		function submitlah(){
			document.theForm.submit();
		}

		function cekPassword(){
			var passb2 = $("#passbaru").val();
			var passk2 = $("#passkonfir").val();
			$("#simpanpassword").hide();
			$.ajax({
				type:"post",
				url:"<?php echo base_url()?>login/cek_password",
				data: {passb:passb2, passk:passk2},
				success: function(respond){
					if(respond==1){
						$("#simpanpassword").hide();
						$("#cekpasnya").html("*Password konfirmasi harus sama dengan password baru (min: 8 karakter)");
					}
					
					if(respond==0){
						$("#simpanpassword").show();
						$("#cekpasnya").empty();
					}
					
					if(respond==2){
						$("#simpanpassword").hide();
						$("#cekpasnya").html("*Password salah");
					}
					
				}
			});
		}

		$("form").keypress(function(e) {
		//Enter key
		if (e.which == 13) {
			return false;
		}
		});
	</script>
</html>







