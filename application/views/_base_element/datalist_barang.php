
<datalist id = 'datalist_barang'></datalist>
<script>
    function load_datalist_barang(){
        $.ajax({
            url:"<?php echo base_url();?>ws/barang/list",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_barang = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html_datalist_barang+=`<option value = '${respond['content'][a]["nama"]}'>Nama Barang: ${respond["content"][a]["nama"]} Jenis Barang: ${respond["content"][a]["jenis_nama"]} Merk barang: ${respond["content"][a]["merk_nama"]} Satuan: ${respond["content"][a]["satuan"]}</option>`;
                    }
                    $("#datalist_barang").html(html_datalist_barang);
                }
            }
        });
    }
    function load_datalist_barang_nonkombinasi(){
        $.ajax({
            url:"<?php echo base_url();?>ws/barang/list",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_barang = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        if(respond["content"][a]["tipe"].toLowerCase() == "nonkombinasi"){
                            html_datalist_barang+=`<option value = '${respond['content'][a]["nama"]}'>Nama Barang: ${respond["content"][a]["nama"]} Jenis Barang: ${respond["content"][a]["jenis_nama"]} Merk barang: ${respond["content"][a]["merk_nama"]} Satuan: ${respond["content"][a]["satuan"]}</option>`;
                        }
                    }
                    $("#datalist_barang").html(html_datalist_barang);
                }
            }
        });
    }
</script>