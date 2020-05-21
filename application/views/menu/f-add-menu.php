
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/register_error',$notif_data); ?>
                <form id = "register_form" method = "POST">
                    <div class = "form-group">
                        <h5>Menu Display</h5>
                        <input type = "text" class = "form-control" required name = "display">
                    </div>
                    <div class = "form-group">
                        <h5>Controller</h5>
                        <input type = "text" class = "form-control" required name = "controller">
                    </div>
                    <div class = "form-group">
                        <h5>Icon</h5>
                        <input type = "text" class = "form-control" required name = "icon">
                    </div>
                    <div class = "form-group">
                        <h5>Kategori</h5>
                        <select class = "form-control" required name = "kategori">
                            <option value = "GENERAL">GENERAL</option>
                            <option value = "TOKO">TOKO</option>
                            <option value = "CABANG">CABANG</option>
                            <option value = "GUDANG">GUDANG</option>
                        </select>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>