<div class = "modal fade" id = "hapus_customer<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <b><h4 class = "modal-title">Hapus Customer</h4></b>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>customer/hapus_customer">
                    <input type="hidden" name="id_pk_cust" value="<?php echo $view_customer[$x]['ID_PK_CUST'] ?>"> 
                    <div class = "form-group">
                        <h5 style="text-align:center">Apakah anda yakin akan menghapus customer dengan nama: "<b><?php echo $view_customer[$x]['CUST_NAME'] ?></b>"?</h5>
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