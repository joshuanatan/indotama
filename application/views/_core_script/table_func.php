<script>
    var colCount = 1; //ragu either 1/0
    var orderBy = 0;
    var orderDirection = "ASC";
    var searchKey = "";
    var page = 1;
    var content = [];

    /*custom get_content_function*/
    var contentCtrl = "content";
    if(typeof(custom_contentCtrl) != "undefined"){
        contentCtrl = custom_contentCtrl;
    }

    if(typeof(url_add) == "undefined"){
        url_add = "";
    }

    function refresh(req_page = 1) {
        if(typeof(ctrl) != "undefined"){
            page = req_page;
            $.ajax({
                url: "<?php echo base_url();?>ws/"+ctrl+"/"+contentCtrl+"?orderBy="+orderBy+"&orderDirection="+orderDirection+"&page="+page+"&searchKey="+searchKey+"&"+url_add,
                type: "GET",
                dataType: "JSON",
                success: function(respond) {
                    var html = "";
                    if(respond["status"] == "SUCCESS"){
                        content = respond["content"];
                        for(var a = 0; a<respond["content"].length; a++){
                            html += "<tr>";
                            for(var b = 0; b<respond["key"].length; b++){
                                if(respond["content"][a][respond["key"][b]] == null){
                                    respond["content"][a][respond["key"][b]] = "";
                                }
                                if(respond["key"][b].toLowerCase() == "status"){
                                    switch(respond["content"][a]["status"].toLowerCase()){
                                        case "aktif":
                                        html += `<td class = 'align-middle text-center'><span class="badge badge-success align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                                        break;
                                        case "konfirmasi":
                                        html += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                                        break;
                                        case "selesai":
                                        html += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                                        break;
                                        default:
                                        html += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top" id = "orderDirection">${respond["content"][a][respond["key"][b]].toUpperCase()}</span></td>`;
                                        break;
                                    }
                                }
                                else{
                                    html += "<td class = 'align-middle text-center'>"+respond["content"][a][respond["key"][b]]+"</td>";
                                }
                            }
                            /*
                                additional button
                                var add_but_array =[
                                    {
                                        html_prop1:html_val1,
                                        html_prop2:html_val2
                                    }
                                ]
                            */
                            if(typeof(additional_button) != "undefined"){
                                var addtnl = ""; 
                                for(var add = 0; add<additional_button.length; add++){
                                    var props = "";
                                    for (var key in additional_button[add]) {
                                        if (additional_button[add].hasOwnProperty(key)) {   
                                            key_final = key.replace("_","-");
                                            props += " "+key_final+"='"+additional_button[add][key]+"'";
                                        }
                                    }
                                    addtnl += "  <i "+props+"></i>";
                                }
                                html += "<td class = 'align-middle text-center action_column'><i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'detail_button text-success md-eye' data-target = '#detail_modal' onclick = 'load_detail_content("+a+")'></i>  <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'edit_button text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content("+a+")'></i>  <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content("+a+")'></i>"+addtnl+"</td>";
                            }
                            else{
                                html += "<td class = 'align-middle text-center action_column'><i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'detail_button text-success md-eye' data-target = '#detail_modal' onclick = 'load_detail_content("+a+")'></i> <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'edit_button text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content("+a+")'></i>  <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content("+a+")'></i></td>";
                            }
                            html += "</tr>";
                        }
                    }
                    else{
                        html += "<tr>";
                        html += "<td colspan = "+colCount+" class = 'align-middle text-center'>No Records Found</td>";
                        html += "</tr>";
                    }
                    $(".content_container:eq(0)").html(html);
                    pagination(respond["page"]);

                    /*
                        unload unauthorized button
                        var unathorized_button = ["classbutton1","classbutton2]
                    */
                    if(typeof(unautorized_button) != "undefined"){
                        for(var a = 0; a<unautorized_button.length; a++){
                            $("."+unautorized_button[a]).remove();
                        }
                    }
                    
                },
                error: function(){
                    var html = "";
                    html += "<tr>";
                    html += "<td colspan = "+colCount+" class = 'align-middle text-center'>No Records Found</td>";
                    html += "</tr>";
                    
                    $(".content_container:eq(0)").html(html);
                    
                    html = "";
                    html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
                    html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
                    $(".pagination_container").html(html);
                }
            });
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
        }
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
    function search(){
        searchKey = $("#search_box").val();
        refresh();
    }
    var tblHeaderCtrl = "columns";
    if(typeof(custom_tblHeaderCtrl) != "undefined"){
        tblHeaderCtrl = custom_tblHeaderCtrl;
    }
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
    document.onreadystatechange = function () {
        if (document.readyState === 'complete') {
            tblheader();
            refresh();
            menubar();
            if(typeof(load_datalist) != "undefined"){
                load_datalist();
            }
        }
    }
    window.onfocus = function(){
        if(typeof(load_datalist) != "undefined"){
            load_datalist();
        }
        menubar();
    }
</script>
