<datalist id='datalist_barang_cabang_jualan'></datalist>
<script>
  var button_tmbh_cepat_barang_cabang = true;
  var url_add = "id_cabang=<?php echo $this->session->id_cabang; ?>";
  var datalist_barang_cabang_jualan;
  load_datalist_barang_cabang_jualan();
  function load_datalist_barang_cabang_jualan() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/barang_cabang/list_data_jualan?" + url_add,
      type: "GET",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        var html_datalist_barang_cabang_jualan = "";
        if (respond["status"] == "SUCCESS") {
          console.log(respond["content"]);
          datalist_barang_cabang_jualan = respond['content'];
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_barang_cabang_jualan += `
                        <option data-hargatoko = '${respond['content'][a]["harga_toko"]}' data-hargagrosir = '${respond['content'][a]["harga_grosir"]}' data-baseprice = '${respond['content'][a]["harga"]}' value = '${respond['content'][a]["nama"]}' data-lastprice = '${respond["content"][a]["last_price"]}' data-idpkbarang = '${respond['content'][a]["id_brg"]}'>
                            Kode Barang: ${respond["content"][a]["kode"].toUpperCase()} / Jenis: ${respond["content"][a]["jenis"]} / Merk: ${respond["content"][a]["merk"]} / Stok: ${respond["content"][a]["qty"]}
                        </option>`;
          }
          $("#datalist_barang_cabang_jualan").html(html_datalist_barang_cabang_jualan);
        }
      }
    });

  }
</script>