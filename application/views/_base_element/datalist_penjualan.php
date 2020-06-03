
<datalist id = 'datalist_penjualan'></datalist>
<script>
    function load_datalist_penjualan(){
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/list?id_cabang=<?php echo $this->session->id_cabang;?>",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_penjualan = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        if(!respond['content'][a]["nama"]){
                            respond['content'][a]["nama"] = "-";
                        }
                        html_datalist_penjualan+="<option value = '"+respond['content'][a]["nomor"]+"'>"+respond['content'][a]["perusahaan_cust"]+" / "+respond['content'][a]["name_cust"]+" / Tgl Jual: "+respond['content'][a]["tgl"]+"</option>";
                    }
                    $("#datalist_penjualan").html(html_datalist_penjualan);
                }
            }
        })
    }
</script>