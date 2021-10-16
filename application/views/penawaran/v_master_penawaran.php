<?php
$page_title = "Penawaran";
$breadcrumb = array(
  "Penawaran"
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
        <?php $this->load->view('_notification/update_success', $notif_data); ?>
        <?php $this->load->view('_notification/delete_success', $notif_data); ?>
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
                      <div class="d-block">
                        <button type="button" class="btn btn-primary btn-sm col-lg-2 col-sm-12" data-toggle="modal" data-target="#register_modal" style="margin-right:10px">Tambah <?php echo ucwords($page_title); ?></button>
                      </div>
                      <br />
                      <br />
                      <div class="align-middle text-center d-block">
                        <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-success md-eye"></i><b> - Details </b>
                        <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-primary md-edit"></i><b> - Edit </b>
                        <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-danger md-delete"></i><b> - Delete </b>
                      </div>
                      <br />
                      <?php $this->load->view("_base_element/table"); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php $this->load->view('req/mm_footer.php'); ?>
        </div>
      </div>
    </div>
    <?php $this->load->view('req/mm_js.php'); ?>
  </body>

</html>

<div class="modal fade" id="register_modal">
  <div class="modal-dialog modal-lg modal-center">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Tambah Data Penawaran</h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/register_error', $notif_data); ?>
        <form id="register_form">
          <div class="form-group">
            <h5>Customer</h5>
            <input type="text" class="form-control" required name="penawar" list="datalist_customer_toko">
          </div>
          <div class="form-group">
            <h5>No Penawaran</h5> Nomor Penawaran Terakhir: <i class = "last_penawaran_no"></i>
            <input type="text" class="form-control" required name="penawaran_no">
          </div>
          <div class="form-group">
            <h5>Tanggal Penawaran</h5>
            <input type="date" class="form-control" required name="tgl">
          </div>
          <div class="form-group">
            <h5>Subjek Penawaran</h5>
            <input list="datalist_barang_jenis" type="text" required name="subjek" class="form-control">
          </div>
          <div class="form-group">
            <h5>Content Penawaran</h5>
            <textarea class="form-control" required name="content"></textarea>
          </div>
          <div class="form-group">
            <h5>Notes Penawaran</h5>
            <textarea class="form-control" required name="notes"></textarea>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <th>Nama Barang</th>
                <th>Jumlah Barang</th>
                <th style="width:25%">Harga Terdaftar</th>
                <th>Harga Penawaran</th>
                <th>Notes</th>
                <th></th>
              </thead>
              <tbody>
                <tr id="btn_tambah_item_penawaran_container">
                  <td colspan="6"><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="$('#btn_tambah_item_penawaran_container').before(tambah_data_barang_penawaran_row());init_nf();">Tambah Data Barang Penawaran</button></td>
                </tr>
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
<div class="modal fade" id="update_modal">
  <div class="modal-dialog modal-lg modal-center">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Update Data Penawaran</h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/update_error', $notif_data); ?>
        <form id="update_form">
          <input type="hidden" name="id_pk_penawaran" id="id_pk_penawaran_edit">
          <div class="form-group">
            <h5>Customer</h5>
            <input type="text" class="form-control" required name="penawar" id="penawar_edit" list="datalist_customer_toko">
          </div>
          <div class="form-group">
            <h5>No Penawaran</h5> Nomor Penawaran Terakhir: <i class = "last_penawaran_no"></i>
            <input type="text" class="form-control" required name="penawaran_no" id="penawaran_no_edit">
          </div>
          <div class="form-group">
            <h5>Tanggal Penawaran</h5>
            <input type="date" class="form-control" required name="tgl" id="tgl_edit">
          </div>
          <div class="form-group">
            <h5>Subjek Penawaran</h5>
            <input list="datalist_barang_jenis" type="text" required name="subjek" id="subjek_edit" class="form-control">
          </div>
          <div class="form-group">
            <h5>Content Penawaran</h5>
            <textarea class="form-control" required name="content" id="content_edit"></textarea>
          </div>
          <div class="form-group">
            <h5>Notes Penawaran</h5>
            <textarea class="form-control" required name="notes" id="notes_edit"></textarea>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <th>Nama Barang</th>
                <th>Jumlah Barang</th>
                <th style="width:25%">Harga Terdaftar</th>
                <th>Harga Penawaran</th>
                <th>Notes</th>
                <th></th>
              </thead>
              <tbody>
                <tr id="btn_tambah_item_penawaran_container_edit">
                  <td colspan="6"><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="$('#btn_tambah_item_penawaran_container_edit').before(tambah_data_barang_penawaran_row());init_nf();">Tambah Data Barang Penawaran</button></td>
                </tr>
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
<div class="modal fade" id="delete_modal">
  <div class="modal-dialog modal-center">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Hapus Data <?php echo ucwords($page_title); ?></h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/delete_error', $notif_data); ?>
        <input type="hidden" id="id_delete" name="id">
        <h4 align="center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
        <table class="table table-bordered table-striped table-hover">
          <tbody>
            <tr>
              <td>Customer</td>
              <td id="penawar_delete"></td>
            </tr>
            <tr>
              <td>No Penawaran</td>
              <td id="penawaran_no_delete"></td>
            </tr>
            <tr>
              <td>Tanggal Penawaran</td>
              <td id="tgl_delete"></td>
            </tr>
            <tr>
              <td>Subjek Penawaran</td>
              <td id="subjek_delete"></td>
            </tr>
            <tr>
              <td>Content Penawaran</td>
              <td id="content_delete"></td>
            </tr>
            <tr>
              <td>Notes Penawaran</td>
              <td id="notes_delete"></td>
            </tr>
          </tbody>
        </table>
        <div class="form-group">
          <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal">Cancel</button>
          <button type="button" onclick="delete_func()" class="btn btn-sm btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  var ctrl = "penawaran";
  var tblHeaderCtrl = "columns";
  var colCount = 11; //ragu either 1/0
  var orderBy = 0;
  var orderDirection = "ASC";
  var searchKey = "";
  var page = 1;
  var url_add = "";

  refresh(1);
  function refresh(req_page = 1) {
    page = req_page;
    var id_jenis = $("#id_jeniss").val();
    $.ajax({
      url: "<?php echo base_url(); ?>ws/" + ctrl + "/content?orderBy=" + orderBy + "&orderDirection=" + orderDirection + "&page=" + page + "&searchKey=" + searchKey + "&id_jenis=" + id_jenis + "&" + url_add,
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"].toUpperCase() == "SUCCESS") {
          $(".last_penawaran_no").html(respond["last_penawaran_no"]);
          content = respond["content"];
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            var html_status = "";
            switch (respond["content"][a]["penawaran_status"].toLowerCase()) {
              case "aktif":
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-success align-top" id = "orderDirection">${respond["content"][a]["penawaran_status"].toUpperCase()}</span></td>`;
                break;
              default:
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top" id = "orderDirection">${respond["content"][a]["penawaran_status"].toUpperCase()}</span></td>`;
                break;
            }
            html += `
              <tr>
                <td class = "text-center">${respond["content"][a]["cust_perusahaan"]}</td>
                <td class = "text-center">${respond["content"][a]["penawaran_no"]}</td>
                <td class = "text-center">${respond["content"][a]["penawaran_subject"]}</td>
                <td>${respond["content"][a]["penawaran_content"]}</td>
                <td>${respond["content"][a]["penawaran_notes"]}</td>
                <td class = "text-center">${respond["content"][a]["penawaran_tgl"].split(" ")[0]}</td>
                ${html_status}
                <td class = "text-center">
                  <a style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content(${a})'></a>  
                  <a style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content(${a})'></a>
                  <a target = "_blank" style = 'cursor:pointer;font-size:large' class="text-default md-print" href = "<?php echo base_url();?>penawaran/pdf/${respond["content"][a]["id_pk_penawaran"]}"></a>
                </td>
              </tr>
          `;
          }
        } else {
          html += "<tr>";
          html += "<td colspan = " + colCount + " class = 'align-middle text-center'>No Records Found</td>";
          html += "</tr>";
        }
        $("#content_container").html(html);

        pagination(respond["page"]);
      },
      error: function() {
        var html = "";
        html += "<tr>";
        html += "<td colspan = " + colCount + " class = 'align-middle text-center'>No Records Found</td>";
        html += "</tr>";

        $("#content_container").html(html);

        html = "";
        html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
        html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
        $("#pagination_container").html(html);
      }
    });
  }
