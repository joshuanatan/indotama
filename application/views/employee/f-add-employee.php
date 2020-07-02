<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/register_error',$notif_data); ?>
                <form id = "register_form" method = "POST" enctype = "multipart/form-data">
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="emp_suff" class="form-control">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="Tn">Tn</option>
                            <option value="MR">Mr</option>
                            <option value="MRS">Mrs</option>
                            <option value="MS">Ms</option>
                            <option value="BAPAK">Bpk</option>
                            <option value="IBU">Ibu</option>
                            <option value="NONA">Nona</option>
                        </select>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" name="emp_nama" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>NPWP</h5>
                        <input type="text" class="form-control" name="emp_npwp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>KTP</h5>
                        <input type="text" class="form-control" name="emp_ktp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" name="emp_hp" required>
                    </div>
                    
                    
                    <div class = "form-group col-lg-6">
                        <h5>Kode Pos</h5>
                        <input type="number" class="form-control" name="emp_kode_pos" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="emp_foto_npwp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto KTP</h5>
                        <input type="file" class="form-control" name="emp_foto_ktp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto Lain</h5>
                        <input type="file" class="form-control" name="emp_foto_lain" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto</h5>
                        <input type="file" class="form-control" name="emp_foto" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Gaji Karyawan</h5>
                        <input type="number" class="form-control" name="emp_gaji" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Rekening Bank</h5>
                        <input type="text" class="form-control" name="emp_rek" required>
                    </div>
                    <div class = "form-group col-lg-12">
                        <h5>Mulai Bekerja</h5>
                        <input type="date" class="form-control" name="emp_startdate" required>
                    </div>
                    
                    <div class = "form-group col-lg-12">
                        <h5>Akhir Bekerja</h5>
                        <input type="radio" name="radio_enddate" id="no_enddate" value="MASIH" checked>Masih Bekerja
                        <br><input type="radio" value="TIDAK" name="radio_enddate" id="yes_enddate">Tidak bekerja sejak:
                        <input type="date" style="display:none" class="form-control" id="emp_enddate" name="emp_enddate">
                    </div>
                    
                    
                    <div class = "form-group col-lg-12">
                        <h5>Jenis Kelamin</h5>
                        <input type="radio" name="emp_gender" value="PRIA" checked>Pria 
                        <input type="radio" name="emp_gender" value="WANITA">Wanita
                    </div>
                    
                    <div class = "form-group col-lg-12">
                        <h5>Toko</h5>
                        <select class="form-control" name="id_fk_toko">
                            <option value="0" disabled>Pilih Toko</option>
                            <?php for($p=0 ; $p<count($toko); $p++){ ?>
                                <option value="<?php echo $toko[$p]['id_pk_toko'] ?>"><?php echo $toko[$p]['toko_nama']?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class = "form-group col-lg-12">
                        <h5>Alamat</h5>
                        <textarea class="form-control" name="emp_alamat" required></textarea>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type="button" onclick = "register_func()"  class = "btn btn-sm btn-primary" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('#yes_enddate').click(function() {
        $("#emp_enddate").show();
        $("#emp_enddate").prop('required',true);
    });
    $('#no_enddate').click(function() {
        $("#emp_enddate").hide();
        $("#emp_enddate").prop('required',false);
    });
</script>