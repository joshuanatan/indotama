<script>
window.onload = function(e){ 
    $("#notif_register_success").css("display", "none");
    $("#notif_register_error").css("display", "none");
}

$( ".closeSuccess" ).click(function() {
    $("#notif_register_success").css("display", "none");
});
</script>