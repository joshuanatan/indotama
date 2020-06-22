
<datalist id = 'datalist_user'></datalist>
<script>
    function load_datalist_user(){
        $.ajax({
            url:"<?php echo base_url();?>ws/user/list",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html+="<option value = '"+respond['content'][a]["name"]+"'></option>";
                    }
                    $("#datalist_user").html(html);
                }
            }
        });
    }
</script>