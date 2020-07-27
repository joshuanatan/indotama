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
                            <td>Nomor Pembelian</td>
                            <td id = "nomor_delete"></td>
                        </tr>
                        <tr>
                            <td>Tanggal Pembelian</td>
                            <td id = "tgl_delete"></td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td id = "supplier_delete"></td>
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                    <button type = "button" id = "btn_hapus_pembelian" onclick = "delete_func()" class = "btn btn-sm btn-primary">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_delete_content(id){
        $("#id_delete").val(content[id]["id"]);
        $("#nomor_delete").html(content[id]["nomor"]);
        $("#tgl_delete").html(content[id]["tgl"]);
        $("#supplier_delete").html(content[id]["supplier"]);

        if(content[id]["status"].toLowerCase() == "selesai"){
            $("#btn_hapus_pembelian").hide();
        }
    }
</script>