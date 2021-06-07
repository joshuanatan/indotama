<datalist id='datalist_retur_confirmed_kembali_barang'></datalist>
<script>
  var datalist_retur_confirmed_kembali_barang;
  load_datalist_retur_confirmed_kembali_barang();

  function load_datalist_retur_confirmed_kembali_barang() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/retur/list_data_confirmed_kembali_barang?id_cabang=<?php echo $this->session->id_cabang; ?>",
      type: "GET",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        var html_datalist_retur_confirmed_kembali_barang = "";
        if (respond["status"] == "SUCCESS") {
          console.log(respond["content"]);
          datalist_retur_confirmed_kembali_barang = respond['content'];
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_retur_confirmed_kembali_barang += "<option data-retur-tipe = '" + respond["content"][a]["tipe"] + "' data-retur-id-penjualan = '" + respond["content"][a]["id_penjualan"] + "' data-retur-id = '" + respond["content"][a]["id"] + "' value = '" + respond['content'][a]["no"] + "'>Tanggal Retur: " + respond["content"][a]["tgl"].split(" ")[0] + "</option>";
          }
          $("#datalist_retur_confirmed_kembali_barang").html(html_datalist_retur_confirmed_kembali_barang);
        }
      }
    });
  }
</script>