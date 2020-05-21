
<li>
    <hr class="light-grey-hr mb-10" />
</li>
<li>
    <a href="#"><?php #redirect ke daftar toko yang dia boleh akses?> 
        <div class="pull-left">
            <span class="right-nav-text">MANAJEMEN GUDANG</span>
        </div>
        <hr/>
    </a>
</li>
<?php if($this->session->multiple_warehouse_access):?>
<li>
    <a href="<?php echo base_url();?>warehouse/daftar_akses_gudang"><?php #redirect ke daftar toko yang dia boleh akses?> 
        <div class="pull-left">
            <span class="right-nav-text">DAFTAR GUDANG</span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php endif;?>
<?php if($this->session->id_warehouse):?>
<li>
    <a href="<?php echo base_url();?>warehouse/daftar_akses_gudang"><?php #redirect ke daftar toko yang dia boleh akses?> 
        <div class="pull-left">
            <span class="right-nav-text">AKTIF: <?php echo strtoupper($this->session->warehouse_nama);?></span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<li id = "gudang_menu_separator"></li>
<?php endif;?>