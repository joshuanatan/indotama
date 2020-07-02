
<datalist id = 'datalist_retur_pengiriman'></datalist>
<script>
    var datalist_retur_pengiriman;
    function load_datalist_retur_pengiriman(){
        $.ajax({
            url:"<?php echo base_url();?>ws/retur/list_pengiriman?id_cabang=<?php echo $this->session->id_cabang;?>",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_retur_pengiriman = "";
                if(respond["status"] == "SUCCESS"){
                    console.log(respond["content"]);
                    datalist_retur_pengiriman = respond['content'];
                    for(var a = 0; a<respond["content"].length; a++){
                        html_datalist_retur_pengiriman += "<option data-retur-tipe = '"+respond["content"][a]["tipe"]+"' data-retur-id-penjualan = '"+respond["content"][a]["id_penjualan"]+"' data-retur-id = '"+respond["content"][a]["id"]+"' value = '"+respond['content'][a]["no"]+"'>Tanggal Retur: "+respond["content"][a]["tgl"].split(" ")[0]+"</option>";
                    }
                    $("#datalist_retur_pengiriman").html(html_datalist_retur_pengiriman);
                }
            }
        });
    }
</script>