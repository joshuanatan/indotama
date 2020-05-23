<div class = "modal fade" id = "edit_employee<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Employee</h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/update_error',$notif_data); ?>
                <form method="POST" action="<?php echo base_url() ?>employee/edit_employee">
                    <input type="hidden" name="id_pk_employee" value="<?php echo $ID_PK_EMPLOYEE; ?>"> 
                    <div class = "form-group">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" value="<?php echo $EMP_NAMA; ?>" name="emp_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>NPWP</h5>
                        <input type="text" class="form-control" value="<?php echo $EMP_NPWP; ?>"  name="emp_npwp" required>
                    </div>
                    <div class = "form-group">
                        <h5>KTP</h5>
                        <input type="text" value="<?php echo $EMP_KTP; ?>"  class="form-control" name="emp_ktp" required>
                    </div>
                    <div class = "form-group">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" value="<?php echo $EMP_HP; ?>"  name="emp_hp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" value="<?php echo $EMP_ALAMAT; ?>" name="emp_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>Kode Pos</h5>
                        <input type="number" class="form-control" value="<?php echo $EMP_KODE_POS; ?>" name="emp_kode_pos" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="emp_foto_npwp">
                    </div>
                    <div class = "form-group">
                        <h5>Foto KTP</h5>
                        <input type="file" class="form-control" name="emp_foto_ktp">
                    </div>
                    <div class = "form-group">
                        <h5>Foto Lain</h5>
                        <input type="file" class="form-control" name="emp_foto_lain">
                    </div>
                    <div class = "form-group">
                        <h5>Foto</h5>
                        <input type="file" class="form-control" name="emp_foto">
                    </div>
                    <div class = "form-group">
                        <h5>Gaji Karyawan</h5>
                        <input type="number" class="form-control" value="<?php echo $EMP_GAJI; ?>" name="emp_gaji" required>
                    </div>
                    <div class = "form-group">
                        <h5>Mulai Bekerja</h5>
                        <input type="date"  value="<?php echo date("Y-m-d",strtotime($EMP_STARTDATE)) ?>"  class="form-control" name="emp_startdate" required>
                    </div>
                    <div class = "form-group">
                        <h5>Akhir Bekerja</h5>
                       
                        <input type="radio" name="radio_enddate" id="no_enddate2" value="MASIH"  <?php if($EMP_ENDDATE=="0000-00-00 00:00:00"){echo "checked";} ?>>Masih Bekerja
                        <br><input type="radio" value="TIDAK"  <?php if($EMP_ENDDATE!="0000-00-00 00:00:00"){echo "checked";} ?> name="radio_enddate" id="yes_enddate2">Tidak bekerja sejak:
                        <input type="date"  <?php if($EMP_ENDDATE=="0000-00-00 00:00:00"){echo 'style="display:none" ';}   if($EMP_ENDDATE!="0000-00-00 00:00:00"){echo 'required value="'.date("Y-m-d",strtotime($EMP_ENDDATE)).'"'; } ?> class="form-control" id="emp_enddate2" name="emp_enddate" >
                    </div>
                    <div class = "form-group">
                        <h5>Rekening Bank</h5>
                        <input type="text" class="form-control" value="<?php echo $EMP_REK; ?>" name="emp_rek" required>
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Kelamin</h5>
                        <input type="radio" name="emp_gender" value="PRIA" <?php  if($EMP_GENDER=="PRIA"){echo "checked";} ?>>Pria 
                        <input type="radio" name="emp_gender" <?php  if($EMP_GENDER=="WANITA"){echo "checked";} ?> value="WANITA">Wanita
                    </div>
                    <div class = "form-group">
                        <h5>Panggilan</h5>
                        <select name="emp_suff" class="form-control">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="MR" <?php  if($EMP_SUFF=="MR"){echo "selected";} ?>>Mr</option>
                            <option value="MRS" <?php  if($EMP_SUFF=="MRS"){echo "selected";} ?>>Mrs</option>
                            <option value="MS" <?php  if($EMP_SUFF=="MS"){echo "selected";} ?>>Ms</option>
                            <option value="BAPAK" <?php  if($EMP_SUFF=="BAPAK"){echo "selected";} ?>>Bpk</option>
                            <option value="IBU" <?php  if($EMP_SUFF=="IBU"){echo "selected";} ?>>Ibu</option>
                            <option value="NONA" <?php  if($EMP_SUFF=="NONA"){echo "selected";} ?>>Nona</option>
                        </select>
                    </div>
                    <div class = "form-group">
                        <h5>Toko</h5>
                        <select class="form-control" name="id_fk_toko">
                            <option value="0" disabled>Pilih Toko</option>
                            <?php for($y=0 ; $y<count($toko); $y++){ ?>
                                <option value="<?php echo $toko[$y]['ID_PK_TOKO'] ?>" <?php  if($ID_FK_TOKO==$toko[$y]['ID_PK_TOKO']){echo "selected";} ?>><?php echo $toko[$y]['TOKO_NAMA']?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type = "submit" class = "btn btn-sm btn-primary" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>