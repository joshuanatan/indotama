<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <form id = "update_form" method = "POST">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group">
                        <h5>Daerah Cabang</h5>
                        <input type = "text" class = "form-control" required name = "daerah" id = "daerah_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Alamat Cabang</h5>
                        <input type = "text" class = "form-control" required name = "alamat" id = "alamat_edit">
                    </div>
                    <div class = "form-group">
                        <h5>No Telp Cabang</h5>
                        <input type = "text" class = "form-control" required name = "notelp" id = "notelp_edit">
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
    function load_edit_content(id){
        $("#id_edit").val(content[id]["id"]);
        $("#daerah_delete").html(content[id]["daerah"]);
        $("#alamat_delete").html(content[id]["alamat"]);
        $("#notelp_delete").html(content[id]["notelp"]);
    }
</script>