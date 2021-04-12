<script>
    tblheader();
    function tblheader(){
        if(typeof(ctrl) != "undefined"){
            $.ajax({
                url: "<?php echo base_url();?>ws/"+ctrl+"/"+tblHeaderCtrl,
                type: "GET",
                dataType: "JSON",
                async:false,
                success: function(respond) {
                    var html = "";
                    if(respond["status"] == "SUCCESS"){
                        colCount = respond["content"].length+1; //sama col action
                        html += "<tr>";
                        for(var a = 0; a<respond["content"].length; a++){
                            html += "<th id = 'col"+a+"' style = 'cursor:pointer' onclick = 'sort("+a+")' class = 'text-center align-middle'>"+respond["content"][a]["col_name"];
                            if(a == 0){
                                html += " <span class='badge badge-primary align-top' id = 'orderDirection'>A-Z</span>";
                            }
                            html += "</th>";
                        }
                        html += "<th class = 'text-center align-middle action_column'>Action</th>";
                        html += "</tr>";
                    }
                    else{
                        html += "<tr>";
                        html += "<th class = 'align-middle text-center'>Columns is not defined</th>";
                        html += "</tr>";
                    }
                    $("#col_title_container").html(html);
                    
                },
                error: function(){
                    var html = "<tr>";
                    html += "<th class = 'align-middle text-center'>Columns is not defined</th>";
                    html += "</tr>";
                    $("#col_title_container").html(html);
                }
            });
        }
    }
    function pagination(page_rules){
        html = "";
        if(page_rules["previous"]){
            html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["before"])+')"><</a></li>';
        }
        else{
            html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
        }
        if(page_rules["first"]){
            html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["first"])+')">'+(page_rules["first"])+'</a></li>';
            html += '<li class="page-item"><a class="page-link">...</a></li>';
        }
        if(page_rules["before"]){
            html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["before"])+')">'+page_rules["before"]+'</a></li>';
        }
        html += '<li class="page-item active"><a class="page-link" onclick = "refresh('+(page_rules["current"])+')">'+page_rules["current"]+'</a></li>';
        if(page_rules["after"]){
            html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["after"])+')">'+page_rules["after"]+'</a></li>';
        }
        if(page_rules["last"]){
            html += '<li class="page-item"><a class="page-link">...</a></li>';
            html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["last"])+')">'+page_rules["last"]+'</a></li>';
        }
        if(page_rules["next"]){
            html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["after"])+')">></a></li>';
        }
        else{
            html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
        }
        $(".pagination_container").html(html);
    }
    function search(){
        searchKey = $("#search_box").val();
        refresh();
    }
    function sort(colNum){
        if(parseInt(colNum) != orderBy){
            orderBy = colNum; 
            orderDirection = "ASC";
            var orderDirectionHtml = ' <span class="badge badge-primary align-top" id = "orderDirection">A-Z</span>';
            $("#orderDirection").remove();
            $("#col"+colNum).append(orderDirectionHtml);
        }
        else{
            var direction = $("#orderDirection").text();
            if(direction == "A-Z"){
                orderDirection = "DESC";
                orderDirectionHtml = "Z-A";
            }
            else{
                orderDirection = "ASC";
                orderDirectionHtml = "A-Z";
            }
            $("#orderDirection").text(orderDirectionHtml);
        }
        refresh();
    }
    menubar();
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
