
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <div class = "form-group">
                    <h5>Nama Marketplace</h5>
                    <input type = "text" class = "form-control" required readonly name = "nama" id = "nama_detail">
                </div>
                <div class = "form-group">
                    <h5>Keterangan</h5>
                    <input type = "text" class = "form-control" required readonly name = "keterangan" id = "keterangan_detail">
                </div>
                <div class = "form-group">
                    <h5>Biaya</h5>
                    <input type = "text" class = "form-control" required readonly name = "biaya" id = "biaya_detail">
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var barang_kombinasi_list;
    function load_detail_content(row){
        $("#nama_detail").val(content[row]["nama"]);
        $("#keterangan_detail").val(content[row]["ket"]);
        $("#biaya_detail").val(content[row]["biaya"]);
    }
</script>