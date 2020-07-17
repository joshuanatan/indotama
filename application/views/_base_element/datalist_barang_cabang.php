
<datalist id = 'datalist_barang_cabang'></datalist>
<script>
    var button_tmbh_cepat_barang_cabang = true;
    var url_add = "id_cabang=<?php echo $this->session->id_cabang;?>";
    var datalist_barang_cabang;
    function load_datalist_barang_cabang(){
        $.ajax({
            url:"<?php echo base_url();?>ws/barang_cabang/list?"+url_add,
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_barang_cabang = "";
                if(respond["status"] == "SUCCESS"){
                    console.log(respond["content"]);
                    datalist_barang_cabang = respond['content'];
                    for(var a = 0; a<respond["content"].length; a++){
                        html_datalist_barang_cabang+="<option data-baseprice = '"+respond['content'][a]["harga"]+"' value = '"+respond['content'][a]["nama"]+"' data-lastprice = '"+respond["content"][a]["last_price"]+"'>"+respond["content"][a]["nama"].toString().toUpperCase()+" / Stok: "+respond["content"][a]["qty"]+" "+respond["content"][a]["satuan"].toString().toUpperCase()+"</option>";
                    }
                    $("#datalist_barang_cabang").html(html_datalist_barang_cabang);
                }
            }
        });
        
        if(button_tmbh_cepat_barang_cabang){
            $("[list='datalist_barang_cabang']").after(`<br/><a href = '<?php echo base_url();?>toko/brg_cabang' target = '_blank'>Tambah Cepat Barang Cabang</a>`);
            button_tmbh_cepat_barang_cabang = false;
        }
    }
</script>