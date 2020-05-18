
<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <input type = "hidden" id = "id_delete">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Controller</td>
                            <td id = "controller_delete"></td>
                        </tr>
                        <tr>
                            <td>Menu Display</td>
                            <td id = "display_delete"></td>
                        </tr>
                        <tr>
                            <td>Icon</td>
                            <td id = "icon_delete"></td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td id = "kategori_delete"></td>
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
    function load_delete_content(id){
        $("#id_delete").val(content[id]["id"]);
        $("#controller_delete").html(content[id]["controller"]);
        $("#display_delete").html(content[id]["display"]);
        $("#icon_delete").html(content[id]["icon"]);
        $("#kategori_delete").html(content[id]["kategori"]);
    }
</script>