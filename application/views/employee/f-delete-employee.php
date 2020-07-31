
<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/delete_error',$notif_data); ?>
                <input type = "hidden" id = "id_delete" name="id_pk_employee">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Nama Lengkap</td>
                            <td id = "emp_nama_delete"></td>
                        </tr>
                        <tr>
                            <td>NPWP</td>
                            <td id = "emp_npwp_delete"></td>
                        </tr>
                        <tr>
                            <td>KTP</td>
                            <td id = "emp_ktp_delete"></td>
                        </tr>
                        <tr>
                            <td>No HP</td>
                            <td id = "emp_hp_delete"></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td id = "emp_alamat_delete"></td>
                        </tr>
                        <tr>
                            <td>Kode Pos</td>
                            <td id = "emp_kode_pos_delete"></td>
                        </tr>
                        <tr>
                            <td>Foto NPWP</td>
                            <td id = "emp_foto_npwp_delete"><img id="img_emp_foto_npwp_delete" width="100px"></td>
                        </tr>
                        <tr>
                            <td>Foto KTP</td>
                            <td id = "emp_foto_ktp_delete"><img id="img_emp_foto_ktp_delete" width="100px"></td>
                        </tr>
                        <tr>
                            <td>Foto Lain</td>
                            <td id = "emp_foto_lain_delete"><img id="img_emp_foto_lain_delete" width="100px"></td>
                        </tr>
                        <tr>
                            <td>Foto</td>
                            <td id = "emp_foto_delete"><img id="img_emp_foto_delete" width="100px"></td>
                        </tr>
                        <tr>
                            <td>Gaji Karyawan</td>
                            <td id = "emp_gaji_delete"></td>
                        </tr>
                        <tr>
                            <td>Mulai Bekerja</td>
                            <td id = "emp_startdate_delete"></td>
                        </tr>
                        <tr id="baris_enddate">
                            <td>Tidak bekerja sejak</td>
                            <td id = "emp_enddate_delete"></td>
                        </tr>
                        <tr>
                            <td>Rekening Bank</td>
                            <td id = "emp_rek_delete"></td>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td id = "emp_gender_delete"></td>
                        </tr>
                        <tr>
                            <td>Panggilan</td>
                            <td id = "emp_suff_delete"></td>
                        </tr>
                        
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">Cancel</button>
                    <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#yes_enddate_edit').click(function() {
        $("#emp_enddate_edit").show();
        $("#emp_enddate_edit").prop('required',true);
    });
    $('#no_enddate_edit').click(function() {
        $("#emp_enddate_edit").hide();
        $("#emp_enddate_edit").prop('required',false);
    });
    
    function load_delete_content(id){
        $("#emp_nama_delete").html(content[id]["nama"]);
        $("#emp_npwp_delete").html(content[id]["npwp"]);
        $("#emp_ktp_delete").html(content[id]["ktp"]);
        $("#emp_hp_delete").html(content[id]["hp"]);
        $("#emp_alamat_delete").html(content[id]["alamat"]);
        $("#emp_kode_pos_delete").html(content[id]["kode_pos"]);
        $("#emp_gaji_delete").html(content[id]["gaji"]);
        $("#id_delete").val(content[id]["id"]);
        
        var str = content[id]["startdate"].split(" ");
        $("#emp_startdate_delete").html(str[0]);

        if(content[id]["enddate"]=="0000-00-00 00:00:00"){
            $("#emp_enddate_delete").html("MASIH BEKERJA");
        }else{
            var str2 = content[id]["enddate"].split(" ");
            $("#emp_enddate_delete").html(str2[0]);
        }
        

        $("#emp_gender_delete").html(content[id]["gender"]);

        $("#emp_suff_delete").html(content[id]["suff"]);
        $("#id_fk_toko_delete").html(content[id]["toko"]);


        var npwp = "<?php echo base_url() ?>asset/uploads/employee/npwp/" + content[id]["foto_npwp"];

        $("#img_emp_foto_npwp_delete").attr("src", npwp);
        var ktp = "<?php echo base_url() ?>asset/uploads/employee/ktp/" + content[id]["foto_ktp"];
        $("#img_emp_foto_ktp_delete").attr("src", ktp);
        var foto = "<?php echo base_url() ?>asset/uploads/employee/foto/" + content[id]["foto"];
        $("#img_emp_foto_delete").attr("src", foto);
        var lain = "<?php echo base_url() ?>asset/uploads/employee/lain/" + content[id]["foto_lain"];
        $("#img_emp_foto_lain_delete").attr("src", lain);
        
        

        //$("#radio_enddate_delete").html(content[id]["radio_enddate"]);
        $("#emp_rek_delete").html(content[id]["rek"]);
    }
    
</script>