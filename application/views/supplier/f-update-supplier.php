
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
                <form id = "update_form" method = "POST">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group col-lg-6">
                        <h5>Badan Usaha</h5>
                        <select name="sup_badan_usaha" id = "sup_badan_usaha_edit" class="form-control">
                            <option value="0" disabled>Pilih Badan Usaha</option>
                            <option value="Toko">Toko</option>
                            <option value="CV">CV</option>
                            <option value="PT">PT</option>
                            <option value="Unit Dagang">Unit Dagang</option>
                        </select>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nama Supplier</h5>
                        <input type = "text" class = "form-control" required name = "nama" id = "nama_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="suff" id = "suff_edit" class="form-control">
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
                        <h5>Nama PIC</h5>
                        <input type = "text" class = "form-control" required name = "pic" id = "pic_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Email</h5>
                        <input type = "text" class = "form-control" required name = "email" id = "email_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>No Kantor</h5>
                        <input type = "text" class = "form-control" required name = "notelp" id = "notelp_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type = "text" class = "form-control" required name = "nohp" id = "nohp_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan" id = "keterangan_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nomor NPWP</h5>
                        <input type="text" class="form-control" name="sup_npwp" id = "sup_npwp_edit" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="sup_foto_npwp" required>
                        <input type="hidden" name="sup_foto_npwp_current" id = "sup_foto_npwp_edit" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Rekening</h5>
                        <input type="text" class="form-control" name="sup_rek" id = "sup_rek_edit" required value = "-">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto Kartu Nama</h5>
                        <input type="file" class="form-control" name="sup_krt_nama" required>
                        <input type="hidden" name="sup_krt_nama_current" id = "sup_krt_nama_edit" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <textarea type = "text" class = "form-control" required name = "alamat" id = "alamat_edit"></textarea>
                    </div>
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
        $("#id_edit").val(content[id]["id"]);
        $("#sup_badan_usaha_edit").val(content[id]["badan_usaha"]);
        $("#nama_edit").val(content[id]["perusahaan"]);
        $("#suff_edit").val(content[id]["suff"]);
        $("#pic_edit").val(content[id]["nama"]);
        $("#email_edit").val(content[id]["email"]);
        $("#notelp_edit").val(content[id]["telp"]);
        $("#nohp_edit").val(content[id]["hp"]);
        $("#keterangan_edit").val(content[id]["keterangan"]);
        $("#sup_npwp_edit").val(content[id]["no_npwp"]);
        $("#sup_foto_npwp_edit").val(content[id]["foto_npwp"]);
        $("#sup_rek_edit").val(content[id]["no_rekening"]);
        $("#sup_krt_nama_edit").val(content[id]["foto_kartu_nama"]);
        $("#alamat_edit").val(content[id]["alamat"]);
    }
</script>
