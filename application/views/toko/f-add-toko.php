
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
                <form id = "register_form" method = "POST">
                    <div class = "form-group col-lg-6">
                        <h5>Nama Toko</h5>
                        <input type = "text" class = "form-control" required name = "nama">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Kode Toko</h5>
                        <input type = "text" class = "form-control" required name = "kode">
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
    function register_func(){
        var form = $("#register_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/register",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData:false,
            contentType:false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#register_modal").modal("hide");
                    $("#register_form :input").val("");
                    refresh(page);
                }
            }
        });
    }
</script>