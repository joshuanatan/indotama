
<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <?php 
                $notif_data = array(
                    "page_title"=>$page_title
                );
                $this->load->view('_notification/update_error',$notif_data); ?>
                <form id = "update_form" method = "POST">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group">
                        <h5>Nama Menu</h5>
                        <input type = "text" class = "form-control" required name = "display" id = "display_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Controller</h5>
                        <input type = "text" class = "form-control" required name = "controller" id = "controller_edit">
                    </div>
                    <input type = "hidden" class = "form-control" required name = "icon" id = "icon_edit" value = "-">
                    <div class = "form-group">
                        <h5>Kategori</h5>
                        <select class = "form-control" required name = "kategori" id = "kategori_edit">
                            <option value = "GENERAL">GENERAL</option>
                            <option value = "TOKO">TOKO</option>
                            <option value = "CABANG">CABANG</option>
                            <option value = "GUDANG">GUDANG</option>
                        </select>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func();menubar()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function load_edit_content(id){
        $("#id_edit").val(content[id]["id"]);
        $("#controller_edit").val(content[id]["controller"]);
        $("#display_edit").val(content[id]["display"]);
        $("#icon_edit").val(content[id]["icon"]);
        $("#kategori_edit").val(content[id]["kategori"]);
    }
</script>
