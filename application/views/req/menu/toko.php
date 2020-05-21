<?php
#Session required: user,id_toko
?>
<li>
    <hr class="light-grey-hr mb-10" />
</li>
<li>
    <a href="#"><?php #redirect ke daftar toko yang dia boleh akses?> 
        <div class="pull-left">
            <span class="right-nav-text">MANAJEMEN TOKO</span>
        </div>
        <hr/>
    </a>
</li>
<?php if($this->session->multiple_toko_access):?>
<li>
    <a href="<?php echo base_url();?>toko/daftar_akses_toko"><?php #redirect ke daftar toko yang dia boleh akses?> 
        <div class="pull-left">
            <span class="right-nav-text">DAFTAR TOKO</span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php endif;?>
<?php 
if($this->session->id_toko):?>
<li>
    <a href="#">
        <div class="pull-left">
            <span class="right-nav-text">AKTIF: <?php echo strtoupper($this->session->nama_toko);?></span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<li id = "toko_menu_separator"></li>
<?php endif;?>
