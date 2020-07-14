<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/delete_error',$notif_data); ?>   
                <input type = "hidden" id = "id_delete" name = "id">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Penawar</td>
                            <td id = "penawar_delete"></td>
                        </tr>
                        <tr>
                            <td>Tanggal Penawaran</td>
                            <td id = "tgl_delete"></td>
                        </tr>
                        <tr>
                            <td>Subjek Penawaran</td>
                            <td id = "subjek_delete"></td>
                        </tr>
                        <tr>
                            <td>Content Penawaran</td>
                            <td id = "content_delete"></td>
                        </tr>
                        <tr>
                            <td>Notes Penawaran</td>
                            <td id = "notes_delete"></td>
                        </tr>
                        <tr>
                            <td>File Penawaran</td>
                            <td id = "file_delete"></td>
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">Cancel</button>
                    <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_delete_content(row){
        $("#id_delete").val(content[row]["id"]);
        $("#penawar_delete").html(content[row]["refrensi"]);
        $("#tgl_delete").html(content[row]["tgl"]);
        $("#subjek_delete").html(content[row]["subject"]);
        $("#content_delete").html(content[row]["content"]);
        $("#notes_delete").html(content[row]["notes"]);
        $("#file_delete").html(content[row]["file_html"]);
    }
    
</script>