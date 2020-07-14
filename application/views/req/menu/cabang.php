<?php
if($this->session->access_cabang):
?>
<?php 
if($this->session->id_cabang):?>
<li class="navigation-header">
    <span><?php echo ucwords(strtolower($this->session->nama_toko_cabang));?> - <?php echo ucwords(strtolower($this->session->daerah_cabang));?></span> 
    <i class="zmdi zmdi-more"></i>
</li>
<?php else: ?>
<li class="navigation-header">
    <span>Tidak ada cabang aktif</span> 
    <i class="zmdi zmdi-more"></i>
</li> 
<?php endif;?>
<li>
    <a href="javascript:void(0);" data-toggle="collapse" data-target="#sub_menu_cabang">    
        <div class="pull-left">
            <i class="zmdi zmdi-apps mr-20"></i>
            <span class="right-nav-text">Manajemen Cabang</span>
        </div>
        <div class="pull-right">
            <i class="zmdi zmdi-caret-down"></i>
        </div>
        <div class="clearfix"></div>
    </a>
    <ul id="sub_menu_cabang" class="collapse-level-1 collapse" aria-expanded="false" style="height: 0px;">
        <?php if($this->session->multiple_cabang_access):?>
        <li>
            <a href="<?php echo base_url();?>toko/daftar_akses_cabang"><?php #redirect ke daftar cabang yang dia boleh akses?>
                Daftar Cabang
            </a>
        </li>
        <?php endif;?>

        <?php 
        if($this->session->id_cabang):?>
        <li id = "cabang_menu_separator"></li>
        <?php endif;?>
        <?php if($this->session->id_cabang):?>
        <li>
            <a href="<?php echo base_url();?>toko/pengaturan_cabang">
                Pengaturan Cabang
            </a>
        </li>
        <?php endif;?>
    </ul>
</li>
<?php endif;?>