<?php
#Session required: user,id_cabang
?>
<li>
    <hr class="light-grey-hr mb-10" />
</li>

<?php #if (1) session cabang true?>
<?php #if (2) session multiple cabang true ?>
<li>
    <a href="#"><?php #redirect ke daftar cabang yang dia boleh akses?>
        <div class="pull-left">
            <span class="right-nav-text">DAFTAR CABANG</span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php #endif (2) session multiple cabang true ?>
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
<?php endfor;endif;?>
<?php #endif (1) session cabang true?>