<datalist id='datalist_barang_cabang'></datalist>
<script>
  var button_tmbh_cepat_barang_cabang = true;
  var url_add = "id_cabang=<?php echo $this->session->id_cabang; ?>";
  var datalist_barang_cabang;
  
  load_datalist_barang_cabang();
  function load_datalist_barang_cabang() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/barang_cabang/list_data?" + url_add,
      type: "GET",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        var html_datalist_barang_cabang = "";
        if (respond["status"] == "SUCCESS") {
          console.log(respond["content"]);
          datalist_barang_cabang = respond['content'];
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_barang_cabang += `
                        <option data-baseprice = '${respond['content'][a]["harga"]}' value = '${respond['content'][a]["nama"]}' data-lastprice = '${respond["content"][a]["last_price"]}'>
                            Kode Barang: ${respond["content"][a]["kode"].toUpperCase()} / Jenis: ${respond["content"][a]["jenis"]} / Merk: ${respond["content"][a]["merk"]} / Stok: ${respond["content"][a]["qty"]}
                        </option>`;
          }
          $("#datalist_barang_cabang").html(html_datalist_barang_cabang);
        }
      }
    });

  }
</script>