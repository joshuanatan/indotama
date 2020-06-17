<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Warehouse</h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/register_error',$notif_data); ?>
                <form id = "register_form" method = "POST">
                    
                    <div class = "form-group col-lg-6">
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" name="warehouse_nama" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="warehouse_alamat" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="warehouse_notelp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" name="warehouse_desc" required>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func();clear_priv_list()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>