<script>
    function register_func(){
        var form = $("#register_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/register",
            type:"POST",
            dataType:"JSON",
            data:data,
            async:false,
            processData:false,
            contentType:false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $('#notif_register_success').show(1).delay(2000).hide(1);
                    $("#register_modal").modal("hide");
                    //$("#register_form :input").val("");
                    if(typeof(refresh) != "undefined"){
                        refresh(page);
                    }
                    //notification
                }

                if(respond["status"] == "ERROR"){
                    $('#regis_error_msg').empty();
                    $('#regis_error_msg').append(respond["msg"]);
                    $('#notif_register_error').show(1).delay(2000).hide(1);
                }
            },
            error:function(){
                //notification
                $('#regis_error_msg').empty();
                $('#regis_error_msg').append(respond["msg"]);
                $('#notif_register_error').show(1).delay(2000).hide(1);
            }
        });
    }
</script>