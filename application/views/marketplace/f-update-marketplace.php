
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
                <form id = "update_form" method = "POST" enctype = "multipart/form-data">
                    <input type = "hidden" name = "id" id = "id_edit">
                    
                    <div class = "form-group">
                        <h5>Nama Marketplace</h5>
                        <input type = "text" class = "form-control" required name = "nama" id = "nama_edit">
                    </div>
                    
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan" id = "keterangan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Biaya</h5>
                        <input type = "text" class = "form-control" required name = "biaya" id = "biaya_edit">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    var barang_kombinasi_list;
    function load_edit_content(row){
        $("#id_edit").val(content[row]["id"]);
        $("#nama_edit").val(content[row]["nama"]);
        $("#keterangan_edit").val(content[row]["ket"]);
        $("#satuan_edit").val(content[row]["satuan"]);
        $("#biaya_edit").val(content[row]["biaya"]);
    }
</script>