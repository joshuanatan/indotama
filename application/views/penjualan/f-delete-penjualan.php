<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/delete_error',$notif_data); ?>
                <input type = "hidden" id = "id_delete">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Nomor Penjualan</td>
                            <td id = "nomor_delete"></td>
                        </tr>
                        <tr>
                            <td>Tanggal Penjualan</td>
                            <td id = "tgl_delete"></td>
                        </tr>
                        <tr>
                            <td>Dateline</td>
                            <td id = "dateline_delete"></td>
                        </tr>
                        <tr>
                            <td>Jenis Penjualan</td>
                            <td id = "jenis_delete"></td>
                        </tr>
                        <tr>
                            <td>Tipe Pembayaran</td>
                            <td id = "pembayaran_delete"></td>
                        </tr>
                        <tr>
                            <td>Customer</td>
                            <td id = "customer_delete"></td>
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">Cancel</button>
                    <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_delete_content(id){
        $("#id_delete").val(content[id]["id"]);
        $("#nomor_delete").html(content[id]["nomor"]);
        $("#tgl_delete").html(content[id]["tgl"]);
        $("#dateline_delete").html(content[id]["dateline_tgl"]);
        $("#jenis_delete").html(content[id]["jenis"]);
        $("#pembayaran_delete").html(content[id]["tipe_pembayaran"]);
        $("#customer_delete").html(content[id]["perusahaan_cust"]+" - "+content[id]["name_cust"]);
    }
</script>