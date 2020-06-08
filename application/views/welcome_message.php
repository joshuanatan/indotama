<?php
$page_title = "Dashboard";
$breadcrumb = array(
    "Dashboard"
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

        <?php $this->load->view('req/mm_menubar');?>

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
                                </div>
                                <div class = "panel-body">
                                    <?php $this->load->view("home/v_daftar_permintaan");?>
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
<?php $this->load->view("_core_script/table_func");?>
<script>
window.onload = function() {
    if(!window.location.hash) {
        window.location = window.location + '#loaded';
        window.location.reload();
    }
}
</script>