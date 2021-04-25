
<datalist id = 'datalist_cabang'></datalist>
<script>
    function load_datalist_cabang_all(){
        $.ajax({
            url:"<?php echo base_url();?>ws/cabang/list_cabang",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_cabang = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        if(!respond['content'][a]["name"]){
                            respond['content'][a]["name"] = "-";
                        }
                        html_datalist_cabang+="<option value = '"+respond['content'][a]["name"]+"'>"+respond['content'][a]["kode"]+" / "+respond['content'][a]["daerah"]+"</option>";
                    }
                    $("#datalist_cabang").html(html_datalist_cabang);
                }
            }
        })
    }
</script>