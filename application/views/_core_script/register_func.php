
<script>
var default_register_form_html = $("#register_form").html();
function reset_register_form(){
    $("#register_form").html(default_register_form_html);
    /*bagian ini diperlukan untuk initiate script supaya enable formnya*/
    if(typeof(init_nf) != "undefined"){
        init_nf();
    }
    if(typeof(emp_add_form_script_init) != "undefined"){
        emp_add_form_script_init();
    } 
}
</script>
<script>
    function register_func(id_register_form = "register_form",id_register_modal = "register_modal"){
        if(typeof(nf_reformat_all) != "undefined"){
            nf_reformat_all();
        }
        var form = $(`#${id_register_form}`)[0];
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
                    $(`#${id_register_modal}`).modal("hide");
                    $(".form-reset").val("");
                    if(typeof(refresh) != "undefined"){
                        refresh(page);
                    }
                    if(typeof(reset_register_form) != "undefined"){
                        reset_register_form();
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