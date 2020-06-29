
<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Pembatalan <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/delete_error',$notif_data); ?>
                <form id = "delete_form" method = "POST">
                    <input type = 'hidden' name = 'id' id = 'id_delete'>
                    <input type = 'hidden' name = 'id_brg_pemenuhan' id = 'id_brg_pemenuhan_delete'>
                    <input type = 'hidden' name = 'type' value = '<?php echo $type;?>'>
                    <input type = 'hidden' name = 'tipe_pengiriman' value = '<?php echo $tipe_pengiriman;?>'>
                    <input type = 'hidden' name = 'id_tempat_pengiriman' value = '<?php echo $id_tempat_pengiriman;?>'>
                    <table class = "table table-striped table-bordered">
                        <tr>
                            <th>Nama Barang</th>
                            <td id = "brg_nama_delete"></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengiriman</th>
                            <td id = "tgl_pengiriman_delete"></td>
                        </tr>
                        <tr>
                            <th>Jumlah Pengiriman</th>
                            <td id = "brg_pemenuhan_qty_delete"></td>
                        </tr>
                        <tr>
                            <th>Nama Toko</th>
                            <td id = "toko_delete"></td>
                        </tr>
                        <tr>
                            <th>Daerah</th>
                            <td id = "cabang_delete"></td>
                        </tr>
                    </table>
                    <div class = "form-group">
                        <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger" style = "width:100%">Batalkan Pengiriman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 