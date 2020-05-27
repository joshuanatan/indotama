<?php if($this->session->access_gudang):?>
<li>
    <hr class="light-grey-hr mb-10" />
</li>
<li>
    <a href="#" href="javascript:void(0);" data-toggle="collapse" data-target="#sub_menu_warehouse" class="collapsed" ><?php #redirect ke daftar toko yang dia boleh akses?> 
        <div class="pull-left">
            <span class="right-nav-text">MANAJEMEN GUDANG</span>
        </div>
        <hr/>
    </a>
    
    <ul id="sub_menu_warehouse" class="collapse-level-1 collapse" aria-expanded="false" style="height: 0px;">
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
    </ul>
</li>
<?php endif;?>