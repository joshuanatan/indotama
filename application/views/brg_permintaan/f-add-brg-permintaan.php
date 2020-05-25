
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/register_error',$notif_data); ?>
                <form id = "register_form" method = "POST">
                    <input type = 'hidden' name = 'id_fk_cabang' value="<?php echo $_SESSION['id_cabang'] ?>" id = 'id_fk_cabang'>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input list="list_brg_nama" name="brg_nama" id="brg_nama" class = "form-control">
                        <datalist id="list_brg_nama">
                            <?php for($x=0; $x<count($barang); $x++){ ?>
                            <option value="<?php echo $barang[$x]['brg_nama'] ?>">
                            <?php } ?>
                        </datalist>
                    </div>
                    <div class = "form-group">
                        <h5>Jumlah Barang</h5>
                        <input type = "number" class = "form-control" name="brg_permintaan_qty" required id = "brg_permintaan_qty">
                    </div>
                    <div class = "form-group">
                        <h5>Notes</h5>
                        <textarea type = "text" class = "form-control" name="brg_permintaan_notes" required id = "brg_permintaan_notes"></textarea>
                    </div>
                    <div class = "form-group">
                        <h5>Deadline</h5>
                        <input type = "date" class = "form-control" name="brg_permintaan_deadline" required id = "brg_permintaan_deadline">
                    </div>
                    
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>