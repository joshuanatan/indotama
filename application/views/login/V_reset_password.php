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
										<h2>Reset Password</h2>
										<div class="form-wrap mt-40">
											<form action="<?php echo base_url(); ?>login/pass_reset" method="post" name="theForm">
                                                <input type="hidden" name="id_pk_user" value="<?php echo $id_pk_user ?>">
												<div class="form-group">
													<label class="control-label mb-10" for="exampleInputEmail_2">Password Baru</label>
													<input type="password" name="user_pass" id="passbaru" class="form-control" placeholder="Password" required min="8" oninput="cekPassword()"/>
												</div>
                                                <div class="form-group">
													<label class="control-label mb-10" for="exampleInputEmail_2">Knfirmasi Password Baru</label>
                                                    <input type="password" name="passkonfir" id="passkonfir" class="form-control" placeholder="Password" required min="8" oninput="cekPassword()"/>
                                                    <p style="font-size:10px; color:red" id="cekpasnya"></p>
												</div>
												<div class="form-group text-center">
                                                    <a id="simpanpassword" onclick="submitlah()" class="btn btn-primary  btn-rounded" style="display:none">Save</a>
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
				$("#cekpasnya").html("*Password konfirmasi harus sama dengan password baru");
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
</script>