<div class="modal fade" id="detail_modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Detail Data <?php echo ucwords($page_title); ?></h4>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="d_id_edit">
        <div class="form-group">
          <h5>Nomor Penjualan</h5>
          <input type="text" class="form-control" readonly id="d_no_penjualan_edit">
        </div>
        <div class="form-group">
          <h5>Tanggal Pengiriman</h5>
          <input disabled type="date" class="form-control" required name="tgl_pengiriman" id="d_tgl_pengiriman_edit">
        </div>
        <div class="form-group">
          <h5>Detail Penjualan</h5>
          <table class="table table-striped table-bordered">
            <tr>
              <th>Perusahaan Customer</th>
              <td id="d_perusahaan_cust_edit"></td>
            </tr>
            <tr>
              <th>Contact Person</th>
              <td id="d_cp_cust_edit"></td>
            </tr>
            <tr>
              <th>Email / No HP</th>
              <td id="d_contact_cust_edit"></td>
            </tr>
          </table>
        </div>
        <div class="form-group">
          <h5>Item Penjualan</h5>
          <table class="table table-striped table-bordered">
            <thead>
              <th>Barang</th>
              <th>Jumlah</th>
              <th>Notes</th>
              <th style="width:30%">Pengiriman</th>
            </thead>
            <tbody id="d_daftar_brg_beli_edit">
            </tbody>
          </table>
        </div>
        <div class="form-group">
          <h5>Tambahan Penjualan</h5>
          <table class="table table-striped table-bordered">
            <thead>
              <th>Tambahan</th>
              <th>Jumlah</th>
              <th>Notes</th>
            </thead>
            <tbody id="d_daftar_tambahan_beli_edit">
            </tbody>
          </table>
        </div>
        <div class="form-group">
          <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  function load_detail_content(row) {
    var satuan_opt = "";
    $("#d_id_edit").val(content[row]["id"]);
    $("#d_no_penjualan_edit").val(content[row]["nomor_penj"]);
    var tgl = content[row]["tgl"].split(" ");
    $("#d_tgl_pengiriman_edit").val(tgl[0]);
    $("#d_perusahaan_cust_edit").html(content[row]["perusahaan_cust"]);
    content[row]["suff_cust"] == 'null' ? content[row]["suff_cust"] = content[row]["suff_cust"] : content[row]["suff_cust"] = "-";
    content[row]["name_cust"] == 'null' ? content[row]["name_cust"] = content[row]["name_cust"] : content[row]["name_cust"] = "-";
    content[row]["email_cust"] == 'null' ? content[row]["email_cust"] = content[row]["email_cust"] : content[row]["email_cust"] = "-";
    content[row]["hp_cust"] == 'null' ? content[row]["hp_cust"] = content[row]["hp_cust"] : content[row]["hp_cust"] = "-";
    $("#d_cp_cust_edit").html(content[row]["suff_cust"] + ". " + content[row]["name_cust"]);
    $("#d_contact_cust_edit").html(content[row]["email_cust"] + " / " + content[row]["hp_cust"]);

    $.ajax({
      url: "<?php echo base_url(); ?>ws/pengiriman/brg_pengiriman?id=" + content[row]["id"],
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        $(".d_brg_pembelian_row").remove();
        if (respond["status"] == "SUCCESS") {
          var html_datalist_satuan = "";
          for (var a = 0; a < datalist_satuan.length; a++) {
            html_datalist_satuan += "<option value = '" + datalist_satuan[a]["id"] + "'>" + datalist_satuan[a]["nama"].toString().toUpperCase() + " / Rumus: " + datalist_satuan[a]["rumus"] + "</option>";
          }
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html += `
                        <tr class = 'd_brg_pembelian_row'>
                            <input disabled type = 'hidden' name = 'check[]' value = '${a}'>
                            <input disabled type = 'hidden' value = '${respond["content"][a]["id"]}' name = 'id_brg_kirim${a}'>
                            <td>
                                ${respond["content"][a]["nama_brg"]}<br/>
                                Notes:${respond["content"][a]["note_brg_penjualan"]}
                            </td>
                            <td>
                                ${respond["content"][a]["qty_brg_penjualan"]} ${respond["content"][a]["satuan_brg_penjualan"]}
                            </td>
                            <td>
                                <input disabled type = 'text' class = 'form-control' name = 'notes${a}' value = '${respond["content"][a]["note"]}'>
                            </td>
                            <td>
                                <div style = 'display:inline-block'>
                                    <input disabled value = '${respond["content"][a]["qty"]}' type = 'text' class = 'form-control' style = 'width:50%; display:inline-block' name = 'qty_kirim${a}'>
                                    <select disabled class = 'form-control satuan_opt' style = 'width:50%; display:inline-block' id = 'd_id_satuan_edit${a}' name = 'id_satuan${a}'>${html_datalist_satuan}</select>
                                </div>
                            </td>
                        </tr>`;
          }
          $("#d_daftar_brg_beli_edit").html(html);
          for (var a = 0; a < respond["content"].length; a++) {
            $("#id_satuan_edit" + a).val(respond["content"][a]["id_satuan"]);
          }
        }
      }
    });
    $.ajax({
      url: "<?php echo base_url(); ?>ws/pembelian/tmbhn_pembelian?id=" + content[row]["id"],
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        $(".d_tmbhn_pembelian_row").remove();
        if (respond["status"] == "SUCCESS") {
          content_brg_pembelian = respond["content"];
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html += `
                        <tr class = 'd_tmbhn_pembelian_row'>
                            <td>
                                ${respond["content"][a]["tmbhn"]}
                            </td>
                            <td>
                                ${respond["content"][a]["jumlah"]} ${respond["content"][a]["satuan"]}
                            </td>
                            <td>
                                ${respond["content"][a]["notes"]}
                            </td>
                        </tr>`;
          }
          $("#d_daftar_tambahan_beli_edit").html(html);
        }
      }
    });
  }
</script>