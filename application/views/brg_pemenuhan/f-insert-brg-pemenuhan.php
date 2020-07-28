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
            $this->load->view('_notification/update_error',$notif_data); ?>
                <form id = "register_form" method = "POST">
                    <input type = "hidden" name = "id_fk_brg_permintaan" id = "id_fk_brg_permintaan_insert">
                    <input type = "hidden" name = "brg_pemenuhan_tipe" id = "brg_pemenuhan_tipe_insert">
                    <input type = "hidden" name = "brg_skrg" id = "vbrg_skrg_insert">
                    <div class = "form-group">
                        <h5>Jumlah Pemenuhan</h5>
                        <input type = "text" class = "form-control nf-input" name="brg_pemenuhan_qty" required id = "brg_pemenuhan_qty_insert">
                    </div>
                    <h5>*Stok barang sekarang: <span id="brg_skrg_insert"></span><br>
                    </h5>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" id = "register-brg-pemenuhan-button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function load_edit_content(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            if(content[row]["status"].toLowerCase() != "aktif"){
                $("#register-brg-pemenuhan-button").hide();
                $("#register_modal input").attr("disabled",true);
                $("#register_modal input").val("Permintaan sudah diselesaikan, terima kasih");
            }
            else{
                
                $("#register-brg-pemenuhan-button").show();
                $("#register_modal input").attr("disabled",false);
                $("#register_modal input").val("");
            }
            $("#id_fk_brg_permintaan_insert").val(content[row]["id"]);
            $("#brg_skrg_insert").html(content[row]["jml_brg_cbg"]);
            $("#vbrg_skrg_insert").val(content[row]["jml_brg_cbg"]);
            $("#brg_pemenuhan_tipe_insert").val('<?php echo $type ?>');
        });
    }

</script>