<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/update_error',$notif_data); ?>
                <form id = "update_form" method = "POST" enctype = "multipart/form-data">
                    <input type="hidden" name="id_pk_employee" id="id_pk_employee_edit"> 
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select id="emp_suff_edit" name="emp_suff" class="form-control">
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
                        <input type="text" class="form-control" id="emp_nama_edit" name="emp_nama" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>NPWP</h5>
                        <input type="text" class="form-control" id="emp_npwp_edit" name="emp_npwp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>KTP</h5>
                        <input type="text" class="form-control" id="emp_ktp_edit" name="emp_ktp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" id="emp_hp_edit" name="emp_hp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Kode Pos</h5>
                        <input type="text" class="form-control" id="emp_kode_pos_edit" name="emp_kode_pos" required>
                    </div>
                    
                    
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" id="emp_foto_npwp_edit" name="emp_foto_npwp" required>
                        <img id="img_emp_foto_npwp_edit" width="100px">
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto KTP</h5>
                        <input type="file" class="form-control" id="emp_foto_ktp_edit" name="emp_foto_ktp" required>
                        <img id="img_emp_foto_ktp_edit" width="100px">
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto Lain</h5>
                        <input type="file" class="form-control" id="emp_foto_lain_edit" name="emp_foto_lain" required>
                        <img id="img_emp_foto_lain_edit" width="100px">
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Foto</h5>
                        <input type="file" class="form-control" id="emp_foto_edit"  name="emp_foto" required>
                        <span id="img_emp_foto_edit"></span>
                    </div>
                    <div class = "clearfix"></div>
                    <div class = "form-group col-lg-6">
                        <h5>Gaji Karyawan</h5>
                        <input type="text" class="form-control nf-input" id="emp_gaji_edit" name="emp_gaji" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Rekening Bank</h5>
                        <input type="text" class="form-control" id="emp_rek_edit" name="emp_rek" required>
                    </div>
                    
                    <div class = "form-group col-lg-12">
                        <h5>Mulai Bekerja</h5>
                        <input type="date" class="form-control" id="emp_startdate_edit" name="emp_startdate" required>
                    </div>
                    
                    <div class = "form-group col-lg-12">
                        <h5>Akhir Bekerja</h5>
                        <input type="radio" name="radio_enddate" id="no_enddate_edit" value="MASIH" checked>Masih Bekerja
                        <br><input type="radio" value="TIDAK" name="radio_enddate" id="yes_enddate_edit">Tidak bekerja sejak:
                        <input type="date" style="display:none" class="form-control" id="emp_enddate_edit" name="emp_enddate">
                    </div>
                    
                    <div class = "form-group col-lg-12">
                        <h5>Jenis Kelamin</h5>
                        <input type="radio" name="emp_gender" value="PRIA" id="pria">Pria 
                        <input type="radio" name="emp_gender" value="WANITA" id="wanita">Wanita
                    </div>
                    
                    <div class = "form-group col-lg-12">
                        <h5>Alamat</h5>
                        <textarea class="form-control" id="emp_alamat_edit" name="emp_alamat" required></textarea>
                    </div>
                    <div class = "clearfix"></div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
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
        $("#emp_gaji_edit").val(content[id]["gaji"]);

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