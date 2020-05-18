<?php
#Session required: user,id_warehouse
?>

<li>
    <hr class="light-grey-hr mb-10" />
</li>
<li>
    <a href=""><?php #redirect ke daftar toko yang dia boleh akses?> 
        <div class="pull-left">
            <span class="right-nav-text">GUDANG</span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php 
if(isset($gudang)):
for($a = 0; $a<count($gudang); $a++):?>
<li>
    <a href="<?php echo base_url();?><?php echo $gudang[$a]["menu_name"];?>">
        <div class="pull-left">
            <i class="md-<?php echo $gudang[$a]["menu_icon"];?>"></i>
            <span class="right-nav-text" style="margin-left:20px"><?php echo strtoupper($gudang[$a]["menu_display"]);?></span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php endfor;endif;?>