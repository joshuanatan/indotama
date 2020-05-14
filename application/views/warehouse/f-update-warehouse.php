<div class = "modal fade" id = "edit_warehouse<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Warehouse</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>warehouse/edit_warehouse">
                    <input type="hidden" name="id_pk_warehouse" value="<?php echo $ID_PK_WAREHOUSE; ?>"> 
                    <div class = "form-group">
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" value="<?php echo $WAREHOUSE_NAMA; ?>" name="warehouse_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" value="<?php echo $WAREHOUSE_ALAMAT; ?>" name="warehouse_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" value="<?php echo $WAREHOUSE_NOTELP; ?>" name="warehouse_notelp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" value="<?php echo $WAREHOUSE_DESC; ?>" name="warehouse_desc" required>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type = "submit" class = "btn btn-sm btn-primary" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>