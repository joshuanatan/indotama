<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data Warehouse</h4>
            </div>
            <div class = "modal-body">
                    <input type = "hidden" name = "id"  readonly id = "d_id_edit"> 
                    
                    <div class = "form-group col-lg-6">
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" name="warehouse_nama" readonly id = "d_warehouse_nama_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="warehouse_alamat" readonly id = "d_warehouse_alamat_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="warehouse_notelp" readonly id = "d_warehouse_notelp_edit" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" name="warehouse_desc" readonly id = "d_warehouse_desc_edit" required>
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
        $("#d_warehouse_nama_edit").val(content[id]["nama"]);
        $("#d_warehouse_alamat_edit").val(content[id]["alamat"]);
        $('#d_warehouse_notelp_edit').val(content[id]["notelp"]);
        $('#d_warehouse_desc_edit').val(content[id]["desc"]);
    }
</script>