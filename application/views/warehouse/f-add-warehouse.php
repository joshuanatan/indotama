<div class = "modal fade" id = "tambah_warehouse">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Warehouse</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>warehouse/register_warehouse">
                    <div class = "form-group">
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" name="warehouse_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="warehouse_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="warehouse_notelp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" name="warehouse_desc" required>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type="submit" class = "btn btn-sm btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>