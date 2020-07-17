<?php
$page_title = "Master Employee";
$breadcrumb = array(
    "Master","Employee"
);
$notif_data = array(
    "page_title"=>$page_title
);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php $this->load->view('req/mm_css.php');?>
    </head>

    <body>
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <div class="wrapper theme-1-active pimary-color-pink">

            <?php $this->load->view('req/mm_menubar.php');?>

            <div class="page-wrapper" style="background-color:white">
								<div class="panel-heading">
									<div class="pull-left">
										<h6 class="panel-title txt-dark" style="font-size:30px;">Edit Profile</h6>
									</div>
									<div class="clearfix"></div>
                                </div>
                                <div class="col-lg-3"></div>
								<div class="panel-wrapper collapse in">
                                    <center>
									<div class="panel-body">
										<div class="row">
											<div class="col-md-12">
												<div class="form-wrap">
													<form action="<?php echo base_url()?>dashboard/edit_profile_method" method="post" class="form-horizontal">
														<div class="form-body">
															<div class="row">
															<?php if($this->session->msg_b != ""):?>
																<div class = "alert alert-success">
																	<?php echo $this->session->msg_b ?>
																</div>
															<?php endif;?>
															<?php if($this->session->msg_e != ""):?>
																<div class = "alert alert-error">
																	<?php echo $this->session->msg_e ?>
																</div>
															<?php endif;?>
																<input type = "hidden" name = "id_employee" value="<?php echo $employee[0]['id_pk_employee'] ?>" id = "id_employee">
																<input type = "hidden" name = "id_user" id = "id_user" value="<?php echo $user[0]['id_pk_user'] ?>">
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Username</label>
																		<div class="col-md-9">
																			<input type = "text" class = "form-control" required name = "name" id = "name_edit" value="<?php echo $user[0]['user_name'] ?>">
																		</div>
																	</div>
                                                                </div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Email</label>
																		<div class="col-md-9">
																			<input type = "text" class = "form-control" required name = "email" id = "email_edit"  value="<?php echo $user[0]['user_email'] ?>">
																		</div>
																	</div>
                                                                </div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Nama Lengkap</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_nama_edit" name="emp_nama" required  value="<?php echo $employee[0]['emp_nama'] ?>">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">No HP</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_hp_edit" name="emp_hp" required  value="<?php echo $employee[0]['emp_hp'] ?>">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Alamat</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_alamat_edit" name="emp_alamat" required  value="<?php echo $employee[0]['emp_alamat'] ?>">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Kode Pos</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_kode_pos_edit" name="emp_kode_pos" required  value="<?php echo $employee[0]['emp_kode_pos'] ?>">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Rekening Bank</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_rek_edit" name="emp_rek" required  value="<?php echo $employee[0]['emp_rek'] ?>">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-3">
																	<div class="form-group">
																		<label style="text-align:center" class="control-label col-md-12">Jenis Kelamin</label>
																		<div class="col-md-12">
																			<input type="radio" name="emp_gender" value="PRIA" <?php if($employee[0]['emp_gender']=="PRIA"){echo "checked";} ?> id="pria">Pria 
                        <input type="radio" name="emp_gender" value="WANITA" id="wanita" <?php if($employee[0]['emp_gender']=="WANITA"){echo "checked";} ?>>Wanita
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-3">
																	<div class="form-group">
																		<label style="text-align:left" class="control-label col-md-12">Panggilan</label>
																		<div class="col-md-12">
																			<select id="emp_suff_edit" name="emp_suff" class="form-control">
																				<option value="0" disabled>Pilih Panggilan</option>
																				<option value="MR"  <?php if($employee[0]['emp_suff']=="MR"){echo "selected";} ?>>Mr</option>
																				<option value="MRS"<?php if($employee[0]['emp_suff']=="MRS"){echo "selected";} ?>>Mrs</option>
																				<option value="MS"<?php if($employee[0]['emp_suff']=="MS"){echo "selected";} ?>>Ms</option>
																				<option value="BAPAK" <?php if($employee[0]['emp_suff']=="BAPAK"){echo "selected";} ?>>Bpk</option>
																				<option value="IBU" <?php if($employee[0]['emp_suff']=="IBU"){echo "selected";} ?>>Ibu</option>
																				<option value="NONA" <?php if($employee[0]['emp_suff']=="NONA"){echo "selected";} ?>>Nona</option>
																			</select>
																		</div>
																	</div>
                                                                </div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Foto</label>
																		<div class="col-md-9">
																			<input type="file" class="form-control" id="emp_foto_edit"  name="emp_foto" >
                        <img src="<?php echo base_url() ?>asset/uploads/employee/foto/<?php echo $employee[0]['emp_foto'] ?>" width="100px">
																			 
																		</div>
																	</div>
                                                                </div>
															
														</div>
														<div class="form-actions mt-10">
															<div class="row">
																<div class="col-md-6">
																	<div class="row">
																		<div class="col-md-offset-3 col-md-9">
																			<button type="submit" class="btn btn-success  mr-10">Submit</button>
																			<a href="<?php echo base_url() ?>dashboard" class="btn btn-default">Cancel</a>
																		</div>
																	</div>
																</div>
																<div class="col-md-6"> </div>
															</div>
														</div>
													</form>
												</div>
											</div>
										</div>
									</div>
                                    </center>
                                </div>
                    
                    <?php $this->load->view('req/mm_footer.php');?>
                
            </div>
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>