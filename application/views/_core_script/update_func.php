<script>
    function update_func(){
        var form = $("#update_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/update",
            type:"POST",
            dataType:"JSON",
            data:data,
            async:false,
            processData: false,
            contentType: false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $('#notif_update_success').show(1).delay(2000).hide(1);
                    $("#update_modal").modal("hide");
                    refresh(page);
                }

                if(respond["status"] == "ERROR"){
                    $('#update_error_msg').empty();
                    $('#update_error_msg').append(respond["msg"]);
                    $('#notif_update_error').show(1).delay(2000).hide(1);
                }
            },
            error:function(){
                //notification
                $('#update_error_msg').empty();
                $('#update_error_msg').append(respond["msg"]);
                $('#notif_update_error').show(1).delay(2000).hide(1);
            }
        });
    }
</script>