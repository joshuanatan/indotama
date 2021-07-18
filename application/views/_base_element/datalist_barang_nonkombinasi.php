<datalist id='datalist_barang_nonkombinasi'></datalist>
<script>
  load_datalist_barang_nonkombinasi();
  function load_datalist_barang_nonkombinasi() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/barang/list_data_nonkombinasi",
      type: "GET",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        var html_datalist_barang_nonkombinasi = "";
        if (respond["status"] == "SUCCESS") {
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_barang_nonkombinasi += `<option value = '${respond['content'][a]["nama"]}'>Nama Barang: ${respond["content"][a]["nama"]} Jenis Barang: ${respond["content"][a]["jenis_nama"]} Merk barang: ${respond["content"][a]["merk_nama"]} Satuan: ${respond["content"][a]["satuan"]}</option>`;
          }
          $("#datalist_barang_nonkombinasi").html(html_datalist_barang_nonkombinasi);
        }
      }
    });
  }
</script>