<?php
$page_title = "Penjualan";
$breadcrumb = array(
  "Penjualan", "Tambah Penjualan"
);
$notif_data = array(
  "page_title" => $page_title
);
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <?php $this->load->view('req/mm_css.php'); ?>
  </head>

  <body>
    <div class="preloader-it">
      <div class="la-anim-1"></div>
    </div>
    <div class="wrapper theme-1-active pimary-color-pink">
      <?php $this->load->view('req/mm_menubar.php'); ?>
      <div class="page-wrapper">
        <?php $this->load->view('_notification/register_success', $notif_data); ?>
        <?php $this->load->view('_notification/register_error', $notif_data); ?>
        <div class="alert alert-success alert-dismissable col-lg-5 pull-right position-fixed" id="notif_register_success_cust" style="position: fixed; top:30%;right:5%;z-index:100">
          <i class="zmdi zmdi-check pr-15 pull-left"></i>
          <p class="pull-left">Register <?php echo ucwords($page_title) ?> berhasil!</p>
          <div class="clearfix"></div>
        </div>
        <div class="container-fluid">
          <div class="row mt-20">
            <div class="col-lg-12 col-sm-12">
              <div class="panel panel-default card-view">
                <div class="panel-heading bg-gradient">
                  <div class="pull-left">
                    <h6 class="panel-title txt-light"><?php echo ucwords($page_title); ?></h6>
                  </div>
                  <div class="clearfix"></div>
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">Home</a></li>
                    <?php for ($a = 0; $a < count($breadcrumb); $a++) : ?>
                      <?php if ($a + 1 != count($breadcrumb)) : ?>
                        <li class="breadcrumb-item"><?php echo ucwords($breadcrumb[$a]); ?></a></li>
                      <?php else : ?>
                        <li class="breadcrumb-item active"><?php echo ucwords($breadcrumb[$a]); ?></li>
                      <?php endif; ?>
                    <?php endfor; ?>
                  </ol>
                </div>
                <div class="panel-wrapper collapse in">
                  <div class="panel-body">
                    <div class="col-lg-12">
                      <form id="update_form" method="POST">
                        <input type = "hidden" required name = "id_pk_penjualan" id = "id_pk_penjualan">
                        <div class="form-group col-lg-12">
                          <h5>Nomor Penjualan</h5>
                          <input required type='text' class="form-control" required name="no_penjualan" id = "no_penjualan">
                        </div>
                        <div class="form-group col-lg-12">
                          <h5>Customer</h5>
                          <input required type='text' class="form-control" list="datalist_customer_toko" required name="customer" id = "customer">
                        </div>
                        <div class="form-group col-lg-6">
                          <h5>Tanggal Penjualan</h5>
                          <input required type="date" class="form-control" required name="tgl" id = "tgl">
                        </div>
                        <div class="form-group col-lg-6">
                          <h5>Dateline</h5>
                          <input required type="date" class="form-control" required name="dateline" id = "dateline">
                        </div>
                        <div class="form-group col-lg-6">
                          <h5>Kurir</h5>
                          <input type required="text" class="form-control" required name="kurir" id = "kurir">
                        </div>
                        <div class="form-group col-lg-6">
                          <h5>No Resi</h5>
                          <input type required="text" class="form-control" required name="no_resi" id = "no_resi">
                        </div>
                        <div class="form-group col-lg-6">
                          <h5>Jenis Penjualan</h5>
                          <input checked type="radio" name="jenis_penjualan" value="OFFLINE" onclick="$('#online_info_container').hide()">&nbsp;OFFLINE
                          &nbsp;&nbsp;
                          <input type="radio" name="jenis_penjualan" value="ONLINE" onclick="open_online_container()">&nbsp;ONLINE
                        </div>
                        <div id="online_info_container" style="display:none">
                          <div class="form-group col-lg-6">
                            <h5>Marketplace</h5>
                            <select required class="form-control" required name="marketplace" id="marketplace"></select>
                          </div>
                        </div>
                        <div class="form-group col-lg-12">
                          <h5>Custom</h5>
                          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#custom_produk_modal">Custom Produk</button>
                        </div>
                        <div class="form-group col-lg-12">
                          <h5>Produk Custom</h5>
                          <table class="table table-striped table-bordered" style="width:50%">
                            <thead>
                              <th>Barang Awal</th>
                              <th>Barang Pindah</th>
                              <th>Jumlah</th>
                            </thead>
                            <tbody id="daftar_brg_custom_container">
                            </tbody>
                          </table>
                        </div>

                        <div class="form-group col-lg-12">
                          <h5>Item Penjualan</h5>
                          <div class = "table-responsive">
                            <table class="table table-striped table-bordered">
                              <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th style = "width:20%">Daftar Harga</th>
                                <th>Harga Jual</th>
                                <th>Harga Final</th>
                                <th>Notes</th>
                                <th>Action</th>
                              </thead>
                              <tbody id="daftar_brg_jual_add">
                                <tr id="add_brg_jual_but_container">
                                  <td colspan=7><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="add_brg_jual_row()">Tambah Barang Penjualan</button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <div class="form-group col-lg-12">
                          <h5>Tambahan Penjualan</h5>
                          <div class = "table-responsive">
                            <table class="table table-striped table-bordered">
                              <thead>
                                <th>Tambahan</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Harga Final</th>
                                <th>Notes</th>
                                <th>Action</th>
                              </thead>
                              <tbody id="daftar_tambahan_jual_add">
                                <tr id="add_tambahan_jual_but_container">
                                  <td colspan=7><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="add_tambahan_jual_row()">Tambah Barang Penjualan</button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <div class="form-group col-lg-12">
                          <input type = "checkbox" name = "ppn_check[]" id = "ppn_check" onclick = "toggle_ppn()"> PPN
                        </div>
                        <div class="form-group col-lg-12">
                          <h5>Total Price</h5>
                          <input type="text" class="form-control" required readonly onclick="count_total_price()" id="total_price">
                        </div>
                        <div class="form-group col-lg-12">
                          <h5>Pembayaran Penjualan</h5>
                          <div class = "table-responsive">
                            <table class="table table-striped table-bordered">
                              <thead>
                                <th>Jenis Pembayaran</th>
                                <th>Metode Pembyaran</th>
                                <th>Jumlah</th>
                                <th>Notes</th>
                                <th>Status Bayar</th>
                                <th>Tanggal Bayar/Tanggal Jatuh Tempo</th>
                                <th>Action</th>
                              </thead>
                              <tbody id="daftar_pembayaran_add">
                                <tr id="add_pembayaran_but_container">
                                  <td colspan=7><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="add_pembayaran_row()">Tambah Pembayaran Penjualan</button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <div class="form-group col-lg-12" style="width:50%">
                          <button type="button" class="btn btn-sm btn-danger" onclick="close_window()">Cancel</button>
                          <button type="button" onclick="count_total_price();update_func();location.reload()" class="btn btn-sm btn-primary">Submit</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php $this->load->view('req/mm_js.php'); ?>
  </body>

