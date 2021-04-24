 <div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data Warehouse</h4>
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
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" name="warehouse_nama" id = "warehouse_nama_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="warehouse_alamat" id = "warehouse_alamat_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="warehouse_notelp" id = "warehouse_notelp_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" name="warehouse_desc" id = "warehouse_desc_edit" required>
                    </div>

                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func();" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function load_edit_content(id){
        $("#id_edit").val(content[id]["id"]);
        $("#warehouse_nama_edit").val(content[id]["nama"]);
        $("#warehouse_alamat_edit").val(content[id]["alamat"]);
        $('#warehouse_notelp_edit').val(content[id]["notelp"]);
        $('#warehouse_desc_edit').val(content[id]["desc"]);
    }
</script>