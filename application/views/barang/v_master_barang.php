<?php
$page_title = "Master Barang";
$breadcrumb = array(
  "Master", "Barang"
);
$notif_data = array(
  "page_title" => $page_title
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php $this->load->view('req/mm_css.php'); ?>
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
  <style>
    #jenis_barang {
      overflow: hidden;
      display: flex;
      height: auto;
      width: 100%;
      margin-bottom: 30px;
    }

    .row {
      margin: 0 !important;
    }

    .btn-jenis-barang {
      cursor: pointer;
      margin: auto 10px;
      text-align: center;
      width: auto;
      height: auto;
    }

    /* .btn-jenis-barang:hover {
        background-color: lightgrey;
      } */

    .judul-jenis {
      margin-bottom: 30px;
    }

    .judul-jenis h2 {
      font-size: 20px;
      font-weight: bold;
      text-align: center;
      color: black;
    }
  </style>
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
                  <div class="row">
                    <input type="hidden" value="-" id="id_jeniss">
                    <div class="row" id="jenis_barang">
                      <?php for ($x = 0; $x < count($daftar_jenis_barang); $x++) : ?>
                        <?php if ($x == 0) : ?>
                          <div class="col-lg-12 btn-jenis-barang btn btn-sm btn-default" id="jenis_brg_<?php echo $daftar_jenis_barang[$x]['id_pk_brg_jenis'] ?>" onclick="daftar_jenis_barang(<?php echo $daftar_jenis_barang[$x]['id_pk_brg_jenis']; ?>)"><?php echo $daftar_jenis_barang[$x]["brg_jenis_nama"]; ?></div>
                        <?php else : ?>
                          <div class="col-lg-12 btn-jenis-barang btn btn-sm btn-warning" id="jenis_brg_<?php echo $daftar_jenis_barang[$x]['id_pk_brg_jenis'] ?>" onclick="daftar_jenis_barang(<?php echo $daftar_jenis_barang[$x]['id_pk_brg_jenis']; ?>)"><?php echo $daftar_jenis_barang[$x]["brg_jenis_nama"]; ?></div>
                        <?php endif; ?>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <br>
                  <div class="row judul-jenis">
                    <h2 style="color:black !important">Jenis Barang: <span id="tampil_barang"></span></h2>
                  </div>
                  <br>
                  <div class="col-lg-12">
                    <div class="d-flex">
                      <button id="tambah_jual" type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#register_modal" style="margin-bottom:10px;margin-right:10px">Tambah <?php echo ucwords($page_title); ?> Jual</button>
                    </div>

                    <br />
                    <br />
                    <div class="align-middle text-center d-block">
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-success md-eye"></i><b> - Details </b>
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-primary md-edit"></i><b> - Edit </b>
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-danger md-delete"></i><b> - Delete </b>
                    </div>
                    <br />
                    <?php
                    $data = array(
                      "ctrl_model" => "m_barang",
                      "excel_title" => "Daftar Barang"
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
</body>

</html>

<?php
$data = array(
  "page_title" => "Master Barang"
);
?>
<?php $this->load->view("barang/f-add-barang", $data); ?>
<?php $this->load->view("barang/f-update-barang", $data); ?>
<?php $this->load->view("barang/f-delete-barang", $data); ?>
<?php $this->load->view("barang/f-detail-barang", $data); ?>
<?php $this->load->view("_base_element/datalist_barang_jenis"); ?>
<?php $this->load->view("_base_element/datalist_barang_jenis_jualan"); ?>
<?php $this->load->view("_base_element/datalist_barang_merk"); ?>
<?php $this->load->view("_base_element/datalist_barang_nonkombinasi"); ?>
<?php $this->load->view("_base_element/datalist_satuan"); ?>
<?php $this->load->view('_notification/notif_general'); ?>
<!-- register,update,delte func -->
<?php $this->load->view("req/core_script"); ?>
<script>
  $("#tambah_jual").click(function() {
    $("#tambah_id_brg_jenis_btn").val("");
    $("#tambah_id_brg_jenis_btn").attr('list', "datalist_barang_jenis_jualan");
    $("#tambah_id_brg_jenis_btn").attr('readonly', false);
  });

  $("#tambah_kantor").click(function() {
    $("#tambah_id_brg_jenis_btn").val("BARANG KANTOR");
    $("#tambah_id_brg_jenis_btn").attr('readonly', true);
  });
</script>
<!-- untuk pembuatan table baru dengan fitur ini, silahkan copy dari sini sampe ..... -->
<script>
  var ctrl = "barang";
  var contentCtrl = "content_tab";
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
      url: "<?php echo base_url(); ?>ws/" + ctrl + "/" + contentCtrl + "?orderBy=" + orderBy + "&orderDirection=" + orderDirection + "&page=" + page + "&searchKey=" + searchKey + "&id_jenis=" + id_jenis + "&" + url_add,
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"] == "SUCCESS") {
          content = respond["content"];
          var html = "";
          for (var a = 0; a < respond["content"].length; a++) {
            var html_status = "";
            switch (respond["content"][a]["status"].toLowerCase()) {
              case "aktif":
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-success align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                break;
              case "konfirmasi":
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                break;
              case "selesai":
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                break;
              case "diterima":
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                break;
              default:
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                break;
            }
            html += `
              <tr>
                  <td>${respond["content"][a]["kode"]}</td>
                  <td>${respond["content"][a]["nama"]}</td>
                  <td>${respond["content"][a]["ket"]}</td>
                  <td>${respond["content"][a]["merk"]}</td>
                  <td>${respond["content"][a]["minimal"]}</td>
                  <td>${respond["content"][a]["satuan"]}</td>
                  <td>${respond["content"][a]["harga"]}</td>
                  <td>${respond["content"][a]["harga_toko"]}</td>
                  <td>${respond["content"][a]["harga_grosir"]}</td>
                  ${html_status}
                  <td>${respond["content"][a]["last_modified"]}</td>
                  <td>
                      <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'detail_button text-success md-eye' data-target = '#detail_modal' onclick = 'load_detail_content(${a})'></i>
                      <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content(${a})'></i>  
                      <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content(${a})'></i>
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
<!-- .... sampe sini. keep in mind, ubah URLnya sesuai kebutuhan-->

<script>
  function daftar_jenis_barang(id_jenis, nama_jenis) {
    $("#id_jeniss").val(id_jenis);
    $("#tampil_barang").html(nama_jenis);
    refresh();
  }

  $(".btn-jenis-barang").click(function() {
    $(".btn-jenis-barang").removeClass("btn btn-sm btn-default");
    $(".btn-jenis-barang").addClass("btn btn-sm btn-warning");
    $(this).removeClass("btn btn-sm btn-warning");
    $(this).addClass("btn btn-sm btn-default");
  });
</script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
  function myFunction(x) {
    if (x.matches) {
      $('#jenis_barang').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: false,
        autoplay: false,
        speed: 300,
        arrows: true,
        prevArrow: '<button type="button" class="slick-prev btn btn-sm btn-default"><i class = "md-arrow-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next btn btn-sm btn-default"><i class = "md-arrow-right"></i></button>'
      });
    } else if (y.matches) { // If media query matches
      $('#jenis_barang').slick({
        infinite: false,
        slidesToShow: 3,
        slidesToScroll: 3,
        dots: false,
        autoplay: false,
        speed: 300,
        arrows: true,
        prevArrow: '<button type="button" class="slick-prev btn btn-sm btn-default"><i class = "md-arrow-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next btn btn-sm btn-default"><i class = "md-arrow-right"></i></button>'
      });
    } else {
      $('#jenis_barang').slick({
        infinite: true,
        slidesToShow: 5,
        slidesToScroll: 3,
        arrows: true,
        prevArrow: '<button type="button" class="slick-prev btn btn-sm btn-default"><i class = "md-arrow-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next btn btn-sm btn-default"><i class = "md-arrow-right"></i></button>'
      });
    }
  }

  var x = window.matchMedia("(max-width: 767px)");
  var y = window.matchMedia("(min-width: 768px) and (max-width: 1024px)");
  myFunction(x); // Call listener function at run time
  x.addListener(myFunction); // Attach listener function on state changes
</script>