<div class = "modal fade" id = "hapus_employee<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <b><h4 class = "modal-title">Hapus Karyawan</h4></b>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>employee/hapus_employee">
                    <input type="hidden" name="id_pk_employee" value="<?php echo $ID_PK_EMPLOYEE; ?>"> 
                    <div class = "form-group">
                        <h5 style="text-align:center">Apakah anda yakin akan menghapus karyawan dengan nama: "<b><?php echo $EMP_NAMA; ?></b>"?</h5>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type = "submit" class = "btn btn-sm btn-primary" value="Yakin">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>