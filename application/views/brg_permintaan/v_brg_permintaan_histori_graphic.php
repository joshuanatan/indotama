<?php
$page_title = "Permintaan Barang";
$breadcrumb = array(
    "Permintaan Barang"
);
$notif_data = array(
    "page_title"=>$page_title
);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php $this->load->view('req/mm_css.php');?>
    </head>

    <body>
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <div class="wrapper theme-1-active pimary-color-pink">

            <?php $this->load->view('req/mm_menubar.php');?>

            <div class="page-wrapper">
                
                <div class="container-fluid">
                    <div class="row mt-20">
                        <div class="col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class = "col-lg-4">
                                            <div class = "form-group">
                                                <h5>Tanggal Histori</h5>    
                                                <input type = "date" class = "form-control">
                                            </div>
                                        </div>
                                        <div class = "col-lg-2">
                                            <div class = "form-group">
                                                <h5>&nbsp;</h5>    
                                                <button type = "button" class = "btn btn-primary btn-sm">Lihat Histori</button>
                                            </div>
                                        </div>
                                        <div class = "col-lg-6">
                                            <ol class="breadcrumb">
                                                <li class="breadcrumb-item">Home</a></li>
                                                <?php for($a = 0; $a<count($breadcrumb); $a++):?>
                                                <?php if($a+1 != count($breadcrumb)):?>
                                                <li class="breadcrumb-item"><?php echo ucwords($breadcrumb[$a]);?></a></li>
                                                <?php else:?>
                                                <li class="breadcrumb-item active"><?php echo ucwords($breadcrumb[$a]);?></li>
                                                <?php endif;?>
                                                <?php endfor;?>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords("Histori Pengiriman");?></h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class = "col-lg-12">
                                            <ul class="timeline">
                                                <?php for($a = 0 ;$a<10; $a++):?>
                                                <li>
                                                    <div class="timeline-badge bg-yellow">
                                                        <i class="icon-layers" ></i>
                                                    </div>
                                                    <div class="timeline-panel pa-30">
                                                        <div class="timeline-heading">
                                                            <h6 class="mb-15">1 september 15</h6>
                                                        </div>
                                                        <div class="timeline-body">
                                                            <h4 class="mb-5">pogody</h4>
                                                            <p class="lead head-font mb-20">Responsive html5 template</p>
                                                            <p>Invitamus me testatur sed quod non dum animae tuae lacrimis ut libertatem deum rogus aegritudinis causet. Dicens hoc contra serpentibus isto.</p>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php endfor;?>
                                                
                                                <li class="clearfix no-float"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords("Histori Permintaan Barnag");?></h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class = "col-lg-12">
                                            <ul class="timeline timeline-container-invert">
                                                <?php for($a = 0 ;$a<10; $a++):?>
                                                    
                                                <li class="timeline-inverted">
                                                    <div class="timeline-badge bg-pink">
                                                        <i class="icon-magnifier-add" ></i>
                                                    </div>
                                                    <div class="timeline-panel pa-30">
                                                        <div class="timeline-heading">
                                                            <h6 class="mb-15">23 March 16</h6>
                                                        </div>
                                                        <div class="timeline-body">
                                                            <h4 class=" mb-5">Beavis</h4>
                                                            <p class="lead  mb-20">HTML5 Coming Soon Template</p>
                                                            <p>Invitamus me testatur sed quod non dum animae tuae lacrimis ut libertatem deum rogus aegritudinis causet. Dicens hoc contra serpentibus isto.</p>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php endfor;?>
                                                
                                                <li class="clearfix no-float"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $this->load->view('req/mm_footer.php');?>
                </div>
            </div>
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>
<script>
    var ctrl = "permintaan";
</script>
<?php $this->load->view("_core_script/menubar_func");?>
