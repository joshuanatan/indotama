
<datalist id = 'datalist_barang_merk'></datalist>
<script>
    var datalist_barang_merk;
    function load_datalist_barang_merk(){
        $.ajax({
            url:"<?php echo base_url();?>ws/barang_merk/list",
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    datalist_barang_merk = respond["content"];
                    var html_datalist_barang_merk = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html_datalist_barang_merk += "<option value = '"+respond["content"][a]["nama"]+"'>";
                    }
                    $("#datalist_barang_merk").html(html_datalist_barang_merk);
                }
            },
            error:function(){

            }
        });
    }
</script>