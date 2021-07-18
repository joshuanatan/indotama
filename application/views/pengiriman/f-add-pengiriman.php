<div class="modal fade" id="register_modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Tambah Data <?php echo ucwords($page_title); ?></h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/register_error', $notif_data); ?>
        <form id="register_form" method="POST">
          <input type='hidden' name='id_reff' id='id_penjualan'>
          <input type='hidden' name='type' value='<?php echo $type; ?>'>
          <input type='hidden' name='tipe_pengiriman' value='<?php echo $tipe_pengiriman; ?>'>
          <input type='hidden' name='id_tempat_pengiriman' value='<?php echo $id_tempat_pengiriman; ?>'>
          <div class="form-group">
            <h5>Nomor Penjualan</h5>
            <input type="text" class="form-control" list="datalist_penjualan" required id="no_penjualan">
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-primary btn-sm" style="width:20%" onclick="load_detail_penjualan()">Load Data Barang</button>
          </div>
          <div class="form-group">
            <h5>Tanggal Pengiriman</h5>
            <input type="date" class="form-control" required name="tgl_pengiriman">
          </div>
          <div class="form-group">
            <h5>Detail Penjualan</h5>
            <table class="table table-striped table-bordered">
              <tr>
                <th>Perusahaan Customer</th>
                <td id="perusahaan_cust"></td>
              </tr>
              <tr>
                <th>Contact Person</th>
                <td id="cp_cust"></td>
              </tr>
              <tr>
                <th>Email / No HP</th>
                <td id="contact_cust"></td>
              </tr>
            </table>
          </div>
          <!-- <div class = "form-group">
            <h5>Custom Produk</h5>
            <table class="table table-striped table-bordered">
              <thead>
                <th>Produk Asal</th>
                <th>Produk Custom</th>
                <th>Qty (Pcs)</th>
                <th>Action</th>
              </thead>
              <tbody id="daftar_custom_produk_add">
                <tr id="add_custom_produk_but_container">
                  <td colspan=6><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="produk_custom_row_add()">Tambah Barang Penjualan</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div> -->
          <script>
          function produk_custom_row_add(){
            var custom_produk_row = $(".row_brg_custom_add").length;
            var html = `
              <tr class = "row_brg_custom_add">
                <input type = "hidden" name = "produk_custom_row_add_check[]" value = "${custom_produk_row}">
                <td><input name = 'custom_brg_awal${custom_produk_row}' list = 'datalist_barang_cabang_jualan' type = 'text' class = 'form-control'></td>
                <td><input name = 'custom_brg_akhir${custom_produk_row}' list = 'datalist_barang_cabang_jualan' type = 'text' class = 'form-control'></td>
                <td><input name = 'custom_brg_qty${custom_produk_row}' type = 'text' class = 'form-control nf-input'></td>
                <td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td>
              </tr>`;
              $("#add_custom_produk_but_container").before(html);
          }
          </script>
          <div class="form-group">
            <h5>Item Penjualan</h5>
            <table class="table table-striped table-bordered">
              <thead>
                <th>Barang</th>
                <th>Jumlah</th>
                <th>Notes</th>
                <th style="width:30%">Pengiriman</th>
              </thead>
              <tbody id="daftar_brg_beli">
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
              <tbody id="daftar_tambahan_beli">
              </tbody>
            </table>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="register_func()" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function load_detail_penjualan() {
    var satuan_opt = "";
    var no_penjualan = $("#no_penjualan").val();
    var detail_penjualan;
    var content_brg_penjualan;
    $.ajax({
      url: "<?php echo base_url(); ?>ws/penjualan/detail/" + no_penjualan,
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"] == "SUCCESS") {
          detail_penjualan = respond["data"];
          $("#id_penjualan").val(respond["data"][0]["id"]);
          $("#perusahaan_cust").html(respond["data"][0]["cust_perusahaan"]);
          $("#cp_cust").html(respond["data"][0]["suff_cust"] + ". " + respond["data"][0]["name_cust"]);
          respond["data"][0]["email_cust"] == 'null' ? respond["data"][0]["email_cust"] = respond["data"][0]["email_cust"] : respond["data"][0]["email_cust"] = "-";
          respond["data"][0]["telp_cust"] == 'null' ? respond["data"][0]["telp_cust"] = respond["data"][0]["telp_cust"] : respond["data"][0]["telp_cust"] = "-";
          respond["data"][0]["hp_cust"] == 'null' ? respond["data"][0]["hp_cust"] = respond["data"][0]["hp_cust"] : respond["data"][0]["hp_cust"] = "-";
          $("#contact_cust").html(respond["data"][0]["email_cust"] + " / " + respond["data"][0]["telp_cust"] + " / " + respond["data"][0]["hp_cust"]);
        }
      }
    });
    $.ajax({
      url: "<?php echo base_url(); ?>ws/penjualan/brg_penjualan?id=" + detail_penjualan[0]["id"],
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        $(".brg_penjualan_row").remove();
        if (respond["status"] == "SUCCESS") {
          content_brg_penjualan = respond["content"];
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html += `
              <tr class = 'brg_penjualan_row'>
                <input type = 'hidden' name = 'check[]' value = '${a}'>
                <input type = 'hidden' value = '${respond["content"][a]["id"]}' name = 'id_brg${a}'>
                <td>
                  ${respond["content"][a]["nama_brg"]}<br/>
                  Notes:${respond["content"][a]["note"]}
                </td>
                <td>
                  ${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}
                </td>
                <td>
                  <input type = 'text' class = 'form-control' name = 'notes${a}'>
                </td>
                <td>
                  <div style = 'display:inline-block'>
                    <input value = 0 type = 'text' class = 'form-control nf-input' style = 'width:50%; display:inline-block' name = 'qty_kirim${a}'>
                    <select class = 'form-control satuan_opt' style = 'width:50%; display:inline-block' name = 'id_satuan${a}'></select>
                  </div>
                </td>
              </tr>`;
          }
          $("#daftar_brg_beli").html(html);
          init_nf();
          var html_datalist_satuan = "";
          for (var a = 0; a < datalist_satuan.length; a++) {
            html_datalist_satuan += `
              <option value = '${datalist_satuan[a]["id"]}'>
                ${datalist_satuan[a]["nama"].toString().toUpperCase()} / Rumus: ${datalist_satuan[a]["rumus"]}
              </option>`;
          }
          $(".satuan_opt").html(html_datalist_satuan);
        }
      }
    });
    $.ajax({
      url: "<?php echo base_url(); ?>ws/penjualan/tmbhn_penjualan?id=" + detail_penjualan[0]["id"],
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        $(".tmbhn_penjualan_row").remove();
        if (respond["status"] == "SUCCESS") {
          content_brg_penjualan = respond["content"];
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html += `
              <tr class = 'tmbhn_penjualan_row'>
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
          $("#daftar_tambahan_beli").html(html);
        }
      }
    });
  }
</script>

<?php $this->load->view("_base_element/datalist_barang_cabang_jualan"); ?>