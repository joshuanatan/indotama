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

<body style = "background-color:white">
  <div class="form-group">
    <h5>Search Data Here</h5>
    <input id="search_box" placeholder="Search data here..." type="text" class="form-control input-sm " onkeyup="search()" style="width:25%">
  </div>
  <nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center pagination_container">
    </ul>
  </nav>
  <div class="table-responsive">
    <table class="table table-bordered table-striped" id="table_container">
      <thead id="col_title_container">
      </thead>
      <tbody id="content_container" class="content_container">
      </tbody>
    </table>
  </div>
  <nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center pagination_container">
    </ul>
  </nav>
  <?php $this->load->view('req/mm_js.php'); ?>
</body>

</html>
<?php $this->load->view("req/core_script"); ?>

<script>
  var ctrl = "customer";
  var tblHeaderCtrl = "columns_detail_penjualan";
  var contentCtrl = "detail_penjualan";
  var colCount = 10; //ragu either 1/0
  var orderBy = 0;
  var orderDirection = "ASC";
  var searchKey = "";
  var page = 1;
  var url_add = "";

  refresh();

  function refresh(req_page = 1) {
    page = req_page;
    $.ajax({
      url: `<?php echo base_url();?>ws/${ctrl}/${contentCtrl}/<?php echo $id_pk_customer;?>?orderBy=${orderBy}&orderDirection=${orderDirection}&page=${page}&searchKey=${searchKey}&${url_add}`,
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
              case "selesai":
                html_status += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top">${respond["content"][a]["penj_status"].toUpperCase()}</span></td>`;
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
            if (respond["content"][a]["status_pembayaran"].toLowerCase() != "belum lunas" || respond["content"][a]["penj_status"] == "selesai") {
              html_durasi_pembayaran += `<td class = 'align-middle text-center'>-</td>`;
            } else {
              if (respond["content"][a]["selisih_tanggal"] > 0) {
                html_durasi_pembayaran += `<td class = 'align-middle text-center'><span class="badge badge-success align-top">${Math.abs(respond["content"][a]["selisih_tanggal"])} Hari </span></td>`;
              } else {
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