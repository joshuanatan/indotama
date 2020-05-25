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
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group">
                        <h5>Nomor Pembelian</h5>
                        <input readonly type = "text" class = "form-control" list = "list_pembelian" required id = "no_pembelian_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Penerimaan</h5>
                        <input type = "date" class = "form-control" required name = "tgl_penerimaan" id = "tgl_penerimaan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Detail Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <tr>
                                <th>Cabang</th>
                                <td id = "detail_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>Alamat Cabang</th>
                                <td id = "detail_alamat_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>No Telp Cabang</th>
                                <td id = "detail_notelp_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td id = "detail_supplier_edit"></td>    
                            </tr>
                        </table>
                    </div>
                    <div class = "form-group">
                        <h5>Item Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Notes</th>
                                <th style = "width:30%">Penerimaan</th>
                            </thead>
                            <tbody id = "daftar_brg_beli_edit">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <h5>Tambahan Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Tambahan</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Notes</th>
                            </thead>
                            <tbody id = "daftar_tambahan_beli_edit">
                            </tbody>
                        </table>
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