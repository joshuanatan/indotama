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
                    <div class = "form-group col-lg-6">
                        <h5>Kode Barang</h5>
                        <input type = "text" class = "form-control" required name = "kode">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nama Barang</h5>
                        <input type = "text" class = "form-control" required name = "nama">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Jenis Barang</h5>
                        <input list = "datalist_barang_jenis" type = "text"  required name = "id_brg_jenis" class = "form-control">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Merk Barang</h5>
                        <input list = "datalist_barang_merk" type = "text"  required name = "id_brg_merk" class = "form-control">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Minimal Stok</h5>
                        <input type = "text" class = "form-control" required name = "minimal">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Satuan</h5>
                        <input type = "text" class = "form-control" required name = "satuan" list = "datalist_satuan">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Harga Satuan</h5>
                        <input type = "text" class = "form-control" required name = "harga">
                    </div>
                    <div class = "form-group">
                        <h5>Kombinasi Barang</h5>
                        <input type="radio" name = "kombinasi_barang" checked value="TIDAK KOMBINASI" onclick = "$('#barang_kombinasi_container').hide()">&nbsp;TIDAK KOMBINASI
                        &nbsp;&nbsp;
                        <input type="radio" name = "kombinasi_barang" value="KOMBINASI" onclick = "$('#barang_kombinasi_container').show()">&nbsp;KOMBINASI
                    </div>
                    <table class = "table table-striped table-bordered" id = "barang_kombinasi_container" style = "display:none">
                        <thead>
                            <th>Nama Barang</th>
                            <th>Qty (Pcs)</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <tr id = "btn_tambah_baris_barang_container">
                                <td colspan = 3>
                                    <button type = "button" onclick = "tambah_baris_barang()" class = "btn btn-primary btn-sm col-lg-12">Tambah Barang</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
<script>
    var baris_barang_counter = 0;
    function tambah_baris_barang(){
        var html = "<tr><input type = 'hidden' name = 'check[]' value = '"+baris_barang_counter+"'><td><input type = 'text' class = 'form-control' list = 'datalist_barang' name = 'barang"+baris_barang_counter+"'></td><td><input type = 'text' class = 'form-control' name = 'qty"+baris_barang_counter+"'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#btn_tambah_baris_barang_container").before(html);
        baris_barang_counter++;
    }
</script>