<?php
$page_title = "Cabang";
$breadcrumb = array(
  "Daftar Cabang"
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
                    <div class="align-middle text-center d-block">
                      <i style="cursor:pointer;font-size:large;margin-left:10px" class="text-dark md-wrench"></i><b> - Aktivasi Cabang untuk Manajemen </b>
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
  var ctrl = "cabang";
  var url_add = "";
  var custom_contentCtrl = "";
  var custom_tblHeaderCtrl = "columns_cabang_admin";
  var unautorized_button = ["detail_button", "edit_button", "delete_button"];
  var additional_button = [{
    class: "md-wrench",
    style: "cursor:pointer",
    onclick: "activate_cabang_manajemen()"
  }];
</script>
<?php $this->load->view("_core_script/table_func"); ?>
<script>
  function activate_cabang_manajemen() {
    var is_not_clicked = true;
    $("body table").find("tr").click(function() {
      var row = $(this).index();
      var id_cabang = content[row]["id"];
      $(this).find('.action_column').click(function() {
        $(this).find("i.md-wrench").click(function() {
          if (confirm(`Anda yakin ingin mengaktifkan cabang ${content[row]["daerah"]} pada toko ${content[row]["toko"]}?`)) {
            window.location.href = "<?php echo base_url(); ?>toko/activate_cabang_manajemen/" + id_cabang;

          }
        });
      });
    });
  }
</script>
<?php $this->load->view('_notification/notif_general'); ?>

<script>
  var colCount = 1; //ragu either 1/0
  var orderBy = 0;
  var orderDirection = "ASC";
  var searchKey = "";
  var page = 1;
  var content = [];

  /*custom get_content_function*/
  var contentCtrl = "content";
  if (typeof(custom_contentCtrl) != "undefined") {
    contentCtrl = custom_contentCtrl;
  }

  if (typeof(url_add) == "undefined") {
    url_add = "";
  }

  function refresh(req_page = 1) {
    if (typeof(ctrl) != "undefined") {
      page = req_page;
      $.ajax({
        url: "<?php echo base_url(); ?>ws/cabang/list_cabang_admin?orderBy=" + orderBy + "&orderDirection=" + orderDirection + "&page=" + page + "&searchKey=" + searchKey + "&" + url_add,
        type: "GET",
        dataType: "JSON",
        success: function(respond) {
          var html = "";
          if (respond["status"] == "SUCCESS") {
            content = respond["content"];
            for (var a = 0; a < respond["content"].length; a++) {
              switch (respond["content"][a]["status"].toLowerCase()) {
                case "aktif":
                  html_status = `<td class = 'align-middle text-center'><span class="badge badge-success align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                  break;
                case "konfirmasi":
                  html_status = `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                  break;
                case "selesai":
                  html_status = `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                  break;
                case "diterima":
                  html_status = `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                  break;
                default:
                  html_status = `<td class = 'align-middle text-center'><span class="badge badge-danger align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                  break;
              }
              html += `
                <tr>
                  <td class = 'align-middle text-center'>${respond["content"][a]["toko"]}</td>
                  <td class = 'align-middle text-center'>${respond["content"][a]["daerah"]}</td>
                  <td class = 'align-middle text-center'>${respond["content"][a]["notelp"]}</td>
                  <td class = 'align-middle text-center'>${respond["content"][a]["alamat"]}</td>
                  ${html_status}
                  <td class = 'align-middle text-center'>${respond["content"][a]["last_modified"]}</td>
                  <td class = 'align-middle text-center action_column'>
                    <a href = "<?php echo base_url(); ?>toko/activate_cabang_manajemen/${respond["content"][a]["id"]}"><i style = 'cursor:pointer;font-size:large' class = 'md-wrench'></i></a> 
                  </td>
                </tr>
                `;
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
  var tblHeaderCtrl = "columns";
  if (typeof(custom_tblHeaderCtrl) != "undefined") {
    tblHeaderCtrl = custom_tblHeaderCtrl;
  }

  function tblheader() {
    if (typeof(ctrl) != "undefined") {
      $.ajax({
        url: "<?php echo base_url(); ?>ws/" + ctrl + "/" + tblHeaderCtrl,
        type: "GET",
        dataType: "JSON",
        async: false,
        success: function(respond) {
          var html = "";
          if (respond["status"] == "SUCCESS") {
            colCount = respond["content"].length + 1; //sama col action
            html += "<tr>";
            for (var a = 0; a < respond["content"].length; a++) {
              html += "<th id = 'col" + a + "' style = 'cursor:pointer' onclick = 'sort(" + a + ")' class = 'text-center align-middle'>" + respond["content"][a]["col_name"];
              if (a == 0) {
                html += " <span class='badge badge-primary align-top' id = 'orderDirection'>A-Z</span>";
              }
              html += "</th>";
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
  }

  function menubar() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/menu/menubar",
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"] == "SUCCESS") {
          var menu_category = "";
          var html = "";
          for (var a = 0; a < respond["data"].length; a++) {
            if (menu_category != respond["data"][a]["menu_category"]) {
              if (html != "") {
                $("#" + menu_category.toLowerCase() + "_menu_separator").after(html);
              }
              html = "";
              menu_category = respond["data"][a]["menu_category"];
              $("." + menu_category.toLowerCase() + "_menu_item").remove();
              console.log("." + menu_category.toLowerCase() + "_menu_item");
            }
            /* Tambahin background color di menu item, dan icon */
            html += `
                        <li class = '${menu_category.toLowerCase()}_menu_item' style = "background-color:rgba(3, 0, 46, 0.2);;">
                            <a href="<?php echo base_url(); ?>${respond["data"][a]["menu_name"]}">
                                <div class = 'pull-left'>
                                    <div class="pull-left">
                                        <i class="md-${respond["data"][a]["menu_icon"]} mr-20"></i>
                                        <span class="right-nav-text">${respond["data"][a]["menu_display"]}</span>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class = 'clearfix'></div>
                            </a>
                        </li>
                        `;
          }
          $("#" + menu_category.toLowerCase() + "_menu_separator").after(html);
        }
      }
    })
  }
  document.onreadystatechange = function() {
    if (document.readyState === 'complete') {
      tblheader();
      refresh();
      menubar();
      if (typeof(load_datalist) != "undefined") {
        load_datalist();
      }
    }
  }
  window.onfocus = function() {
    if (typeof(load_datalist) != "undefined") {
      load_datalist();
    }
    menubar();
  }
</script>