</html>
<script>
  var ctrl = "penjualan";

  var brg_jual_row = 0;
  var tambahan_jual_row = 0;
  var pembayaran_row = 0;
  var custom_produk_row = 0;

  function add_brg_jual_row() {
    var html = `
      <tr class = 'add_brg_jual_row'>
        <td id = 'row${brg_jual_row}'>
          <input name = 'check[]' value = ${brg_jual_row} type = 'hidden'>
          <input type = 'text' list = 'datalist_barang_cabang_jualan' onchange = 'load_harga_barang(${brg_jual_row})' id = 'brg${brg_jual_row}' name = 'brg${brg_jual_row}' class = 'form-control'>
          <a href = '<?php echo base_url(); ?>toko/brg_cabang' class = 'btn btn-primary btn-sm col-lg-12' target = '_blank'>Tambah Barang Cabang</a>
        </td>
        <input name = 'brg_qty_real${brg_jual_row}' type = 'hidden' value = "0" class = 'form-control nf-input'>
        <td>
          <input name = 'brg_qty${brg_jual_row}' type = 'text' class = 'form-control nf-input'>
        </td>
        <td>
          <table>
            <tr>
              <td>Harga Satuan</td>
              <td style = "padding:0px 5px" id = 'harga_barang_jual${brg_jual_row}'></td>
            </tr>
            <tr>
              <td>Harga Toko</td>
              <td style = "padding:0px 5px" id = 'harga_barang_toko${brg_jual_row}'></td>
            </tr>
            <tr>
              <td>Harga Grosir</td>
              <td style = "padding:0px 5px" id = 'harga_barang_grosir${brg_jual_row}'></td>
            </tr>
          </table>
        </td>
        <td>
          <input type = 'text' name = 'brg_price${brg_jual_row}' id = 'brg_price${brg_jual_row}' class = 'form-control nf-input'>
        </td>
        <td>
          <input readonly type = 'text' class = 'form-control nf-input' id = 'harga_brg_final${brg_jual_row}'>
        </td>
        <td>
          <input type = 'text' name = 'brg_notes${brg_jual_row}' class = 'form-control'>
        </td>
        <td>
          <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
        </td>
      </tr>`;
    $("#add_brg_jual_but_container").before(html);
    init_nf();
    brg_jual_row++;
  }

  function add_tambahan_jual_row() {
    var html = `
        <tr class = 'add_tambahan_jual_row'>
            <td>
                <input name = 'tambahan[]' value = ${tambahan_jual_row} type = 'hidden'>
                <input name = 'tmbhn${tambahan_jual_row}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <input name = 'tmbhn_jumlah${tambahan_jual_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input name = 'tmbhn_harga${tambahan_jual_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input readonly type = 'text' class = 'form-control nf-input' id = 'harga_tambahan_final${tambahan_jual_row}'>
            </td>
            <td>
                <input name = 'tmbhn_notes${tambahan_jual_row}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
    $("#add_tambahan_jual_but_container").before(html);
    init_nf();
    tambahan_jual_row++;
  }

  function add_pembayaran_row() {
    var html = `
        <tr class = 'add_pembayaran_row'>
          <input name = 'pembayaran[]' value = ${pembayaran_row} type = 'hidden'>
          <td id = 'row${pembayaran_row}'>
            <select name = 'pmbyrn_nama${pembayaran_row}' class = 'form-control'>
              <option value = "Down Payment 1">Down Payment 1</option>
              <option value = "Down Payment 2">Down Payment 2</option>
              <option value = "Down Payment 3">Down Payment 3</option>
              <option value = "Full Payment">Full Payment</option>
              <option value = "Tempo">Tempo</option>
              <option value = "Keep">Keep</option>
            </select>
          </td>
          <td>
            <select name = 'pmbyrn_persen${pembayaran_row}' class = 'form-control'>
              <option value = "Cash">Cash</option>
              <option value = "Debit">Debit</option>
              <option value = "Transfer">Transfer</option>
              <option value = "Kartu Kredit">Kartu Kredit</option>
              <option value = "Tarik Tunai">Tarik Tunai</option>
            </select>
          </td>
          <td>
              <input type = 'text' name = 'pmbyrn_nominal${pembayaran_row}' class = 'form-control nf-input'>
          </td>
          <td>
              <input type = 'text' name = 'pmbyrn_notes${pembayaran_row}' class = 'form-control'>
          </td>
          <td>
              <select name = 'pmbyrn_status${pembayaran_row}' class = 'form-control'>
                  <option value = 'aktif'>LUNAS</option>
                  <option value = 'belum lunas'>BELUM LUNAS</option>
              </select>
          </td>
          <td>
              <input type = 'date' name = 'pmbyrn_dateline${pembayaran_row}' class = 'form-control'>
          </td>
          <td>
              <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
          </td>
        </tr>`;
    $("#add_pembayaran_but_container").before(html);
    init_nf();
    pembayaran_row++;
  }

  function add_custom_produk_row() {
    var html = `
        <tr class = 'add_custom_produk_row'>
            <td>
                <input name = 'custom[]' value = ${custom_produk_row} type = 'hidden'>
                <input name = 'custom_brg_awal${custom_produk_row}' list = 'datalist_barang_cabang_jualan' type = 'text' class = 'form-control'>
                <a href = '<?php echo base_url(); ?>toko/brg_cabang' class = 'btn btn-primary btn-sm' target = '_blank'>Tambah Barang Cabang</a>
            </td>
            <td>
                <input name = 'custom_brg_akhir${custom_produk_row}' list = 'datalist_barang_cabang_jualan' type = 'text' class = 'form-control'>
            </td>
            <td>
                <input name = 'custom_brg_qty${custom_produk_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
    $("#add_custom_produk_but_container").before(html);
    init_nf();
    custom_produk_row++;
  }

  function load_harga_barang(row) {
    var nama_barang = $("#brg" + row).val();
    var hrg_brg_dsr = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-baseprice");
    var hrg_brgtoko = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-hargatoko");
    var hrg_brggrosir = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-hargagrosir");
    var id_brg = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-idpkbarang");
    $("#harga_barang_jual" + row).text(`Rp. ${format_number(hrg_brg_dsr)}`);
    $("#harga_barang_toko" + row).text(`Rp. ${format_number(hrg_brgtoko)}`);
    $("#harga_barang_grosir" + row).text(`Rp. ${format_number(hrg_brggrosir)}`);

    $.ajax({
      url:`<?php echo base_url();?>ws/barang_cabang/get_latest_harga_jual/${id_brg}`,
      type:"GET",
      dataType:"JSON",
      success:function(respond){
        $("#brg_price" + row).val(`${format_number(respond["data"][0]["last_item_price"])}`);
        init_nf();
      }
    });
  }

  var total_price_global = 0;
  function count_total_price() {
    var total = 0;
    for (var a = 0; a < brg_jual_row; a++) {
      var qty = deformatting_func($("input[name='brg_qty" + a + "']").val().split(" ")[0]);
      var price = deformatting_func($("input[name='brg_price" + a + "']").val().split(" ")[0]);
      if (typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty) {
        $("#harga_brg_final" + a).val("0");
        total += 0;
      } else {
        var count_result = Math.round(parseFloat(qty.split(" ")[0]) * parseInt(price));
        $("#harga_brg_final" + a).val(formatting_func(count_result));
        total += count_result;
      }
    }
    for (var a = 0; a < tambahan_jual_row; a++) {
      var qty = deformatting_func($("input[name='tmbhn_jumlah" + a + "'").val().split(" ")[0]);
      var price = deformatting_func($("input[name='tmbhn_harga" + a + "'").val().split(" ")[0]);
      if (typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty) {
        $("#harga_tambahan_final" + a).val("0");
        total += 0;
      } else {
        var count_result = Math.round(parseFloat(qty.split(" ")[0]) * parseInt(price));
        $("#harga_tambahan_final" + a).val(formatting_func(count_result));
        total += count_result;
      }
    }
    $("#total_price").val(formatting_func(total));
    total_price_global = total;

    if($('input[type=checkbox]').prop('checked')){
      $("#total_price").val(formatting_func(parseInt(total*1.1,10)));
    }
    else{
      $("#total_price").val(formatting_func(total));
    }
  }
  
  function open_online_container() {
    $('#online_info_container').show();
    $('#marketplace').html(html_option_marketplace);
  }

  function toggle_nomor_penjualan() {
    if ($("#penomoran_otomatis_cb").prop("checked")) {
      $("#penomoran_otomatis_cb").prop("checked", true);
      $("#nomor").prop("readonly", true);
      $("#nomor").val("-");
    } else {
      $("#penomoran_otomatis_cb").prop("checked", false);
      $("#nomor").prop("readonly", false);
      $("#nomor").val("");
    }
  }

  function toggle_ppn(){
    if(total_price_global == 0){
      count_total_price();
    }
    if($('input[type=checkbox]'). prop('checked')){
      $("#total_price").val(formatting_func(parseInt(total_price_global*1.1,10)));
    }
    else{
      $("#total_price").val(formatting_func(total_price_global));
    }
  }
