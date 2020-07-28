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
                <input type = "hidden" id = "id_delete" name="id">
                <h4 align = "center">Apakah Anda yakin akan membatalkan permintaan dibawah ini</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>Nama Barang</th>
                            <td id = "brg_nama_delete"></td>    
                        </tr>
                        <tr>
                            <td>Jumlah Permintaan (Pcs)</td>
                            <td id = "brg_permintaan_qty_delete"></td>
                        </tr>
                        <tr>
                            <td>Jumlah Terpenuhi (Pcs)</td>
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
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                    <button type = "button" id = "delete-permintaan-button" onclick = "delete_func()" class = "btn btn-sm btn-primary">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_delete_content(row){
        if(content[row]["status"].toLowerCase() != "aktif"){
            $("#delete-permintaan-button").hide();
        }
        else{
            $("#delete-permintaan-button").show();
        }
        $("#id_delete").val(content[row]["id"]);
        $("#brg_nama_delete").html(content[row]["barang"]);
        $("#brg_permintaan_qty_delete").html(content[row]["qty"]);
        $("#brg_pemenuhan_qty_delete").html(content[row]["qty_pemenuhan"]);
        $("#brg_permintaan_notes_delete").html(content[row]["notes"]);
        $("#brg_permintaan_status_delete").html(content[row]["status"]);
    }
</script>