<script>
    function update_func(){
        var form = $("#update_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+form+"/update",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData: false,
            contentType: false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#update_modal").modal("hide");
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