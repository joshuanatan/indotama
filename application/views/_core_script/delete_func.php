<script>
    function delete_func(){
        var id = $("#id_delete").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/delete?id="+id,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $('#notif_delete_success').show(1).delay(2000).hide(1);
                    $("#delete_modal").modal("hide");
                    refresh(page);
                    //notification
                }
                if(respond["status"] == "ERROR"){
                    $('#delete_error_msg').empty();
                    $('#delete_error_msg').append(respond["msg"]);
                    $('#notif_delete_error').show(1).delay(2000).hide(1);
                }
                
            },
            error:function(){
                //notification
                $('#delete_error_msg').empty();
                $('#delete_error_msg').append(respond["msg"]);
                $('#notif_delete_error').show(1).delay(2000).hide(1);
            }
        })
    }
</script>