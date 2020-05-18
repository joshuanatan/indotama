<?php
#Session required: user
?>

<li>
    <hr class="light-grey-hr mb-10" />
</li>
<?php 
if(isset($general)):for($a = 0; $a<count($general); $a++):?>
<li>
    <a href="<?php echo base_url();?><?php echo $general[$a]["menu_name"];?>">
        <div class="pull-left">
            <i class="md-<?php echo $general[$a]["menu_icon"];?>"></i>
            <span class="right-nav-text" style="margin-left:20px"><?php echo strtoupper($general[$a]["menu_display"]);?></span>
        </div>
        <div class="clearfix"></div>
    </a>
</li>
<?php endfor;endif;?>