</script>
<?php $this->load->view("_base_element/datalist_customer_toko"); ?>
<?php $this->load->view("_base_element/datalist_barang_cabang_jualan"); ?>
<?php $this->load->view("_base_element/datalist_marketplace"); ?>
<script>
  load_datalist();

  function load_datalist() {
    load_datalist_customer();
    // load_datalist_customer_toko();
    load_datalist_barang_cabang_jualan();
    load_datalist_marketplace();
  }
</script>
<script>
  function close_window() {
    if (confirm("Close Window?")) {
      close();
    }
  }
</script>
<div class="modal fade" id="custom_produk_modal">
  <div class="modal-dialog modal-center">
    <div class="modal-content">
      <div class="modal-header">
        <h4>Custom Produk</h4>
      </div>
      <div class="modal-body">
        <form method="POST" id="register_brg_pindah_form">
          <table class="table table-striped table-bordered">
            <thead>
              <th>Produk Asal</th>
              <th>Produk Custom</th>
              <th>Qty (Pcs)</th>
              <th>Action</th>
            </thead>
            <tbody id="daftar_custom_produk_add">
              <tr id="add_custom_produk_but_container">
                <td colspan=6><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="add_custom_produk_row()">Tambah Barang Penjualan</button>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="form-group">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="register_brg_pindah()" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  var brg_custom_base_html = $("#daftar_custom_produk_add").html();

  function register_brg_pindah() {
    nf_reformat_all();
    var form = $("#register_brg_pindah_form")[0];
    var data = new FormData(form);
    $.ajax({
      url: "<?php echo base_url(); ?>ws/barang_pindah/register?sumber=penjualan",
      type: "POST",
      dataType: "JSON",
      data: data,
      processData: false,
      contentType: false,
      success: function(respond) {
        html = "";
        if (respond["content"]) {
          for (var a = 0; a < respond["content"].length; a++) {
            html += `
                        <tr>
                            <td>
                                <input type = 'hidden' name = 'brg_custom[]' value = '${a}'>
                                <input type = 'hidden' name = 'id_brg_custom${a}' value = '${respond["content"][a]["id_brg_pindah"]}'>
                                ${respond["content"][a]["nama_brg_awal"]}
                            </td>
                            <td>${respond["content"][a]["nama_brg_akhir"]}</td>
                            <td>${respond["content"][a]["qty"]}</td>
                        </tr>`;
          }
        }
        $("#daftar_brg_custom_container").append(html);
        $("#daftar_custom_produk_add").html(brg_custom_base_html);
        $("#custom_produk_modal").modal("hide");
      },
      error: function() {}
    });
  }
