<datalist id = 'datalist_customer'></datalist>
<script>
    function load_datalist_customer(){
        $.ajax({
            url:"<?php echo base_url();?>ws/customer/list",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        if(!respond['content'][a]["nama"]){
                            respond['content'][a]["nama"] = "-";
                        }
                        html+="<option value = '"+respond['content'][a]["perusahaan"].toString().toUpperCase()+"'>Contact Person: "+respond['content'][a]["suff"]+" "+respond['content'][a]["name"].toString().toUpperCase()+" / "+respond['content'][a]["email"]+" / "+respond['content'][a]["hp"]+"</option>";
                    }
                    $("#datalist_customer").html(html);
                }
            }
        })
    }
</script>