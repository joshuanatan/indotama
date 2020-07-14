
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
                        <h5>Penawar</h5>
                        <input type = "text" class = "form-control" required name = "penawar" id = "penawar_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Penawaran</h5>
                        <input type = "date" class = "form-control" required name = "tgl" id = "tgl_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Subjek Penawaran</h5>
                        <input list = "datalist_barang_jenis" type = "text"  required name = "subjek" id = "subjek_edit" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <h5>Content Penawaran</h5>
                        <textarea class = "form-control" required name = "content" id = "content_edit"></textarea>
                    </div>
                    <div class = "form-group">
                        <h5>Notes Penawaran</h5>
                        <textarea class = "form-control" required name = "notes" id = "notes_edit"></textarea>
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
    function load_edit_content(row){
        $("#id_edit").val(content[row]["id"]);
        $("#penawar_edit").val(content[row]["refrensi"]);
        $("#tgl_edit").val(content[row]["tgl"]);
        $("#subjek_edit").val(content[row]["subject"]);
        $("#content_edit").val(content[row]["content"]);
        $("#notes_edit").val(content[row]["notes"]);
    }
</script>