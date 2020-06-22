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
                            <td>Nomor Retur</td>
                            <td id = "no_retur_delete"></td>
                        </tr>
                        <tr>
                            <td>Nomor Penjualan</td>
                            <td id = "no_penjualan_delete"></td>
                        </tr>
                        <tr>
                            <td>Tanggal Retur</td>
                            <td id = "tgl_retur_delete"></td>
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
    function load_delete_content(row){
        $("#id_delete").val(content[row]["id"]);
        $("#no_retur_delete").html(content[row]["no"]);
        $("#no_penjualan_delete").html(content[row]["nomor_penj"]);
        $("#tgl_retur_delete").html(content[row]["tgl"].split(" ")[0]);
    }
</script>