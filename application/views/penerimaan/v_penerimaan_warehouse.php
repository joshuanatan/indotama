<?php
$page_title = "Penerimaan";
$breadcrumb = array(
  "Penerimaan"
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
<script>
  var ctrl = "penerimaan";
  var url_add = "type=<?php echo $type; ?>";
</script>
<script>
  function redirect_tipe_penerimaan() {
    var tipe_penerimaan = $("#tipe_penerimaan").val();
    window.location.href = "<?php echo base_url(); ?>penerimaan/" + tipe_penerimaan;
  }
</script>
<script>
  var colCount = 1; //ragu either 1/0
  var orderBy = 0;
  var orderDirection = "ASC";
  var searchKey = "";
  var page = 1;
  var content = [];
  var tblHeaderCtrl = "columns";
  var contentCtrl = "content";
  
  function tblheader() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/" + ctrl + "/" + tblHeaderCtrl,
      type: "GET",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        var html = "";
        if (respond["status"].toUpperCase() == "SUCCESS") {
          colCount = respond["content"].length + 1; //sama col action
          html += "<tr>";
          for (var a = 0; a < respond["content"].length; a++) {
            if (a == 0) {
              html += `
                <th id = 'col${a}' style = 'cursor:pointer' onclick = 'sort(${a})' class = 'text-center align-middle'>
                  ${respond["content"][a]["col_name"]} <span class='badge badge-primary align-top' id = 'orderDirection'>A-Z</span>
                </th>`;
            } else {
              html += `
                <th id = 'col${a}' style = 'cursor:pointer' onclick = 'sort(${a})' class = 'text-center align-middle'>
                  ${respond["content"][a]["col_name"]}
                </th>`;
            }
          }
          html += "<th class = 'text-center align-middle action_column'>Action</th>";
          html += "</tr>";
        } else {
          html += "<tr>";
          html += "<th class = 'align-middle text-center'>Columns is not defined</th>";
          html += "</tr>";
        }
        $("#col_title_container").html(html);
      },
      error: function() {
        var html = "<tr>";
        html += "<th class = 'align-middle text-center'>Columns is not defined</th>";
        html += "</tr>";
        $("#col_title_container").html(html);
      }
    });
  }

  function refresh(req_page = 1) {
    page = req_page;
    $.ajax({
      url: "<?php echo base_url(); ?>ws/" + ctrl + "/" + contentCtrl + "?orderBy=" + orderBy + "&orderDirection=" + orderDirection + "&page=" + page + "&searchKey=" + searchKey + "&" + url_add,
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        var html = "";
        if (respond["status"].toUpperCase() == "SUCCESS") {
          content = respond["content"];
          for (var a = 0; a < respond["content"].length; a++) {
            html += "<tr>";
            for (var b = 0; b < respond["key"].length; b++) {
              if (respond["content"][a][respond["key"][b]] == null) {
                respond["content"][a][respond["key"][b]] = "";
              }
              if (respond["key"][b].toLowerCase() == "status") {
                switch (respond["content"][a]["status"].toLowerCase()) {
                  case "aktif":
                    html += `<td class = 'align-middle text-center'><span class="badge badge-success align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                    break;
                  case "konfirmasi":
                    html += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                    break;
                  case "selesai":
                    html += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                    break;
                  case "diterima":
                    html += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                    break;
                  default:
                    html += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                    break;
                }
              } else {
                html += "<td class = 'align-middle text-center'>" + respond["content"][a][respond["key"][b]] + "</td>";
              }
            }
            html += `
              <td class = 'align-middle text-center action_column'>
                <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'detail_button text-success md-eye' data-target = '#detail_modal' onclick = 'load_detail_content(${a})'></i> 
                <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'edit_button text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content(${a})'></i>  
                <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content(${a})'></i>
              </td>`;
            html += "</tr>";
          }
        } else {
          html += "<tr>";
          html += "<td colspan = " + colCount + " class = 'align-middle text-center'>No Records Found</td>";
          html += "</tr>";
        }
        $(".content_container:eq(0)").html(html);
        pagination(respond["page"]);
      },
      error: function() {
        var html = "";
        html += "<tr>";
        html += "<td colspan = " + colCount + " class = 'align-middle text-center'>No Records Found</td>";
        html += "</tr>";

        $(".content_container:eq(0)").html(html);

        html = "";
        html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
        html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
        $(".pagination_container").html(html);
      }
    });

    function pagination(page_rules) {
      html = "";
      if (page_rules["previous"]) {
        html += '<li class="page-item"><a class="page-link" onclick = "refresh(' + (page_rules["before"]) + ')"><</a></li>';
      } else {
        html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
      }
      if (page_rules["first"]) {
        html += '<li class="page-item"><a class="page-link" onclick = "refresh(' + (page_rules["first"]) + ')">' + (page_rules["first"]) + '</a></li>';
        html += '<li class="page-item"><a class="page-link">...</a></li>';
      }
      if (page_rules["before"]) {
        html += '<li class="page-item"><a class="page-link" onclick = "refresh(' + (page_rules["before"]) + ')">' + page_rules["before"] + '</a></li>';
      }
      html += '<li class="page-item active"><a class="page-link" onclick = "refresh(' + (page_rules["current"]) + ')">' + page_rules["current"] + '</a></li>';
      if (page_rules["after"]) {
        html += '<li class="page-item"><a class="page-link" onclick = "refresh(' + (page_rules["after"]) + ')">' + page_rules["after"] + '</a></li>';
      }
      if (page_rules["last"]) {
        html += '<li class="page-item"><a class="page-link">...</a></li>';
        html += '<li class="page-item"><a class="page-link" onclick = "refresh(' + (page_rules["last"]) + ')">' + page_rules["last"] + '</a></li>';
      }
      if (page_rules["next"]) {
        html += '<li class="page-item"><a class="page-link" onclick = "refresh(' + (page_rules["after"]) + ')">></a></li>';
      } else {
        html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
      }
      $(".pagination_container").html(html);
    }
  }

  function sort(colNum) {
    if (parseInt(colNum) != orderBy) {
      orderBy = colNum;
      orderDirection = "ASC";
      var orderDirectionHtml = ' <span class="badge badge-primary align-top" id = "orderDirection">A-Z</span>';
      $("#orderDirection").remove();
      $("#col" + colNum).append(orderDirectionHtml);
    } else {
      var direction = $("#orderDirection").text();
      if (direction == "A-Z") {
        orderDirection = "DESC";
        orderDirectionHtml = "Z-A";
      } else {
        orderDirection = "ASC";
        orderDirectionHtml = "A-Z";
      }
      $("#orderDirection").text(orderDirectionHtml);
    }
    refresh();
  }

  function search() {
    searchKey = $("#search_box").val();
    refresh();
  }

  tblheader();
  refresh();
</script>
<?php
$data = array(
  "page_title" => "Penerimaan"
);
?>
<?php $this->load->view("_core_script/menubar_func"); ?>
<?php $this->load->view("penerimaan/f-add-penerimaan", $data); ?>
<?php $this->load->view("penerimaan/f-update-penerimaan", $data); ?>
<?php $this->load->view("penerimaan/f-detail-penerimaan", $data); ?>
<?php $this->load->view("penerimaan/f-delete-penerimaan", $data); ?>

<?php $this->load->view("_base_element/datalist_pembelian_toko"); ?>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script"); ?>