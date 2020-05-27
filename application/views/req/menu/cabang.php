<?php
if($this->session->access_cabang):
?>
<li>
    <hr class="light-grey-hr mb-10" />
</li>
<li>
    <a href="#" href="javascript:void(0);" data-toggle="collapse" data-target="#sub_menu_cabang" class="collapsed" ><?php #redirect ke daftar cabang yang dia boleh akses?>
        <div class="pull-left">
            <span class="right-nav-text">MANAJEMEN CABANG</span>
        </div>
        <hr/>
    </a>
    <ul id="sub_menu_cabang" class="collapse-level-1 collapse" aria-expanded="false" style="height: 0px;">
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
    </ul>
</li>
<?php endif;?>