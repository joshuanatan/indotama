<script src="<?php echo base_url();?>assets/global/js/Plugin/jquery-placeholder.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/material.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/jquery-placeholder/jquery.placeholder.js"></script>

<!-- advanced form plugin -->
<script src="<?php echo base_url();?>assets/global/vendor/select2/select2.full.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/bootstrap-select/bootstrap-select.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/icheck/icheck.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/switchery/switchery.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/asrange/jquery-asRange.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/ionrangeslider/ion.rangeSlider.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/asspinner/jquery-asSpinner.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/clockpicker/bootstrap-clockpicker.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/ascolor/jquery-asColor.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/asgradient/jquery-asGradient.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/ascolorpicker/jquery-asColorPicker.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/bootstrap-maxlength/bootstrap-maxlength.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/jquery-knob/jquery.knob.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/bootstrap-touchspin/bootstrap-touchspin.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/jquery-labelauty/jquery-labelauty.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/timepicker/jquery.timepicker.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/datepair/datepair.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/datepair/jquery.datepair.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/jquery-strength/password_strength.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/jquery-strength/jquery-strength.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/multi-select/jquery.multi-select.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/typeahead-js/bloodhound.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/typeahead-js/typeahead.jquery.min.js"></script>
<script src="<?php echo base_url();?>assets/global/vendor/jquery-placeholder/jquery.placeholder.js"></script>
<!-- end of advance form plugin -->
<!-- advance form on page js -->
<script src="<?php echo base_url();?>assets/global/js/Plugin/select2.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/bootstrap-tokenfield.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/bootstrap-tagsinput.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/bootstrap-select.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/icheck.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/switchery.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/asrange.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/ionrangeslider.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/asspinner.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/clockpicker.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/ascolorpicker.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/bootstrap-maxlength.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/jquery-knob.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/bootstrap-touchspin.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/card.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/jquery-labelauty.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/bootstrap-datepicker.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/jt-timepicker.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/datepair.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/jquery-strength.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/multi-select.js"></script>
<script src="<?php echo base_url();?>assets/global/js/Plugin/jquery-placeholder.js"></script>
<script>
function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}
</script>

<script>
function splitter(str,sprt){
    var finalValue = "";
    var split = str.split(sprt);
    for(var a = 0; a<split.length; a++){
        finalValue += split[a];
    }
    return finalValue;
}
</script>
<script>
function splitToArray(str,sprt){
    var finalValue = [];
    var split = str.split(sprt);
    return split;
}
</script>
<script>
function commas(id){
	var str = $("#"+id).val();
	str = splitter(str,",");
    str = addCommas(str);
    $("#"+id).val(str);
}
</script>