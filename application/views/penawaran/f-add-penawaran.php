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
                        <h5>Penawar</h5>
                        <input type = "text" class = "form-control" required name = "penawar">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Penawaran</h5>
                        <input type = "date" class = "form-control" required name = "tgl">
                    </div>
                    <div class = "form-group">
                        <h5>Subjek Penawaran</h5>
                        <input list = "datalist_barang_jenis" type = "text"  required name = "subjek" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <h5>Content Penawaran</h5>
                        <textarea class = "form-control" required name = "content"></textarea>
                    </div>
                    <div class = "form-group">
                        <h5>Notes Penawaran</h5>
                        <textarea class = "form-control" required name = "notes"></textarea>
                    </div>
                    <div class = "form-group">
                        <h5>File Penawaran</h5>
                        <input type = "file" name = "file">
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
<script>
    var baris_barang_counter = 0;
    function tambah_baris_barang(){
        var html = "<tr><input type = 'hidden' name = 'check[]' value = '"+baris_barang_counter+"'><td><input type = 'text' class = 'form-control' list = 'datalist_barang' name = 'barang"+baris_barang_counter+"'></td><td><input type = 'text' class = 'form-control' name = 'qty"+baris_barang_counter+"'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#btn_tambah_baris_barang_container").before(html);
        baris_barang_counter++;
    }
</script>