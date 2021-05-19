<?php
$page_title = "Penjualan";
$breadcrumb = array(
  "Penjualan"
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
                    <div class="form-inline">
                      <select class="form-control form-sm" id="tipe_pembayaran" style="width:20%">
                        <option value = "Down Payment 1">Down Payment 1</option>
                        <option value = "Down Payment 2">Down Payment 2</option>
                        <option value = "Down Payment 3">Down Payment 3</option>
                        <option value = "Full Payment">Full Payment</option>
                        <option value = "Tempo">Tempo</option>
                        <option value = "Keep">Keep</option>
                      </select>
                      <button type="button" onclick="redirect_tipe_pembayaran()" class="btn btn-primary btn-sm">Buka</button>
                    </div>
                    <br />
                    <div class="d-block">
                      <a target="_blank" href="<?php echo base_url(); ?>penjualan/tambah" class="btn btn-primary btn-sm col-lg-2 col-sm-12" style="margin-right:10px">Tambah <?php echo ucwords($page_title); ?></a>
                    </div>
                    <br />
                    <br />
                    <div class="align-middle text-center d-block">
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-success md-eye"></i><b> - Details </b>
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-primary md-edit"></i><b> - Edit </b>
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-danger md-delete"></i><b> - Delete </b>
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-info md-print"></i><b> - Invoice </b>
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-default md-print"></i><b> - Invoice Copy </b>
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-secondary md-check"></i><b> - Selesai </b>
                    </div>
                    <br />
                    <?php
                    $data = array(
                      "ctrl_model" => "m_penjualan",
                      "excel_title" => "Daftar Penjualan"
                    );
                    ?>
                    <?php $this->load->view("_base_element/table", $data); ?>
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




  <div class = "modal fade" id = "pdf_asli_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Download Invoice Asli</h4>
            </div>
            <div class = "modal-body">
                <h4>Pilih jenis invoice yang akan di download</h4>
                <input type='hidden' id="nomor_invoice_asli">
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-secondary" data-dismiss = "modal">Cancel</button>
                    <a><button type = "button" id = "pdf_invoice_asli_cap" onclick="download_pdf_asli('cap')" class = "btn btn-sm btn-primary">Cap</button></a>
                    <a><button onclick="download_pdf_asli('noncap')" type = "button" id = "pdf_invoice_asli_noncap" class = "btn btn-sm btn-warning">Non-Cap</button></a>
                </div>
            </div>
        </div>
    </div>
  </div>
</body>

</html>
<script>
  var ctrl = "penjualan";
  var tipe_pemb = $("#tipe_pembayaran").val();
  var url_add = "id_cabang=<?php echo $this->session->id_cabang; ?>";
  var tblHeaderCtrl = "columns";
  var colCount = 11; //ragu either 1/0
  var orderBy = 0;
  var orderDirection = "ASC";
  var searchKey = "";
  var page = 1;
</script>
<?php $this->load->view("_core_script/core"); ?>
<?php $this->load->view("penjualan/f-delete-penjualan"); ?>
<?php $this->load->view("penjualan/f-detail-penjualan"); ?>
<?php $this->load->view("penjualan/f-selesai-penjualan"); ?>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script"); ?>
<script>

  refresh(1);

  function refresh(req_page = 1) {
    page = req_page;
    var id_jenis = $("#id_jeniss").val();
    $.ajax({
      url: "<?php echo base_url(); ?>ws/penjualan/content?orderBy=" + orderBy + "&orderDirection=" + orderDirection + "&page=" + page + "&searchKey=" + searchKey + "&" + url_add,
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"] == "SUCCESS") {
          content = respond["content"];
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            var html_status = "";
            switch (respond["content"][a]["penj_status"].toLowerCase()) {
              case "aktif":
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-success align-top">${respond["content"][a]["penj_status"].toUpperCase()}</span></td>`;
                break;
              default:
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top">${respond["content"][a]["penj_status"].toUpperCase()}</span></td>`;
                break;
            }
            var html_status_pembayaran = "";
            switch (respond["content"][a]["status_pembayaran"].toLowerCase()) {
              case "lunas":
                html_status_pembayaran += `<td class = 'align-middle text-center'><span class="badge badge-success align-top">${respond["content"][a]["status_pembayaran"].toUpperCase()}</span></td>`;
                break;
              case "lebih bayar":
                html_status_pembayaran += `<td class = 'align-middle text-center'><span class="badge badge-light align-top">${respond["content"][a]["status_pembayaran"].toUpperCase()}</span></td>`;
                break;
              default:
                html_status_pembayaran += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top">${respond["content"][a]["status_pembayaran"].toUpperCase()}</span></td>`;
                break;
            }
            var html_durasi_pembayaran = "";
            if(respond["content"][a]["status_pembayaran"].toLowerCase() != "belum lunas" ||  respond["content"][a]["penj_status"] == "selesai"){
              html_durasi_pembayaran += `<td class = 'align-middle text-center'>-</td>`;
            } 
            else{
              if(respond["content"][a]["selisih_tanggal"] > 0) {
                html_durasi_pembayaran += `<td class = 'align-middle text-center'><span class="badge badge-success align-top">${Math.abs(respond["content"][a]["selisih_tanggal"])} Hari </span></td>`;
              }
              else{
                html_durasi_pembayaran += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top">${Math.abs(respond["content"][a]["selisih_tanggal"])} Hari </span></td>`;
              }
            }
            html += `
              <tr>
                  <td>${respond["content"][a]["penj_nomor"]}</td>
                  <td>${format_number(respond["content"][a]["penj_nominal"])}</td>
                  <td>${respond["content"][a]["penj_tgl"]}</td>
                  <td>${respond["content"][a]["cust_perusahaan"]}</td>
                  <td>${respond["content"][a]["penj_jenis"]}</td>
                  ${html_status}
                  ${html_status_pembayaran}
                  ${html_durasi_pembayaran}
                  <td>
                    <a style = "cursor:pointer;font-size:large" class = 'text-success md-eye' data-toggle = 'modal' data-target = '#detail_modal' onclick = 'load_detail_content(${a})'></a>
                    <a style = "font-size:large" class = 'text-primary md-edit' href = "<?php echo base_url();?>penjualan/update/${respond["content"][a]["id_pk_penjualan"]}" target = "_blank"></a>  
                    <a style = "cursor:pointer;font-size:large" class = 'text-danger md-delete' data-toggle = 'modal' data-target = '#delete_modal' onclick = 'load_delete_content(${a})'></a>
                    
                    <a style="cursor:pointer;font-size:large" data-toggle = 'modal' data-target = '#pdf_asli_modal' onclick = 'load_pdf_asli_modal(${respond["content"][a]["id_pk_penjualan"]})' class="text-info md-print"></a>
                    <a style="cursor:pointer;font-size:large" href = "<?php echo base_url(); ?>pdf/invoice/copy/${respond["content"][a]["id_pk_penjualan"]}" class="text-default md-print"></a>
                    <a style="cursor:pointer;font-size:large" data-toggle = "modal" data-target = "#selesai_modal" class="text-secondary md-check"></a>
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
<script>

  function load_pdf_asli_modal(id){
    $("#nomor_invoice_asli").val(id);
  }

  function download_pdf_asli(status_cap){
    var id_penj = $("#nomor_invoice_asli").val();
    window.location.href = "<?= base_url() ?>penjualan/view_invoice_asli/"+id_penj+"/"+status_cap;
  }

  var unautorized_button = ["edit_button"];
  var additional_button = [{
      style: 'cursor:pointer;font-size:large',
      class: 'text-primary md-edit',
      onclick: 'redirect_edit_penjualan()'
    },
    {
      style: 'cursor:pointer;font-size:large',
      class: 'text-info md-print',
      onclick: 'redirect_print_pdf()'
    }, {
      style: 'cursor:pointer;font-size:large',
      class: 'text-copy md-print',
      onclick: 'redirect_print_pdf_copy()'
    },
    {
      style: 'cursor:pointer;font-size:large',
      class: 'text-secondary md-check',
      onclick: 'open_selesai_modal()'
    },
    {
      style: 'cursor:pointer;font-size:large',
      class: 'text-warning md-email',
      onclick: 'redirect_mailto()'
    }
  ];
</script>
<?php
$data = array(
  "page_title" => "Penjualan"
);
?>

<script>
  function redirect_tipe_pembayaran() {
    var tipe_pemb = $("#tipe_pembayaran").val();
    url_add = "id_cabang=<?php echo $this->session->id_cabang; ?>&tipe_pemb=" + tipe_pemb;
    refresh(page);
  }
</script>