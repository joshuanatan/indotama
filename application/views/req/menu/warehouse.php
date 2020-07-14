<?php 
if($this->session->access_gudang):
?>
<?php 
if($this->session->id_warehouse):?>
<li class="navigation-header">
    <span><?php echo ucwords(strtolower($this->session->nama_warehouse));?></span> 
    <i class="zmdi zmdi-more"></i>
</li>
<?php else: ?>
<li class="navigation-header">
    <span>Tidak ada gudang aktif</span> 
    <i class="zmdi zmdi-more"></i>
</li> 
<?php endif;?>
<li>
    <a href="javascript:void(0);" data-toggle="collapse" data-target="#sub_menu_warehouse">    
        <div class="pull-left">
            <i class="zmdi zmdi-flag mr-20"></i>
            <span class="right-nav-text">Manajemen Gudang</span>
        </div>
        <div class="pull-right">
            <i class="zmdi zmdi-caret-down"></i>
        </div>
        <div class="clearfix"></div>
    </a>
    <ul id="sub_menu_warehouse" class="collapse-level-1 collapse" aria-expanded="false" style="height: 0px;">
        <?php if($this->session->multiple_warehouse_access):?>
        <li>
            <a href="<?php echo base_url();?>warehouse/daftar_akses_gudang"><?php #redirect ke daftar toko yang dia boleh akses?> 
                Daftar Gudang
            </a>
        </li>
        <?php endif;?>
        <?php if($this->session->id_warehouse):?>
        <li id = "gudang_menu_separator"></li>
        <?php endif;?>
        <?php if($this->session->id_warehouse):?>
        <li>
            <a href="<?php echo base_url();?>warehouse/pengaturan_warehouse">
                Pengaturan Gudang
            </a>
        </li>
        <?php endif;?>
    </ul>
</li>
<?php endif;?>