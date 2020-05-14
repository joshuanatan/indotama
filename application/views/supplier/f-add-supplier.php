
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <form id = "register_form" method = "POST">
                    <div class = "form-group">
                        <h5>Nama Supplier</h5>
                        <input type = "text" class = "form-control" required name = "nama">
                    </div>
                    <div class = "form-group">
                        <h5>Nama PIC</h5>
                        <input type = "text" class = "form-control" required name = "pic">
                    </div>
                    <div class = "form-group">
                        <h5>Email</h5>
                        <input type = "text" class = "form-control" required name = "email">
                    </div>
                    <div class = "form-group">
                        <h5>No Telp</h5>
                        <input type = "text" class = "form-control" required name = "notelp">
                    </div>
                    <div class = "form-group">
                        <h5>No HP</h5>
                        <input type = "text" class = "form-control" required name = "nohp">
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type = "text" class = "form-control" required name = "alamat">
                    </div>
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan">
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