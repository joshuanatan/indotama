<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                    <div class = "form-group">
                        <h5>Kode Barang</h5>
                        <input disabled type = 'text' class = 'form-control' name = 'brg' id = "d_kode">
                    </div>
                    <div class = "form-group">
                        <h5>Gambar Barang</h5>
                        <img id = "d_gambar_brg" width="200px">
                    </div>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input disabled type = 'text' class = 'form-control' list = 'datalist_barang' name = 'brg' id = "d_brg_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Stok</h5>
                        <input disabled type = "text" class = "form-control" required name = "stok" id = "d_stok_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Notes</h5>
                        <input disabled type = 'text' class = "form-control" required name = "notes" id = "d_notes_edit">
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
        $("#d_kode").val(content[row]["kode_brg"]);
        $("#d_brg_edit").val(content[row]["nama_brg"]);
        $("#d_stok_edit").val(content[row]["qty"]);
        $("#d_notes_edit").val(content[row]["notes"]);

        var brg = "<?php echo base_url() ?>asset/uploads/barang/" + content[row]["image_brg"];
        $("#d_gambar_brg").attr("src", brg);
    }
</script>