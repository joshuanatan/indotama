
<datalist id = 'datalist_toko'></datalist>
<script>
    var datalist_toko;
    function load_datalist_toko(){
        $.ajax({
            url:"<?php echo base_url();?>ws/toko/list_data",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_toko = "";
                if(respond["status"] == "SUCCESS"){
                    console.log(respond["content"]);
                    datalist_toko = respond['content'];
                    for(var a = 0; a<respond["content"].length; a++){
                        html_datalist_toko+="<option value = '"+respond['content'][a]["nama"]+"'>"+respond["content"][a]["nama"].toString().toUpperCase()+" / Kode: "+respond["content"][a]["kode"]+"</option>";
                    }
                    $("#datalist_toko").html(html_datalist_toko);
                }
            }
        });
    }
</script>