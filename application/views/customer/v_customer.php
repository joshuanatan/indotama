<?php
$page_title = "Master Customer";
$breadcrumb = array(
  "Master", "Customer"
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
                    <?php
                    $data = array(
                      "ctrl_model" => "m_customer",
                      "excel_title" => "Daftar Customer"
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
  "page_title" => "Master Customer"
);
?>
<?php $this->load->view('customer/f-add-customer', $data); ?>
<?php $this->load->view('customer/f-update-customer', $data); ?>
<?php $this->load->view('customer/f-delete-customer', $data); ?>

<?php $this->load->view('_base_element/datalist_toko', $data); ?>

<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script"); ?>

<script>
  var ctrl = "customer";
  var contentCtrl = "content";
  var tblHeaderCtrl = "columns";
  var colCount = 10; //ragu either 1/0
  var orderBy = 0;
  var orderDirection = "ASC";
  var searchKey = "";
  var page = 1;
  var url_add = "";

  refresh();
  load_datalist_toko();

  function refresh(req_page = 1) {
    page = req_page;
    $.ajax({
      url: "<?php echo base_url(); ?>ws/" + ctrl + "/" + contentCtrl + "?orderBy=" + orderBy + "&orderDirection=" + orderDirection + "&page=" + page + "&searchKey=" + searchKey + "&" + url_add,
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
                <td>${respond["content"][a]["name"]}</td>
                <td>${respond["content"][a]["perusahaan"]}</td>
                <td>${respond["content"][a]["email"]}</td>
                <td>${respond["content"][a]["telp"]}</td>
                <td>${respond["content"][a]["hp"]}</td>
                <td>${respond["content"][a]["alamat"]}</td>
                <td>${respond["content"][a]["keterangan"]}</td>
                ${html_status}
                <td>${respond["content"][a]["last_modified"]}</td>
                <td>
                    <a href = "<?php echo base_url();?>customer/detail/${respond["content"][a]["id"]}"><i style = 'cursor:pointer;font-size:large' class = 'detail_button text-success md-eye'></i></a>
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