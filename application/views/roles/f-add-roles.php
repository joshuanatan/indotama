<div class = "modal fade" id = "tambah_jabatan">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Jabatan</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>roles/register_jabatan">
                    <div class = "form-group">
                        <h5>Nama Jabatan</h5>
                        <input type="text" class="form-control" name="jabatan_nama" required>
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