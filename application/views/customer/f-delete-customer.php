
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
                <input type = "hidden" id = "id_delete">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Nama Supplier</td>
                            <td id = "name_delete"></td>
                        </tr>
                        <tr>
                            <td>Nama PIC</td>
                            <td id = "perusahaan_delete"></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td id = "email_delete"></td>
                        </tr>
                        <tr>
                            <td>No Telp</td>
                            <td id = "telp_delete"></td>
                        </tr>
                        <tr>
                            <td>No HP</td>
                            <td id = "hp_delete"></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td id = "alamat_delete"></td>
                        </tr>
                        <tr>
                            <td>Toko</td>
                            <td id = "toko_delete"></td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td id = "keterangan_delete"></td>
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
    function load_delete_content(id){
        $("#id_delete").val(content[id]["id"]);
        $("#name_delete").html(content[id]["name"]);
        $("#perusahaan_delete").html(content[id]["perusahaan"]);
        $("#email_delete").html(content[id]["email"]);
        $("#telp_delete").html(content[id]["telp"]);
        $("#hp_delete").html(content[id]["hp"]);
        $("#alamat_delete").html(content[id]["alamat"]);
        $("#keterangan_delete").html(content[id]["keterangan"]);
        $("#toko_delete").html(content[id]["nama_toko"]);
    }
</script>