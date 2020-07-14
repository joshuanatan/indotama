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
                    <div class = "form-group">
                        <h5>Nama Cabang</h5>
                        <input type = "text" class = "form-control" required name = "nama" id = "nama_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Kode Cabang</h5>
                        <input type = "text" class = "form-control" required name = "kode" id = "kode_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Daerah Cabang</h5>
                        <input type = "text" class = "form-control" required name = "daerah" id = "daerah_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Alamat Cabang</h5>
                        <input type = "text" class = "form-control" required name = "alamat" id = "alamat_edit">
                    </div>
                    <div class = "form-group">
                        <h5>No Telp Cabang</h5>
                        <input type = "text" class = "form-control" required name = "notelp" id = "notelp_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Kop Surat</h5>
                        <input type = "file" class = "form-control" name = "kop_surat">
                        <input type = "hidden" name = "kop_surat_current" id = "kop_surat_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Surat Non PKP</h5>
                        <input type = "file" class = "form-control" name = "nonpkp">
                        <input type = "hidden" name = "nonpkp_current" id = "nonpkp_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Surat Pernyataan Nomor Rekening</h5>
                        <input type = "file" class = "form-control" name = "pernyataan_rek">
                        <input type = "hidden" name = "pernyataan_rek_current" id = "pernyataan_rek_edit">
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
        $("#nama_edit").val(content[id]["nama"]);
        $("#kode_edit").val(content[id]["kode"]);
        $("#daerah_edit").val(content[id]["daerah"]);
        $("#alamat_edit").val(content[id]["alamat"]);
        $("#notelp_edit").val(content[id]["notelp"]);
        $("#kop_surat_edit").val(content[id]["kop_surat"]);
        $("#nonpkp_edit").val(content[id]["nonpkp"]);
        $("#pernyataan_rek_edit").val(content[id]["pernyataan_rek"]);
    }
</script>