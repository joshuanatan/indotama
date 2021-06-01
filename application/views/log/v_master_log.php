<?php
$page_title = "Log Sistem";
$breadcrumb = array(
  "Log Sistem"
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

<?php $this->load->view("log/f-detail-log"); ?>
<?php $this->load->view("_core_script/menubar_func"); ?>

<script>
  var ctrl = "log";
  var contentCtrl = "content";
  var tblHeaderCtrl = "columns";
  var colCount = 6; //ragu either 1/0
  var orderBy = 0;
  var orderDirection = "ASC";
  var searchKey = "";
  var page = 1;
  var url_add = "";

  refresh();

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
            html += `
                            <tr>
                                <td>${respond["content"][a]["id"]}</td>
                                <td>${respond["content"][a]["date"]}</td>
                                <td>${respond["content"][a]["user"]}</td>
                                <td>${respond["content"][a]["msg"]}</td>
                                <td>${respond["content"][a]["data"]}</td>
                                <td>
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'detail_button text-success md-eye' data-target = '#detail_modal' onclick = 'load_detail_content(${a})'></i>
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