<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                    <input type = "hidden" name = "id_edit"  disabled id = "d_id_edit">
                    <input type = 'hidden' name = 'id_fk_cabang' value="<?php echo $_SESSION['id_cabang'] ?>" id = 'id_fk_cabang_edit'>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input list="list_brg_nama" name="brg_nama" disabled id="d_brg_nama_edit" class = "form-control">
                        <datalist id="list_brg_nama">
                            <?php for($x=0; $x<count($barang); $x++){ ?>
                            <option value="<?php echo $barang[$x]['brg_nama'] ?>">
                            <?php } ?>
                        </datalist>
                    </div>
                    <div class = "form-group">
                        <h5>Jumlah Barang</h5>
                        <input type = "number" class = "form-control" name="brg_permintaan_qty" required  disabled id = "d_brg_permintaan_qty_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Notes</h5>
                        <textarea type = "text" class = "form-control" name="brg_permintaan_notes" required  disabled id = "d_brg_permintaan_notes_edit"></textarea>
                    </div>
                    <div class = "form-group">
                        <h5>Deadline</h5>
                        <input type = "date" class = "form-control" name="brg_permintaan_deadline" required  disabled id = "d_brg_permintaan_deadline_edit">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-success" data-dismiss = "modal">OK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(row){
        $("#d_id_edit").val(content[row]["id"]);
        $("#d_brg_permintaan_qty_edit").val(content[row]["qty"]);
        $("#d_brg_permintaan_notes_edit").val(content[row]["notes"]);
        $("#d_brg_permintaan_deadline_edit").val(content[row]["deadline"]);
        $("#d_brg_permintaan_status_edit").val(content[row]["status"]);
        $("#d_brg_nama_edit").val(content[row]["barang"]);
        $("#d_cabang_daerah_edit").val(content[row]["nama_cabang"]);
    }
</script>