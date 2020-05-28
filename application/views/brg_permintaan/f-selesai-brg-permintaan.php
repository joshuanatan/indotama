<div class = "modal fade" id = "selesai_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );?>
            <div class="alert alert-danger alert-dismissable col-lg-11 align-self-center" id="notif_selesai_error" style="position: fixed; top:50%;z-index:100;left: 50%; margin-left: -45%;">
                <i class="zmdi zmdi-alert-circle-o pr-15 pull-left"></i><p class="pull-left">Penyelesaian <?php echo ucwords($page_title) ?> gagal!</p>
                <div class="clearfix"></div>
            </div>
                <form id = "selesai_form" method = "POST">
                    <input type = "hidden" name = "id_pk_brg_permintaan" id = "id_pk_brg_permintaan_selesai">
                    <table class = "table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Nama Barang</th>
                                <td id = "brg_nama_selesai"></td>    
                            </tr>
                            <tr>
                                <td>Qty Permintaan</td>
                                <td id = "brg_permintaan_qty_selesai"></td>
                            </tr>
                            <tr>
                                <td>Qty Pemenuhan</td>
                                <td id = "brg_pemenuhan_qty_selesai"></td>
                            </tr>
                            <tr>
                                <td>Notes Permintaan</td>
                                <td id = "brg_permintaan_notes_selesai"></td>
                            </tr>
                            <tr>
                                <th>Status Permintaan</th>
                                <td id = "brg_permintaan_status_selesai"></td>    
                            </tr>
                            
                        </tbody>
                    </table>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "selesai_func()" class = "btn btn-sm btn-primary">Selesaikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function load_selesai_content(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            $("#id_pk_brg_permintaan_selesai").val(content[row]["id"]);
            $("#brg_nama_selesai").html(content[row]["barang"]);
            $("#brg_permintaan_qty_selesai").html(content[row]["qty"]);
            $("#brg_pemenuhan_qty_selesai").html(content[row]["qty_pemenuhan"]);
            $("#brg_permintaan_notes_selesai").html(content[row]["notes"]);
            $("#brg_permintaan_status_selesai").html(content[row]["status"]);
        });
    }
</script>
<script>
    function selesai_func(){
        var form = $("#selesai_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/selesai",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData:false,
            contentType:false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#selesai_modal").modal("hide");
                    $("#selesai_form :input").val("");
                    refresh(page);
                    //notification
                    $('#notif_update_success').show(1).delay(2000).hide(1);
                }
            },
            error:function(){
                //notification
                $('#notif_update_error').show(1).delay(2000).hide(1);
            }
        });
    }
</script>