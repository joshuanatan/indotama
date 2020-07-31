<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <input type="hidden" name="id_pk_employee"> 
                
                <div class = "form-group col-lg-6">
                    <h5>Panggilan</h5>
                    <input type = 'text' class = "form-control" disabled id = "d_emp_suff_edit">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nama Lengkap</h5>
                    <input type="text" class="form-control" disabled id="d_emp_nama_edit" required>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>NPWP</h5>
                    <input type="text" class="form-control" disabled id="d_emp_npwp_edit" required>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>KTP</h5>
                    <input type="text" class="form-control" disabled id="d_emp_ktp_edit" required>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>No HP</h5>
                    <input type="text" class="form-control" disabled id="d_emp_hp_edit" required>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Kode Pos</h5>
                    <input type="text" class="form-control" disabled id="d_emp_kode_pos_edit" required>
                </div>
                
                
                <div class = "form-group col-lg-6">
                    <h5>Foto NPWP</h5>
                    <img id="d_emp_foto_npwp_edit" height = "100px">
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Foto KTP</h5>
                    <img id="d_emp_foto_ktp_edit" height = "100px">
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Foto Lain</h5>
                    <img id="d_emp_foto_lain_edit" height = "100px">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Foto</h5>
                    <img id="d_emp_foto_edit" height = "100px">
                </div>
                <div class = "clearfix"></div>
                <div class = "form-group col-lg-6">
                    <h5>Gaji Karyawan</h5>
                    <input type="text" class="form-control" disabled id="d_emp_gaji_edit" name="emp_gaji" required>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Rekening Bank</h5>
                    <input type="text" class="form-control" disabled id="d_emp_rek_edit" required>
                </div>
                <div class = "form-group col-lg-12">
                    <h5>Mulai Bekerja</h5>
                    <input type="date" class="form-control" disabled id="d_emp_startdate_edit" name="emp_startdate" required>
                </div>
                
                <div class = "form-group col-lg-12">
                    <h5>Akhir Bekerja</h5>
                    <input type="radio" name="radio_enddate" disabled id="d_no_enddate_edit" value="MASIH" checked>Masih Bekerja
                    <br><input type="radio" value="TIDAK" name="radio_enddate" disabled id="d_yes_enddate_edit">Tidak bekerja sejak:
                    <input type="date" style="display:none" class="form-control" disabled id="d_emp_enddate_edit" name="emp_enddate">
                </div>
                
                
                <div class = "form-group col-lg-12">
                    <h5>Jenis Kelamin</h5>
                    <input type="text" class="form-control" disabled id="d_emp_gender" required>
                </div>
                <div class = "form-group col-lg-12">
                    <h5>Alamat</h5>
                    <textarea type="text" class="form-control" disabled id="d_emp_alamat_edit" required></textarea>
                </div>
                <div class = "clearfix"></div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(id){
        $("#d_emp_nama_edit").val(content[id]["nama"]);
        $("#d_emp_npwp_edit").val(content[id]["npwp"]);
        $("#d_emp_ktp_edit").val(content[id]["ktp"]);
        $("#d_emp_hp_edit").val(content[id]["hp"]);
        $("#d_emp_alamat_edit").html(content[id]["alamat"]);
        $("#d_emp_kode_pos_edit").val(content[id]["kode_pos"]);
        $("#d_emp_foto_edit").html(content[id]["foto"]);
        $("#d_emp_gender").val(content[id]["gender"]);

        var foto = "<?php echo base_url() ?>asset/uploads/employee/foto/" + content[id]["foto"];
        $("#d_emp_foto_foto_edit").attr("src", foto);
        var npwp = "<?php echo base_url() ?>asset/uploads/employee/npwp/" + content[id]["foto_npwp"];
        $("#d_emp_foto_npwp_edit").attr("src", npwp);
        var ktp = "<?php echo base_url() ?>asset/uploads/employee/ktp/" + content[id]["foto_ktp"];
        $("#d_emp_foto_ktp_edit").attr("src", ktp);
        var lain = "<?php echo base_url() ?>asset/uploads/employee/lain/" + content[id]["foto_lain"];
        $("#d_emp_foto_lain_edit").attr("src", lain);
        var file = "<?php echo base_url() ?>asset/uploads/employee/foto/" + content[id]["foto_file"];
        $("#d_emp_foto_edit").attr("src", file);


        if(content[id]["enddate"]==="0000-00-00 00:00:00"){
            $('#d_no_enddate_edit').prop('checked', true);
            $("#d_emp_enddate_edit").hide();
            $("#d_emp_enddate_edit").prop('required',false);
        }else{
            $('#d_yes_enddate_edit').prop('checked', true);
            $("#d_emp_enddate_edit").show();
            $("#d_emp_enddate_edit").prop('required',true);
        }

        $("#d_emp_suff_edit").val(content[id]["suff"]);
        $("#d_emp_gaji_edit").attr("value",content[id]["gaji"]);

        $("#d_emp_kode_pos_edit").val(content[id]["kode_pos"]);
        
        var split_date = content[id]["startdate"].split(" ");
        $("#d_emp_startdate_edit").val(split_date[0]);
        split_date = content[id]["enddate"].split(" ");
        $("#d_emp_enddate_edit").val(split_date[0]);
        
        $("#d_emp_rek_edit").val(content[id]["rek"]);
        $("#d_id_fk_toko_edit").val(content[id]["id_toko"]);
    }

    $('#d_yes_enddate_edit').click(function() {
        $("#d_emp_enddate_edit").show();
        $("#d_emp_enddate_edit").prop('required',true);
    });
    $('#d_no_enddate_edit').click(function() {
        $("#d_emp_enddate_edit").hide();
        $("#d_emp_enddate_edit").prop('required',false);
    });
</script>