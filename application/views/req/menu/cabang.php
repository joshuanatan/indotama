<?php
#Session required: user,id_cabang
?>
<li>
    <hr class="light-grey-hr mb-10" />
</li>
<li>
    <a href="#"><?php #redirect ke daftar cabang yang dia boleh akses?>
        <div class="pull-left">
            <span class="right-nav-text">MANAJEMEN CABANG</span>
        </div>
        <hr/>
    </a>
</li>
<?php if($this->session->multiple_cabang_access):?>
<li>
    <a href="<?php echo base_url();?>toko/daftar_akses_cabang"><?php #redirect ke daftar cabang yang dia boleh akses?>
        <div class="pull-left">
            <span class="right-nav-text">DAFTAR CABANG</span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php endif;?>
<?php 
if($this->session->id_cabang):?>
<li>
    <a href="#">
        <div class="pull-left">
            <span class="right-nav-text">AKTIF: <?php echo strtoupper($this->session->nama_toko_cabang);?> - <?php echo strtoupper($this->session->daerah_cabang);?></span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<li id = "cabang_menu_separator"></li>
<?php endif;?>