<?php
$page_title = "Master Supplier";
$breadcrumb = array(
  "Master", "Supplier", "Detail"
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
                          <h6 class="panel-title txt-dark">Detail Supplier</h6>
                        </div>
                        <div class="pull-right">
                          <div class="tab-struct custom-tab-1">
                            <ul role="tablist" class="nav nav-tabs" id="myTabs_9">
                              <li onclick = "active_tab('penjualan')" class="active" role="presentation"><a data-toggle="tab" role="tab" id="home_tab_9" href="#penjualan">Pembelian</a></li>
                              <li onclick = "active_tab('barang')" role="presentation" class=""><a data-toggle="tab" id="profile_tab_9" role="tab" href="#barang" aria-expanded="false">Produk Pembelian</a></li>
                            </ul>
                          </div>
                        </div>

                        <div class="clearfix"></div>
                      </div>
                      <div class="panel-wrapper collapse in">
                        <div class="panel-body">
                          <div class="tab-content" id="myTabContent_9">
                            <div id="penjualan" class="tab-pane fade active in" role="tabpanel">
                              <iframe src = "<?php echo base_url();?>supplier/table_pembelian_detail_supplier/<?php echo $id_pk_supplier;?>" style = "width:100%;height:400px"></iframe>
                            </div>
                            <div id="barang" class="tab-pane fade" role="tabpanel">
                              <iframe src = "<?php echo base_url();?>supplier/table_brg_pembelian_detail_supplier/<?php echo $id_pk_supplier;?>" style = "width:100%;height:400px"></iframe>
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