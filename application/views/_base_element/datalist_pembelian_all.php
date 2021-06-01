<datalist id="datalist_pembelian"></datalist>

<script>
  load_datalist_pembelian_all();
  function load_datalist_pembelian_all() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/pembelian/list_pembelian_all",
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        var html = "";
        if (respond["status"] == "SUCCESS") {
          for (var a = 0; a < respond["content"].length; a++) {
            html += "<option value = '" + respond["content"][a]["nomor"] + "'>" + respond["content"][a]["nama_toko"] + " - " + respond["content"][a]["daerah_cabang"] + " / Supplier: " + respond["content"][a]["perusahaan_sup"] + "</option>";
          }
          $("#datalist_pembelian").html(html);
        }
      }
    })
  }
</script>