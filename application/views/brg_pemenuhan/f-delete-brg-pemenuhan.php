<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Pembatalan <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/delete_error',$notif_data); ?>
                <input type = "hidden" id = "id_delete">
                <h4 align = "center">Apakah anda yakin akan membatalkan pemenuhan di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>Nama Barang</th>
                            <td id = "brg_nama_delete"></td>    
                        </tr>
                        <tr>
                            <td>Qty Permintaan</td>
                            <td id = "brg_permintaan_qty_delete"></td>
                        </tr>
                        <tr>
                            <td>Qty Pemenuhan</td>
                            <td id = "brg_pemenuhan_qty_delete"></td>
                        </tr>
                        <tr>
                            <td>Notes Permintaan</td>
                            <td id = "brg_permintaan_notes_delete"></td>
                        </tr>
                        <tr>
                            <th>Status Permintaan</th>
                            <td id = "brg_permintaan_status_delete"></td>    
                        </tr>
                        <tr>
                            <th>Cabang Peminta</th>
                            <td id = "brg_permintaan_cabang_delete"></td>    
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
        $("#brg_nama_delete").html(content[row]["nama_barang"]);
        $("#brg_permintaan_qty_delete").html(content[row]["stok_permintaan"]);
        $("#brg_pemenuhan_qty_delete").html(content[row]["stok_terpenuhi"]);
        $("#brg_permintaan_notes_delete").html(content[row]["notes"]);
        $("#brg_permintaan_status_delete").html(content[row]["status_permintaan"]);
        $("#brg_permintaan_cabang_delete").html(content[row]["cabang_peminta"]);
        
        
    }
</script>