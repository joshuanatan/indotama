function populate_table_data(table_header,table_data,table_title,amt_table){
    var header = "<tr role='row'>";
    for(var a = 0; a<table_header.length; a++){
        header += `<th>${table_header[a]}</th>`;
    }
    header += "</tr>";

    var data = "";
    if(table_data.length >0){
        for(var a = 0; a<table_data.length; a++){
            data += "<tr>";
            for(var b = 0; b<table_data[a].length; b++){
                data += `<td>${table_data[a][b]}</td>`;
            }
            data += "</tr>";
        }
    }
    else{
        data = `<tr><td style = 'text-align:center' colspan = '${table_header.length}'>No Data</td></tr>`;
    }

    var template = `
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
            <div class="panel panel-default card-view">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h6 class="panel-title txt-dark">${table_title}</h6>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-wrapper collapse in">
                    <div class="panel-body">
                        <div class="table-wrap">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped display mb-30"  id = "table${amt_table}">
                                    <thead>
                                        ${header}
                                    </thead>
                                    <tbody>
                                        ${data}
                                    </tbody>
                                </table>
                            </div>	
                        </div>
                    </div>
                </div> 
            </div>
        </div>`;
    return template;
}