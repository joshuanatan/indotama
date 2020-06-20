
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                    <input type="hidden" name="id_pk_cust" readonly id = "d_id_edit"> 
                    
                    <div class = "form-group col-lg-6">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" name="cust_name" readonly id = "d_name_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="cust_suff" class="form-control" disabled id = "d_suff_edit">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="MR">Mr</option>
                            <option value="MRS">Mrs</option>
                            <option value="MS">Ms</option>
                            <option value="BAPAK">Bpk</option>
                            <option value="IBU">Ibu</option>
                            <option value="NONA">Nona</option>
                        </select>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Perusahaan</h5>
                        <input type="text" class="form-control" name="cust_perusahaan" readonly id = "d_perusahaan_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Email</h5>
                        <input type="email" class="form-control" name="cust_email" readonly id = "d_email_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="cust_telp" readonly id = "d_telp_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" name="cust_hp" readonly id = "d_hp_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="cust_alamat" readonly id = "d_alamat_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type="text" class="form-control" name="cust_keterangan" readonly id = "d_keterangan_edit" required>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                    </div>
            </div>
        </div>
    </div>
</div>

<script>
    function load_detail_content(id){
        $("#d_id_edit").val(content[id]["id"]);
        $("#d_name_edit").val(content[id]["name"]);
        $("#d_suff_edit").val(content[id]["suff"]);
        $("#d_perusahaan_edit").val(content[id]["perusahaan"]);
        $("#d_email_edit").val(content[id]["email"]);
        $("#d_telp_edit").val(content[id]["telp"]);
        $("#d_hp_edit").val(content[id]["hp"]);
        $("#d_alamat_edit").val(content[id]["alamat"]);
        $("#d_keterangan_edit").val(content[id]["keterangan"]);
    }
</script>
