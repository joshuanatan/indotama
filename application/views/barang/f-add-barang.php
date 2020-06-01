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
                        <h5>Kode Barang</h5>
                        <input type = "text" class = "form-control" required name = "kode">
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Barang</h5>
                        <input list = "datalist_barang_jenis" type = "text"  required name = "id_brg_jenis" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input type = "text" class = "form-control" required name = "nama">
                    </div>
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan">
                    </div>
                    <div class = "form-group">
                        <h5>Merk Barang</h5>
                        <input list = "datalist_barang_merk" type = "text"  required name = "id_brg_merk" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <h5>Minimal Stok</h5>
                        <input type = "text" class = "form-control" required name = "minimal">
                    </div>
                    <div class = "form-group">
                        <h5>Satuan</h5>
                        <input type = "text" class = "form-control" required name = "satuan">
                    </div>
                    <div class = "form-group">
                        <h5>Harga Satuan</h5>
                        <input type = "text" class = "form-control" required name = "harga">
                    </div>
                    <div class = "form-group">
                        <h5>Gambar</h5>
                        <input type = "file" required name = "gambar">
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