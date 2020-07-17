
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <div class = "form-group">
                    <h5>Penawar</h5>
                    <input type = "text" class = "form-control" required readonly id = "penawar_detail">
                </div>
                <div class = "form-group">
                    <h5>Tanggal Penawaran</h5>
                    <input type = "date" class = "form-control" required readonly id = "tgl_detail">
                </div>
                <div class = "form-group">
                    <h5>Subjek Penawaran</h5>
                    <input list = "datalist_barang_jenis" type = "text"  required readonly id = "subjek_detail" class = "form-control">
                </div>
                <div class = "form-group">
                    <h5>Content Penawaran</h5>
                    <textarea class = "form-control" required readonly id = "content_detail"></textarea>
                </div>
                <div class = "form-group">
                    <h5>Notes Penawaran</h5>
                    <textarea class = "form-control" required readonly id = "notes_detail"></textarea>
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(row){
        $("#penawar_detail").val(content[row]["refrensi"]);
        $("#tgl_detail").val(content[row]["tgl"]);
        $("#subjek_detail").val(content[row]["subject"]);
        $("#content_detail").val(content[row]["content"]);
        $("#notes_detail").val(content[row]["notes"]);
    }
</script>