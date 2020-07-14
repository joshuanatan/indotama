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
										<h6 class="panel-title txt-dark">Edit Profile</h6>
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
													<form action="#" class="form-horizontal">
														<div class="form-body">
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Nama Lengkap</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_nama_edit" name="emp_nama" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">NPWP</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_npwp_edit" name="emp_npwp" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">KTP</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_ktp_edit" name="emp_ktp" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">No HP</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_hp_edit" name="emp_hp" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Alamat</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_alamat_edit" name="emp_alamat" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Kode Pos</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_kode_pos_edit" name="emp_kode_pos" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Foto NPWP</label>
																		<div class="col-md-9">
																			<input type="file" class="form-control" id="emp_foto_npwp_edit" name="emp_foto_npwp" required>
                        <img id="img_emp_foto_npwp_edit" width="100px">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Foto KTP</label>
																		<div class="col-md-9">
																			<input type="file" class="form-control" id="emp_foto_ktp_edit" name="emp_foto_ktp" required>
                        <img id="img_emp_foto_ktp_edit" width="100px">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Foto Lain</label>
																		<div class="col-md-9">
																			<input type="file" class="form-control" id="emp_foto_lain_edit" name="emp_foto_lain" required>
                        <img id="img_emp_foto_lain_edit" width="100px">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Foto</label>
																		<div class="col-md-9">
																			<input type="file" class="form-control" id="emp_foto_edit"  name="emp_foto" required>
                        <span id="img_emp_foto_edit"></span>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Gaji Karyawan</label>
																		<div class="col-md-9">
																			<input type="number" class="form-control" id="emp_gaji_edit" name="emp_gaji" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Mulai Bekerja</label>
																		<div class="col-md-9">
																			<input type="date" class="form-control" id="emp_startdate_edit" name="emp_startdate" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Akhir Bekerja</label>
																		<div class="col-md-9">
																			 <input type="radio" name="radio_enddate" id="no_enddate_edit" value="MASIH" checked>Masih Bekerja
                        <br><input type="radio" value="TIDAK" name="radio_enddate" id="yes_enddate_edit">Tidak bekerja sejak:
                        <input type="date" style="display:none" class="form-control" id="emp_enddate_edit" name="emp_enddate">
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Rekening Bank</label>
																		<div class="col-md-9">
																			<input type="text" class="form-control" id="emp_rek_edit" name="emp_rek" required>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Jenis Kelamin</label>
																		<div class="col-md-9">
																			<input type="radio" name="emp_gender" value="PRIA" id="pria">Pria 
                        <input type="radio" name="emp_gender" value="WANITA" id="wanita">Wanita
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Panggilan</label>
																		<div class="col-md-9">
																			<select id="emp_suff_edit" name="emp_suff" class="form-control">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="MR">Mr</option>
                            <option value="MRS">Mrs</option>
                            <option value="MS">Ms</option>
                            <option value="BAPAK">Bpk</option>
                            <option value="IBU">Ibu</option>
                            <option value="NONA">Nona</option>
                        </select>
																			 
																		</div>
																	</div>
                                                                </div>
                                                                <div class="col-md-6">
																	<div class="form-group">
																		<label class="control-label col-md-3">Toko</label>
																		<div class="col-md-9">
																			<select class="form-control" id="id_fk_toko_edit" name="id_fk_toko">
                            <option value="0" disabled>Pilih Toko</option>
                            <?php for($p=0 ; $p<count($toko); $p++){ ?>
                                <option value="<?php echo $toko[$p]['ID_PK_TOKO'] ?>"><?php echo $toko[$p]['TOKO_NAMA']?></option>
                            <?php } ?>
                        </select>
																			 
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
																			<button type="button" class="btn btn-default">Cancel</button>
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
<script>

    function load_edit_content(id){
        $("#id_pk_employee_edit").val(content[id]["id"]);
        $("#emp_nama_edit").val(content[id]["nama"]);
        $("#emp_npwp_edit").val(content[id]["npwp"]);
        $("#emp_ktp_edit").val(content[id]["ktp"]);
        $("#emp_hp_edit").val(content[id]["hp"]);
        $("#emp_alamat_edit").val(content[id]["alamat"]);
        $("#emp_kode_pos_edit").val(content[id]["kode_pos"]);
        $("#emp_foto_edit").html(content[id]["foto"]);

        var npwp = "<?php echo base_url() ?>asset/uploads/employee/npwp/" + content[id]["foto_npwp"];
        $("#emp_foto_npwp_edit").attr("src", npwp);
        var ktp = "<?php echo base_url() ?>asset/uploads/employee/ktp/" + content[id]["foto_ktp"];
        $("#emp_foto_ktp_edit").attr("src", ktp);
        var lain = "<?php echo base_url() ?>asset/uploads/employee/lain/" + content[id]["foto_lain"];
        $("#emp_foto_lain_edit").attr("src", lain);

        if(content[id]["gender"]=="PRIA"){
            $('#pria').prop('checked', true);
        }else{
            $('#wanita').prop('checked', true);
        }

        if(content[id]["enddate"]==="0000-00-00 00:00:00"){
            $('#no_enddate_edit').prop('checked', true);
            $("#emp_enddate_edit").hide();
            $("#emp_enddate_edit").prop('required',false);
        }else{
            $('#yes_enddate_edit').prop('checked', true);
            $("#emp_enddate_edit").show();
            $("#emp_enddate_edit").prop('required',true);
        }

        $("#emp_suff_edit").val(content[id]["suff"]);
        $("#emp_gaji_edit").attr("value",content[id]["gaji"]);

        $("#emp_kode_pos_edit").val(content[id]["kode_pos"]);
        
        var split_date = content[id]["startdate"].split(" ");
        $("#emp_startdate_edit").val(split_date[0]);
        split_date = content[id]["enddate"].split(" ");
        $("#emp_enddate_edit").val(split_date[0]);
        
        $("#emp_rek_edit").val(content[id]["rek"]);
        $("#id_fk_toko_edit").val(content[id]["id_toko"]);
    }

    $('#yes_enddate_edit').click(function() {
        $("#emp_enddate_edit").show();
        $("#emp_enddate_edit").prop('required',true);
    });
    $('#no_enddate_edit').click(function() {
        $("#emp_enddate_edit").hide();
        $("#emp_enddate_edit").prop('required',false);
    });
</script>