</script>
<?php $this->load->view("_core_script/core"); ?>
<?php $this->load->view("_base_element/datalist_barang_cabang_jualan"); ?>
<?php $this->load->view("_base_element/datalist_customer_toko"); ?>

<script>
  function tambah_data_barang_penawaran_row() {
    var counter = $(".data_barang_penawaran_row").length;
    var html = `
      <tr class = "data_barang_penawaran_row" id = "data_barang_penawaran_row${counter}">
        <input type = "hidden" name = "check[]" value = "${counter}">
        <td><input type = "text" class = "form-control" name = "nama_barang${counter}" list = "datalist_barang_cabang_jualan" onchange = 'load_harga_barang(${counter})' id = 'nama_barang${counter}'></td>
        <td><input type = "text" class = "form-control nf-input" name = "jumlah_barang${counter}"></td>
        <td>
          <table>
            <tr>
              <td>Harga Satuan</td>
              <td style = "padding:0px 5px" id = 'harga_barang_jual${counter}'></td>
            </tr>
            <tr>
              <td>Harga Toko</td>
              <td style = "padding:0px 5px" id = 'harga_barang_toko${counter}'></td>
            </tr>
            <tr>
              <td>Harga Grosir</td>
              <td style = "padding:0px 5px" id = 'harga_barang_grosir${counter}'></td>
            </tr>
          </table>
        </td>
        <td><input type = "text" class = "form-control nf-input" name = "harga_barang${counter}"></td>
        <td><textarea class = "form-control" name = "notes_barang${counter}"></textarea></td>
        <td><i style = 'cursor:pointer;font-size:large;' class = 'text-danger md-delete' onclick = 'delete_brg_penawaran_row(${counter})'></i></td>
      </tr>
    `;
    return html;
  }

  function load_harga_barang(row) {
    var nama_barang = $("#nama_barang" + row).val();
    var hrg_brg_dsr = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-baseprice");
    var hrg_brgtoko = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-hargatoko");
    var hrg_brggrosir = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-hargagrosir");
    var id_brg = $("#datalist_barang_cabang_jualan option[value='" + nama_barang + "']").attr("data-idpkbarang");
    $("#harga_barang_jual" + row).text(`Rp. ${format_number(hrg_brg_dsr)}`);
    $("#harga_barang_toko" + row).text(`Rp. ${format_number(hrg_brgtoko)}`);
    $("#harga_barang_grosir" + row).text(`Rp. ${format_number(hrg_brggrosir)}`);
  }

  function load_edit_content(row) {
    $("#id_pk_penawaran_edit").val(content[row]["id_pk_penawaran"]);
    $("#penawaran_no_edit").val(content[row]["penawaran_no"]);
    $("#penawar_edit").val(content[row]["cust_perusahaan"]);
    $("#tgl_edit").val(content[row]["penawaran_tgl"].split(" ")[0]);
    $("#subjek_edit").val(content[row]["penawaran_subject"]);
    $("#content_edit").val(content[row]["penawaran_content"]);
    $("#notes_edit").val(content[row]["penawaran_notes"]);

    $.ajax({
      url: `<?php echo base_url(); ?>ws/penawaran/brg_penawaran/${content[row]["id_pk_penawaran"]}`,
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"]) {
          $(".data_barang_penawaran_row").remove();
          var html = "";
          for (var a = 0; a < respond["data"].length; a++) {
            html += `
              <tr class = "data_barang_penawaran_row" id = "data_barang_penawaran_row${a}">
                <input type = "hidden" name = "edit_check[]" value = "${a}">
                <input type = "hidden" name = "id_pk_brg_penawaran${a}" id = "id_pk_brg_penawaran${a}" value = "${respond["data"][a]["id_pk_brg_penawaran"]}">
                <td><input type = "text" name = "nama_barang${a}" id = "nama_barang${a}" class = "form-control" value = "${respond["data"][a]["brg_nama"]}" list = "datalist_barang_cabang_jualan"></td>
                <td><input type = "text" name = "jumlah_barang${a}" class = "form-control nf-input" value = "${format_number(respond["data"][a]["brg_penawaran_qty"])} ${respond["data"][a]["brg_penawaran_satuan"]}"></td>
                <td>
                  <table>
                    <tr>
                      <td>Harga Satuan</td>
                      <td style = "padding:0px 5px" id = 'harga_barang_jual${a}'>${format_number(respond["data"][a]["brg_harga"])}</td>
                    </tr>
                    <tr>
                      <td>Harga Toko</td>
                      <td style = "padding:0px 5px" id = 'harga_barang_toko${a}'>${format_number(respond["data"][a]["brg_harga_toko"])}</td>
                    </tr>
                    <tr>
                      <td>Harga Grosir</td>
                      <td style = "padding:0px 5px" id = 'harga_barang_grosir${a}'>${format_number(respond["data"][a]["brg_harga_grosir"])}</td>
                    </tr>
                  </table>
                </td>
                <td><input type = "text" name = "harga_barang${a}" class = "form-control nf-input" value = "${format_number(respond["data"][a]["brg_penawaran_price"])}"></td>
                <td><input type = "text" name = "notes_barang${a}" class = "form-control" value = "${respond["data"][a]["brg_penawaran_notes"]}"></td>
                <td><i style = 'cursor:pointer;font-size:large;' class = 'text-danger md-delete' onclick = 'delete_brg_penawaran(${a})'></i></td>
                </tr>
            `;
          }
          $("#btn_tambah_item_penawaran_container_edit").before(html);
          init_nf();
        }
        else{
          $(".data_barang_penawaran_row").remove();
        }
      }
    })
  }

  function load_delete_content(row) {
    $("#id_delete").val(content[row]["id_pk_penawaran"]);
    $("#penawar_delete").html(content[row]["cust_perusahaan"]);
    $("#penawaran_no_delete").html(content[row]["penawaran_no"]);
    $("#tgl_delete").html(content[row]["penawaran_tgl"].split(" ")[0]);
    $("#subjek_delete").html(content[row]["penawaran_subject"]);
    $("#content_delete").html(content[row]["penawaran_content"]);
    $("#notes_delete").html(content[row]["penawaran_notes"]);
  }
</script>
<script>
  function delete_brg_penawaran(row) {
    if (confirm(`Apakah yakin akan menghapus barang ${$("#nama_barang"+row).val()}?`)) {
      var id_brg_penawaran = $(`#id_pk_brg_penawaran${row}`).val();
      $.ajax({
        url: `<?php echo base_url(); ?>ws/penawaran/delete_brg_penawaran/${id_brg_penawaran}`,
        type: "DELETE",
        dataType: "JSON",
        success: function(respond) {
          if (respond["status"]) {
            alert("Data berhasil dihapus");
            delete_brg_penawaran_row(row);
          }
        }
      })
    }
  }

  function delete_brg_penawaran_row(row) {
    $("#data_barang_penawaran_row" + row).remove();
  }
</script>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script"); ?>