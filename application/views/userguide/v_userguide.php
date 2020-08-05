<!DOCTYPE html>
<html lang="en">
    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="keywords" content="">

        <title>Item Name | Documentation by Author Name</title>

        <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/fonts/font-awesome-4.3.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/css/stroke.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/css/animate.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/css/prettyPhoto.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/css/style.css">

        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/js/syntax-highlighter/styles/shCore.css" media="all">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/js/syntax-highlighter/styles/shThemeRDark.css" media="all">

        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>asset/userguide/css/custom.css">

    </head>

    <body>
        <div id="wrapper">
            <div class="container">
                <div class="row">
                    <section id="top" class="section docs-heading">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="big-title text-center">
                                    <h1>Sistem Indotama Maju Mandiri</h1>
                                    <p class="lead">documentation version 1.0 (Deployment date: 05/08/2020)</p>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </section>
                    <div class="col-md-3">
                        <!-- navbar -->
                        <nav class="docs-sidebar" data-spy="affix" data-offset-top="300" data-offset-bottom="200" role="navigation">
                            <ul class="nav" id = "nav_container">
                                
                            </ul>
                        </nav >
                        <!-- end navbar -->
                    </div>
                    <div class="col-md-9">
                        <div id = "content-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<script src="<?php echo base_url();?>asset/userguide/js/jquery.min.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/bootstrap.min.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/retina.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/jquery.fitvids.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/wow.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/jquery.prettyPhoto.js"></script>

<!-- CUSTOM PLUGINS -->
<script src="<?php echo base_url();?>asset/userguide/js/custom.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/main.js"></script>

<script src="<?php echo base_url();?>asset/userguide/js/syntax-highlighter/scripts/shCore.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/syntax-highlighter/scripts/shBrushXml.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/syntax-highlighter/scripts/shBrushCss.js"></script>
<script src="<?php echo base_url();?>asset/userguide/js/syntax-highlighter/scripts/shBrushJScript.js"></script>
<script>
    $.ajax({
        url:"<?php echo base_url();?>ws/userguide/content",
        async:false,
        type:"GET",
        dataType:"JSON",
        success:function(respond){
            if(respond["status"].toLowerCase() == "success"){
                var html = "";
                for(var a = 0; a<respond["menu"].length; a++){
                    html += `<li><a href="#${respond["menu"][a]["link"]}">${respond["menu"][a]["name"]}</a>`;
                    if(respond["menu"][a]["type"].toLowerCase() == "multiple"){
                        html += `<ul class="nav">`;
                        for(var b = 0; b<respond["menu"][a]["child"].length; b++){
                            html += `<li><a href="#${respond["menu"][a]["child"][b]["link"]}">${respond["menu"][a]["child"][b]["name"]}</a></li>`;
                        }
                        html += `</ul>`;
                    }
                    html += `</li>`;
                }
                $("#nav_container").html(html);

                var html = "";
                for(var a = 0; a<respond["content"].length; a++){
                    html += `
                    <section id="${respond["content"][a]["id"]}" class="section">
                        <div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">${respond["content"][a]["title"]}<a href="#top">#back to top</a><hr></h2>
                                <p>${respond["content"][a]["desc"]}</p>
                            </div>
                        </div>
                    `;

                    for(var b = 0; b<respond["content"][a]["content"].length; b++){

                        html += `
                        <div class="row">
                            <div class="col-md-12">
                                <h4>${respond["content"][a]["content"][b]["title"]}</h4>`;
                                for(var c = 0; c<respond["content"][a]["content"][b]["images"].length; c++){
                                    html += `<img src = "${respond["content"][a]["content"][b]["images"][c]}" style = "width:100%"><br/><br/>`;
                                }

                                html += `<p>${respond["content"][a]["content"][b]["explanation"]}</p>
                            </div>
                        </div>`;
                    }
                    html += `
                    </section>`;
                }
                $("#content-container").html(html);
            }
        }
    })
</script>