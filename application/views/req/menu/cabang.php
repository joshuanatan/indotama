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
            <span class="right-nav-text">CABANG</span>
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
            <span class="right-nav-text">AKTIF: <?php echo strtoupper($this->session->nama_toko);?> - <?php echo strtoupper($this->session->daerah_cabang);?></span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php
if(isset($cabang)):for($a = 0; $a<count($cabang); $a++):?>
<li>
    <a href="<?php echo base_url();?><?php echo $cabang[$a]["menu_name"];?>">
        <div class="pull-left">
            <i class="md-<?php echo $cabang[$a]["menu_icon"];?>"></i>
            <span class="right-nav-text" style="margin-left:20px"><?php echo strtoupper($cabang[$a]["menu_display"]);?></span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php endfor;endif;endif;?>
<?php #endif (1) session cabang true?>