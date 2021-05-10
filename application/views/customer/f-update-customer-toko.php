
<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body" style="display:flex">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/update_error',$notif_data); ?>
                <form id = "update_form" method = "POST">
                    <input type="hidden" name="id_pk_cust" id = "id_edit"> 
                    
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="cust_suff" id = "cust_suff_edit" class="form-control">
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
                        <input type="text" class="form-control" name="cust_name" id = "cust_name_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Badan Usaha</h5>
                        <select name="cust_badan_usaha" id = "cust_badan_usaha_edit" class="form-control">
                            <option value="0" disabled>Pilih Badan Usaha</option>
                            <option value="Toko">Toko</option>
                            <option value="CV">CV</option>
                            <option value="PT">PT</option>
                            <option value="Unit Dagang">Unit Dagang</option>
                        </select>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Perusahaan</h5>
                        <input type="text" class="form-control" name="cust_perusahaan" id = "cust_perusahaan_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Email</h5>
                        <input type="email" class="form-control" name="cust_email" id = "cust_email_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No Kantor</h5>
                        <input type="text" class="form-control" name="cust_telp" id = "cust_telp_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" name="cust_hp" id = "cust_hp_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type="text" class="form-control" name="cust_keterangan" id = "cust_keterangan_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Nomor NPWP</h5>
                        <input type="text" class="form-control" name="cust_npwp" id = "cust_npwp_edit" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="cust_foto_npwp" required>
                        <input type="hidden" name="cust_foto_npwp_current" id = "cust_foto_npwp_edit" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Rekening</h5>
                        <input type="text" class="form-control" name="cust_rek" id = "cust_rek_edit" required value = "-">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto Kartu Nama</h5>
                        <input type="file" class="form-control" name="cust_krt_nama" required>
                        <input type="hidden" name="cust_krt_nama_current" id = "cust_krt_nama_edit" required>
                    </div>
                    <div class = "form-group col-lg-12">
                        <h5>Alamat</h5>
                        <textarea class="form-control" name="cust_alamat" id = "cust_alamat_edit" required></textarea>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Toko</h5>
                        <input type="text" class="form-control" name="toko_nama" id = "cust_toko_nama_edit" required readonly>
                    </div>
                    <br><br>
                    <div class = "form-group col-lg-12">
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
        $("#id_edit").val(content[id]["id"]);
        $("#cust_suff_edit").val(content[id]["suff"]);
        $("#cust_name_edit").val(content[id]["name"]);
        $("#cust_badan_usaha_edit").val(content[id]["badan_usaha"]);
        $("#cust_perusahaan_edit").val(content[id]["perusahaan"]);
        $("#cust_email_edit").val(content[id]["email"]);
        $("#cust_telp_edit").val(content[id]["telp"]);
        $("#cust_hp_edit").val(content[id]["hp"]);
        $("#cust_keterangan_edit").val(content[id]["keterangan"]);
        $("#cust_npwp_edit").val(content[id]["no_npwp"]);
        $("#cust_foto_npwp_edit").val(content[id]["foto_npwp"]);
        $("#cust_rek_edit").val(content[id]["no_rekening"]);
        $("#cust_krt_nama_edit").val(content[id]["foto_kartu_nama"]);
        $("#cust_alamat_edit").val(content[id]["alamat"]);
        $("#cust_toko_nama_edit").val(content[id]["nama_toko"]);
    }
</script>
