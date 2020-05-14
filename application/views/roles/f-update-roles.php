<div class = "modal fade" id = "edit_jabatan<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Jabatan</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>roles/edit_jabatan">
                    <input type="hidden" name="id_pk_jabatan" value="<?php echo $ID_PK_JABATAN ?>"> 
                    <div class = "form-group">
                        <h5>Nama Jabatan</h5>
                        <input type="text" class="form-control" name="jabatan_nama" value="<?php echo $JABATAN_NAMA ?>" required>
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