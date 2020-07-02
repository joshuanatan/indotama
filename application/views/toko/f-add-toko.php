
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog">
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
                <form id = "register_form" method = "POST" enctype = "multipart/form-data">
                    <div class = "form-group">
                        <h5>Nama Toko</h5>
                        <input type = "text" class = "form-control" required name = "nama">
                    </div>
                    <div class = "form-group">
                        <h5>Kode Toko</h5>
                        <input type = "text" class = "form-control" required name = "kode">
                    </div>
                    <div class = "form-group">
                        <h5>Logo Toko</h5>
                        <input type = "file" class = "form-control" name = "logo">
                    </div>
                    <div class = "form-group">
                        <h5>Kop Surat</h5>
                        <input type = "file" class = "form-control" name = "kop_surat">
                    </div>
                    <div class = "form-group">
                        <h5>Surat Non PKP</h5>
                        <input type = "file" class = "form-control" name = "nonpkp">
                    </div>
                    <div class = "form-group">
                        <h5>Surat Pernyataan Nomor Rekening</h5>
                        <input type = "file" class = "form-control" name = "pernyataan_rek">
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