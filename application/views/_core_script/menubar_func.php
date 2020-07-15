<script>
/*kepaksa kepisah karena ada kebutuhan load menubar tanpa load table fun*/
function menubar(){
    $.ajax({
        url:"<?php echo base_url();?>ws/menu/menubar",
        type:"GET",
        async:false,
        dataType:"JSON",
        success:function(respond){
            if(respond["status"] == "SUCCESS"){
                var menu_category = "";
                var html = "";
                for(var a = 0; a<respond["data"].length; a++){
                    if(menu_category != respond["data"][a]["menu_category"]){
                        if(html != ""){
                            $("#"+menu_category.toLowerCase()+"_menu_separator").after(html);
                        }
                        html = "";
                        menu_category = respond["data"][a]["menu_category"];
                        $("."+menu_category.toLowerCase()+"_menu_item").remove();
                        console.log("."+menu_category.toLowerCase()+"_menu_item");
                    }
                    /* Tambahin background color di menu item, dan icon */
                    html += `
                    <li class = '${menu_category.toLowerCase()}_menu_item' style = "background-color:rgba(3, 0, 46, 0.2);;">
                        <a href="<?php echo base_url();?>${respond["data"][a]["menu_name"]}">
                            <div class = 'pull-left'>
                                <div class="pull-left">
                                    <i class="md-${respond["data"][a]["menu_icon"]} mr-20"></i>
                                    <span class="right-nav-text">${respond["data"][a]["menu_display"]}</span>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class = 'clearfix'></div>
                        </a>
                    </li>
                    `;
                }
                $("#"+menu_category.toLowerCase()+"_menu_separator").after(html);
            }
        }
    })
}
</script>