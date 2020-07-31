<?php
$page_title = "Log Sistem";
$breadcrumb = array(
    "Log Sistem"
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
                        <div class="col-lg-12 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords($page_title);?></h6>
                                    </div>
                                    <div class="clearfix"></div>
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
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class = "col-lg-12">
                                            <?php $this->load->view("_base_element/table");?>
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
    var ctrl = "log";
    var url_add = "";
    var unautorized_button = ["edit_button","delete_button"];
</script>
<?php $this->load->view("log/f-detail-log");?>
<?php $this->load->view("_core_script/table_func");?>