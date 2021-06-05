<div class="modal fade" id="update_modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Ubah Data <?php echo ucwords($page_title); ?></h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/update_error', $notif_data); ?>
        <form id="update_form" method="POST">
          <input type="hidden" name="id" id="id_edit">
          <input type='hidden' name='tipe_pengiriman' value='<?php echo $tipe_pengiriman; ?>'>
          <div class="form-group">
            <h5>Nomor Penjualan</h5>
            <input type="text" class="form-control" readonly id="no_penjualan_edit">
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-primary btn-sm" style="width:20%" onclick="load_detail_penjualan()">Load Data Barang</button>
          </div>
          <div class="form-group">
            <h5>Tanggal Pengiriman</h5>
            <input type="date" class="form-control" required name="tgl_pengiriman" id="tgl_pengiriman_edit">
          </div>
          <div class="form-group">
            <h5>Detail Penjualan</h5>
            <table class="table table-striped table-bordered">
              <tr>
                <th>Perusahaan Customer</th>
                <td id="perusahaan_cust_edit"></td>
              </tr>
              <tr>
                <th>Contact Person</th>
                <td id="cp_cust_edit"></td>
              </tr>
              <tr>
                <th>Email / No HP</th>
                <td id="contact_cust_edit"></td>
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
              <tbody id="daftar_brg_beli_edit">
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
              <tbody id="daftar_tambahan_beli_edit">
              </tbody>
            </table>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="update_func()" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function load_edit_content(row) {
    var satuan_opt = "";
    $("#id_edit").val(content[row]["id"]);
    $("#no_penjualan_edit").val(content[row]["nomor_penj"]);
    var tgl = content[row]["tgl"].split(" ");
    $("#tgl_pengiriman_edit").val(tgl[0]);
    $("#perusahaan_cust_edit").html(content[row]["perusahaan_cust"]);
    content[row]["suff_cust"] == 'null' ? content[row]["suff_cust"] = content[row]["suff_cust"] : content[row]["suff_cust"] = "-";
    content[row]["name_cust"] == 'null' ? content[row]["name_cust"] = content[row]["name_cust"] : content[row]["name_cust"] = "-";
    content[row]["email_cust"] == 'null' ? content[row]["email_cust"] = content[row]["email_cust"] : content[row]["email_cust"] = "-";
    content[row]["hp_cust"] == 'null' ? content[row]["hp_cust"] = content[row]["hp_cust"] : content[row]["hp_cust"] = "-";
    $("#cp_cust_edit").html(content[row]["suff_cust"] + ". " + content[row]["name_cust"]);
    $("#contact_cust_edit").html(content[row]["email_cust"] + " / " + content[row]["hp_cust"]);

    $.ajax({
      url: "<?php echo base_url(); ?>ws/pengiriman/brg_pengiriman?id=" + content[row]["id"],
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        $(".brg_pembelian_row").remove();
        if (respond["status"] == "SUCCESS") {
          var html_datalist_satuan = "";
          for (var a = 0; a < datalist_satuan.length; a++) {
            html_datalist_satuan += "<option value = '" + datalist_satuan[a]["id"] + "'>" + datalist_satuan[a]["nama"].toString().toUpperCase() + " / Rumus: " + datalist_satuan[a]["rumus"] + "</option>";
          }
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html += `
                        <tr class = 'brg_pembelian_row'>
                            <input type = 'hidden' name = 'check[]' value = '${a}'>
                            <input type = 'hidden' value = '${respond["content"][a]["id"]}' name = 'id_brg${a}'>
                            <td>
                                ${respond["content"][a]["nama_brg"]}<br/>
                                Notes:${respond["content"][a]["note_brg_penjualan"]}
                            </td>
                            <td>
                                ${respond["content"][a]["qty_brg_penjualan"]} ${respond["content"][a]["satuan_brg_penjualan"]}
                            </td>
                            <td>
                                <input type = 'text' class = 'form-control' name = 'notes${a}' value = '${respond["content"][a]["note"]}'>
                            </td>
                            <td>
                                <div style = 'display:inline-block'>
                                    <input value = '${respond["content"][a]["qty"]}' type = 'text' class = 'form-control nf-input' style = 'width:50%; display:inline-block' name = 'qty_kirim${a}'>
                                    <select class = 'form-control satuan_opt' style = 'width:50%; display:inline-block' id = 'id_satuan_edit${a}' name = 'id_satuan${a}'>${html_datalist_satuan}</select>
                                </div>
                            </td>
                        </tr>`;
            init_nf();
          }
          $("#daftar_brg_beli_edit").html(html);
          for (var a = 0; a < respond["content"].length; a++) {
            $(`#id_satuan_edit${a}`).val(respond["content"][a]["id_satuan"]);
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
        $(".tmbhn_pembelian_row").remove();
        if (respond["status"] == "SUCCESS") {
          content_brg_pembelian = respond["content"];
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html += `
                        <tr class = 'tmbhn_pembelian_row'>
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
          $("#daftar_tambahan_beli_edit").html(html);
        }
      }
    });
  }
</script>