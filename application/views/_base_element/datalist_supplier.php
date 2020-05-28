
<datalist id = 'datalist_supplier'></datalist>
<script>
    function load_datalist_supplier(){
        $.ajax({
            url:"<?php echo base_url();?>ws/supplier/list",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_supplier = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        if(!respond['content'][a]["nama"]){
                            respond['content'][a]["nama"] = "-";
                        }
                        html_datalist_supplier+="<option value = '"+respond['content'][a]["perusahaan"]+"'>"+respond['content'][a]["nama"]+"</option>";
                    }
                    $("#datalist_supplier").html(html_datalist_supplier);
                }
            }
        })
    }
</script>