</script>
<?php $this->load->view('penjualan/f-add-customer-toko'); ?>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("_core_script/menubar_func"); ?>
<?php $this->load->view("req/core_script"); ?>
<script>
var content = <?php echo json_encode($content);?>;
load_edit_content();
function load_edit_content(){
  $("#id_pk_penjualan").val(content["detail"][0]["id_pk_penjualan"]);
  $("#no_penjualan").val(content["detail"][0]["penj_nomor"]);
  $("#customer").val(content["detail"][0]["cust_perusahaan"]);
  $("#tgl").val(content["detail"][0]["penj_tgl"].split(" ")[0]);
  $("#dateline").val(content["detail"][0]["penj_dateline_tgl"].split(" ")[0]);
  $("#no_resi").val(content["detail"][0]["penj_on_no_resi"]);
  $("#kurir").val(content["detail"][0]["penj_on_kurir"]);
  if(content["detail"][0]["penj_jenis"].toLowerCase() == "online"){
    $("input[type='radio'][value='ONLINE']").attr("checked","checked");
    open_online_container();
    $('#marketplace').val(content["detail"][0]["penj_on_marketplace"]);
  }

  if(content["detail"][0]["penj_tipe_pembayaran"] == 1){
    $("#ppn_check").attr("checked","checked");
  }
  $("#total_price").val(format_number(content["detail"][0]["penj_nominal"]));
  for(var a = 0; a<content["brg_custom"].length; a++){

  }
  var html = "";
  for(var a = 0; a<content["item"].length; a++){
    html += `
      <tr class = 'add_brg_jual_row'>
        <input name = 'edit_check[]' value = ${brg_jual_row} type = 'hidden'>
        <input type = "hidden" value = "${content["item"][a]["id_pk_brg_penjualan"]}" name = "id_pk_brg_penjualan${brg_jual_row}">
        <td id = 'row${brg_jual_row}'>
          <input type = 'text' list = 'datalist_barang_cabang_jualan' onchange = 'load_harga_barang(${brg_jual_row})' id = 'brg${brg_jual_row}' name = 'brg${brg_jual_row}' class = 'form-control' value = "${content["item"][a]["brg_nama"]}">
          <a href = '<?php echo base_url(); ?>toko/brg_cabang' class = 'btn btn-primary btn-sm col-lg-12' target = '_blank'>Tambah Barang Cabang</a>
        </td>
        <input name = 'brg_qty_real${brg_jual_row}' type = 'hidden' value = "0" class = 'form-control nf-input'>
        <td>
          <input name = 'brg_qty${brg_jual_row}' type = 'text' value = "${format_number(content["item"][a]["brg_penjualan_qty"])} ${content["item"][a]["brg_penjualan_satuan"]}" class = 'form-control nf-input'>
        </td>
        <td>
          <table>
            <tr>
              <td>Harga Satuan</td>
              <td style = "padding:0px 5px" id = 'harga_barang_jual${brg_jual_row}'>${format_number(content["item"][a]["brg_harga"])}</td>
            </tr>
            <tr>
              <td>Harga Toko</td>
              <td style = "padding:0px 5px" id = 'harga_barang_toko${brg_jual_row}'>${format_number(content["item"][a]["brg_harga_toko"])}</td>
            </tr>
            <tr>
              <td>Harga Grosir</td>
              <td style = "padding:0px 5px" id = 'harga_barang_grosir${brg_jual_row}'>${format_number(content["item"][a]["brg_harga_grosir"])}</td>
            </tr>
          </table>
        </td>
        <td>
          <input type = 'text' name = 'brg_price${brg_jual_row}' id = 'brg_price${brg_jual_row}' value = "${format_number(content["item"][a]["brg_penjualan_harga"])}" class = 'form-control nf-input'>
        </td>
        <td>
          <input readonly type = 'text' class = 'form-control nf-input' id = 'harga_brg_final${brg_jual_row}' value = "${format_number(parseInt(content["item"][a]["brg_penjualan_qty"] * content["item"][a]["brg_penjualan_harga"],10))}">
        </td>
        <td>
          <input type = 'text' name = 'brg_notes${brg_jual_row}' class = 'form-control' value = "${content["item"][a]["brg_penjualan_note"]}">
        </td>
        <td>
          <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_brg_penjualan()'></i>
        </td>
      </tr>`;
    brg_jual_row++;
  }
  $("#add_brg_jual_but_container").before(html);
  var html = "";
  for(var a = 0; a<content["tambahan"].length; a++){
    html += `
      <tr class = 'add_tambahan_jual_row'>
        <td>
          <input name = 'edit_tambahan[]' value = ${tambahan_jual_row} type = 'hidden'>
          <input type = "hidden" name = "id_pk_tmbhn${tambahan_jual_row}" value = "${content["tambahan"][a]["id_pk_tmbhn"]}">
          <input name = 'tmbhn${tambahan_jual_row}' type = 'text' class = 'form-control' value = "${content["tambahan"][a]["tmbhn"]}">
        </td>
        <td>
          <input name = 'tmbhn_jumlah${tambahan_jual_row}' type = 'text' class = 'form-control nf-input' value = "${format_number(content["tambahan"][a]["tmbhn_jumlah"])} ${format_number(content["tambahan"][a]["tmbhn_satuan"])}">
        </td>
        <td>
          <input name = 'tmbhn_harga${tambahan_jual_row}' type = 'text' class = 'form-control nf-input' value = "${format_number(content["tambahan"][a]["tmbhn_harga"])}">
        </td>
        <td>
          <input readonly type = 'text' class = 'form-control nf-input' id = 'harga_tambahan_final${tambahan_jual_row}' value = "${format_number(parseInt(content["tambahan"][a]["tmbhn_harga"] * content["tambahan"][a]["tmbhn_jumlah"],10))}">
        </td>
        <td>
          <input name = 'tmbhn_notes${tambahan_jual_row}' type = 'text' class = 'form-control' value = "${content["tambahan"][a]["tmbhn_notes"]}">
        </td>
        <td>
          <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_tambahan()'></i>
        </td>
      </tr>`;
    tambahan_jual_row++;
  }
  
  $("#add_tambahan_jual_but_container").before(html);
  var html = "";
  for(var a = 0; a<content["pembayaran"].length; a++){
    html = `
      <tr class = 'add_pembayaran_row'>
        <input name = 'edit_pembayaran[]' value = ${pembayaran_row} type = 'hidden'>
        <input type = "hidden" id = "id_pk_penjualan_pembayaran${pembayaran_row}" name = "id_pk_penjualan_pembayaran${pembayaran_row}">
        <td id = 'row${pembayaran_row}'>
          <select name = 'pmbyrn_nama${pembayaran_row}' id = 'pmbyrn_nama${pembayaran_row}' class = 'form-control'>
            <option value = "Down Payment 1">Down Payment 1</option>
            <option value = "Down Payment 2">Down Payment 2</option>
            <option value = "Down Payment 3">Down Payment 3</option>
            <option value = "Full Payment">Full Payment</option>
            <option value = "Tempo">Tempo</option>
            <option value = "Keep">Keep</option>
          </select>
        </td>
        <td>
          <select name = 'pmbyrn_persen${pembayaran_row}' id = 'pmbyrn_persen${pembayaran_row}' class = 'form-control'>
            <option value = "Cash">Cash</option>
            <option value = "Debit">Debit</option>
            <option value = "Transfer">Transfer</option>
            <option value = "Kartu Kredit">Kartu Kredit</option>
            <option value = "Tarik Tunai">Tarik Tunai</option>
          </select>
        </td>
        <td>
          <input type = 'text' name = 'pmbyrn_nominal${pembayaran_row}' id = 'pmbyrn_nominal${pembayaran_row}' class = 'form-control nf-input'>
        </td>
        <td>
          <input type = 'text' name = 'pmbyrn_notes${pembayaran_row}' id = 'pmbyrn_notes${pembayaran_row}' class = 'form-control'>
        </td>
        <td>
          <select name = 'pmbyrn_status${pembayaran_row}' id = 'pmbyrn_status${pembayaran_row}' class = 'form-control'>
            <option value = 'aktif'>LUNAS</option>
            <option value = 'belum lunas'>BELUM LUNAS</option>
          </select>
        </td>
        <td>
          <input type = 'date' name = 'pmbyrn_dateline${pembayaran_row}' id = 'pmbyrn_dateline${pembayaran_row}' class = 'form-control'>
        </td>
        <td>
          <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_pembayaran()'></i>
        </td>
      </tr>`;
    pembayaran_row++;
    $("#add_pembayaran_but_container").before(html); /*perlu dimasukin dulu untuk support value assignment dropdown*/

    $(`#id_pk_penjualan_pembayaran${a}`).val(content["pembayaran"][a]["id_pk_penjualan_pembayaran"]);
    $(`#pmbyrn_nama${a}`).val(content["pembayaran"][a]["penjualan_pmbyrn_nama"]);
    $(`#pmbyrn_persen${a}`).val(content["pembayaran"][a]["penjualan_pmbyrn_persen"]);
    $(`#pmbyrn_nominal${a}`).val(format_number(content["pembayaran"][a]["penjualan_pmbyrn_nominal"]));
    $(`#pmbyrn_notes${a}`).val(content["pembayaran"][a]["penjualan_pmbyrn_notes"]);
    $(`#pmbyrn_status${a}`).val(content["pembayaran"][a]["penjualan_pmbyrn_status"]);
    $(`#pmbyrn_dateline${a}`).val(content["pembayaran"][a]["penjualan_pmbyrn_dateline"].split(" ")[0]);
  }
  
}
</script>