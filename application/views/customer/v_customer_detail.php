<?php
$page_title = "Master Customer";
$breadcrumb = array(
  "Master", "Customer", "Detail"
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
                    <br />
                    <div class="panel panel-default panel-tabs card-view">
                      <div class="panel-heading">
                        <div class="pull-left">
                          <h6 class="panel-title txt-dark">Detail Customer</h6>
                        </div>
                        <div class="pull-right">
                          <div class="tab-struct custom-tab-1">
                            <ul role="tablist" class="nav nav-tabs" id="myTabs_9">
                              <li onclick = "active_tab('penjualan')" class="active" role="presentation"><a data-toggle="tab" role="tab" id="home_tab_9" href="#penjualan">Penjualan</a></li>
                              <li onclick = "active_tab('barang')" role="presentation" class=""><a data-toggle="tab" id="profile_tab_9" role="tab" href="#barang" aria-expanded="false">Produk Penjualan</a></li>
                            </ul>
                          </div>
                        </div>

                        <div class="clearfix"></div>
                      </div>
                      <div class="panel-wrapper collapse in">
                        <div class="panel-body">
                          <div class="tab-content" id="myTabContent_9">
                            <div id="penjualan" class="tab-pane fade active in" role="tabpanel">
                              <iframe src = "<?php echo base_url();?>customer/table_penjualan_detail_customer/<?php echo $id_pk_customer;?>" style = "width:100%;height:400px"></iframe>
                            </div>
                            <div id="barang" class="tab-pane fade" role="tabpanel">
                              <iframe src = "<?php echo base_url();?>customer/table_brg_penjualan_detail_customer/<?php echo $id_pk_customer;?>" style = "width:100%;height:400px"></iframe>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
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

<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script"); ?>

<script>
  var active_tab = "";
  function active_tab(tab_id){
    active_tab = tab_id;
  }


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
  tblheader();

  function refresh(req_page = 1) {
    page = req_page;
    if(active_tab.toLowerCase() == "penjualan"){
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
                        <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal'  class = 'detail_button text-success md-eye'  data-target = '#detail_modal'  onclick = 'load_detail_content(${a})'></i>
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
    else if(active_tab.toLowerCase() == "barang"){
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
                        <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal'  class = 'detail_button text-success md-eye'  data-target = '#detail_modal'  onclick = 'load_detail_content(${a})'></i>
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

  function search() {
    searchKey = $("#search_box").val();
    refresh();
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
</script>
<script>
  menubar();
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
</script>