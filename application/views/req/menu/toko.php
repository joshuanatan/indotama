<?php
if($this->session->access_toko):
?>
<?php 
if($this->session->id_toko):?>
<li class="navigation-header">
    <span><?php echo ucwords(strtolower($this->session->nama_toko));?></span> 
    <i class="zmdi zmdi-more"></i>
</li>
<?php else: ?>
<li class="navigation-header">
    <span>Tidak ada toko aktif</span> 
    <i class="zmdi zmdi-more"></i>
</li> 
<?php endif;?>
<li>
    <a href="javascript:void(0);" data-toggle="collapse" data-target="#sub_menu_toko">    
        <div class="pull-left">
            <i class="zmdi zmdi-shopping-basket mr-20"></i>
            <span class="right-nav-text">Manajemen Toko</span>
        </div>
        <div class="pull-right">
            <i class="zmdi zmdi-caret-down"></i>
        </div>
        <div class="clearfix"></div>
    </a>
    <ul id="sub_menu_toko" class="collapse-level-1 collapse" aria-expanded="false" style="height: 0px;">
    
        <?php if($this->session->multiple_toko_access):?>
        <li style = "background-color:rgba(3, 0, 46, 0.2);;">
            <a href="<?php echo base_url();?>toko/daftar_akses_toko">
                <div class = 'pull-left'>
                    <div class="pull-left">
                        <i class="md-edit mr-20"></i>
                        <span class="right-nav-text">Daftar Toko</span>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class = 'clearfix'></div>
            </a>
        </li>
        <?php endif;?>
        <?php 
        if($this->session->id_toko):?>
        <li id = "toko_menu_separator"></li>
        <?php endif;?>
        <?php
        /*abaikan sementara*/
        if(false):?>
        <li>
            <a href="<?php echo base_url();?>toko/pengaturan_toko">
            Pengaturan Toko
            </a>
        </li>
        <?php endif;?>
    </ul>
</li>
<?php endif;?>