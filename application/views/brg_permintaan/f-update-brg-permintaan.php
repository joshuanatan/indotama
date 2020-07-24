<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog modal-lg">
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
                <form id = "update_form" method = "POST">
                    <input type = "hidden" name = "id_edit" id = "id_edit">
                    <input type = 'hidden' name = 'id_fk_cabang' value="<?php echo $_SESSION['id_cabang'] ?>" id = 'id_fk_cabang_edit'>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input list="list_brg_nama" name="brg_nama" id="brg_nama_edit" class = "form-control">
                        <datalist id="list_brg_nama">
                            <?php for($x=0; $x<count($barang); $x++){ ?>
                            <option value="<?php echo $barang[$x]['brg_nama'] ?>">
                            <?php } ?>
                        </datalist>
                    </div>
                    <div class = "form-group">
                        <h5>Jumlah Barang</h5>
                        <input type = "text" class = "form-control nf-input" name="brg_permintaan_qty" required id = "brg_permintaan_qty_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Notes</h5>
                        <textarea type = "text" class = "form-control" name="brg_permintaan_notes" required id = "brg_permintaan_notes_edit"></textarea>
                    </div>
                    <div class = "form-group">
                        <h5>Deadline</h5>
                        <input type = "date" class = "form-control" name="brg_permintaan_deadline" required id = "brg_permintaan_deadline_edit">
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
        $("#brg_permintaan_qty_edit").val(content[row]["qty"]);
        $("#brg_permintaan_notes_edit").val(content[row]["notes"]);
        $("#brg_permintaan_deadline_edit").val(content[row]["deadline"]);
        $("#brg_permintaan_status_edit").val(content[row]["status"]);
        $("#brg_nama_edit").val(content[row]["barang"]);
        $("#cabang_daerah_edit").val(content[row]["nama_cabang"]);
    }
</script>