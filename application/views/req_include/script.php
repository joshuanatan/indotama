<script src="<?php echo base_url();?>assets/global/vendor/babel-external-helpers/babel-external-helpers.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/jquery/jquery.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/popper-js/umd/popper.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/bootstrap/bootstrap.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/animsition/animsition.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/mousewheel/jquery.mousewheel.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/asscrollbar/jquery-asScrollbar.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/asscrollable/jquery-asScrollable.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/ashoverscroll/jquery-asHoverScroll.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/waves/waves.js"></script>

<!-- Plugins -->
<script src="<?php echo base_url();?>assets/global/vendor/switchery/switchery.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/intro-js/intro.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/screenfull/screenfull.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/slidepanel/jquery-slidePanel.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Component.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Base.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Config.js"></script>

<script src="<?php echo base_url();?>assets/main/js/Section/Menubar.js"></script>
<script src="<?php echo base_url();?>assets/main/js/Section/GridMenu.js"></script>
<script src="<?php echo base_url();?>assets/main/js/Section/Sidebar.js"></script>
<script src="<?php echo base_url();?>assets/main/js/Section/PageAside.js"></script>
<script src="<?php echo base_url();?>assets/main/js/Plugin/menu.js"></script>

<script src="<?php echo base_url();?>assets/global/js/config/colors.js"></script>
<script src="<?php echo base_url();?>assets/main/js/config/tour.js"></script>
<script>Config.set('assets', '<?php echo base_url();?>assets/main');</script>

<!-- Page -->
<script src="<?php echo base_url();?>assets/main/js/Site.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/asscrollable.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/slidepanel.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/switchery.js"></script>

<script>
    (function(document, window, $){
        'use strict';

        var Site = window.Site;
        $(document).ready(function(){
            Site.run();
        });
    })(document, window, jQuery);
</script>