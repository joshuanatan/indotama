
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                    <input type = "hidden" name = "id"  disabled id = "d_id_edit">
                    <div class = "form-group col-lg-6">
                        <h5>Nama Supplier</h5>
                        <input type = "text" class = "form-control" required name = "nama" disabled id = "d_nama_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nama PIC</h5>
                        <input type = "text" class = "form-control" required name = "pic" disabled id = "d_pic_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="suff" class="form-control"  disabled id = "d_suff_edit">
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
                        <h5>Email</h5>
                        <input type = "text" class = "form-control" required name = "email" disabled id = "d_email_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>No Telp</h5>
                        <input type = "text" class = "form-control" required name = "notelp" disabled id = "d_notelp_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type = "text" class = "form-control" required name = "nohp" disabled id = "d_nohp_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Alamat</h5>
                        <input type = "text" class = "form-control" required name = "alamat" disabled id = "d_alamat_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <textarea type = "text" class = "form-control" required name = "keterangan" disabled id = "d_keterangan_edit"></textarea>
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
        $("#d_pic_edit").val(content[id]["nama"]);
        $("#d_suff_edit").val(content[id]["suff"]);
        $("#d_nama_edit").val(content[id]["perusahaan"]);
        $("#d_email_edit").val(content[id]["email"]);
        $("#d_notelp_edit").val(content[id]["telp"]);
        $("#d_nohp_edit").val(content[id]["hp"]);
        $("#d_alamat_edit").val(content[id]["alamat"]);
        $("#d_keterangan_edit").val(content[id]["keterangan"]);
    }
</script>
