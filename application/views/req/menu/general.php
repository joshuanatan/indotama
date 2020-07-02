<?php
if($this->session->id_user):
?>

<li class="navigation-header">
    <span>Manajemen Umum</span> 
    <i class="zmdi zmdi-more"></i>
</li>
<li>
    <a href="javascript:void(0);" data-toggle="collapse" data-target="#sub_menu_umum">    
        <div class="pull-left">
            <i class="zmdi zmdi-landscape mr-20"></i>
            <span class="right-nav-text">Manajemen Umum</span>
        </div>
        <div class="pull-right">
            <i class="zmdi zmdi-caret-down"></i>
        </div>
        <div class="clearfix"></div>
    </a>
    <ul id="sub_menu_umum" class="collapse-level-1 collapse">
        <li id = "general_menu_separator"></li>
    </ul>
</li>
<?php endif;?>