<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                    <input type = "hidden" name = "id" id = "d_id_edit">
                    <div class = "form-group">
                        <h5>Nama Merk</h5>
                        <input disabled type = "text" class = "form-control" required name = "nama" id = "d_nama_edit">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                    </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(id){
        $("#d_id_edit").val(content[id]["id"]);
        $("#d_nama_edit").val(content[id]["nama"]);
    }
</script>