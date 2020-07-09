function populate_chart_data(chart_title,amt_chart){
    var template = `
    <div class="col-lg-12" style = "width:100%">
        <div class="panel panel-default card-view">
            <div class="panel-heading">
                <div class="pull-left">
                    <h6 class="panel-title txt-dark">${chart_title}</h6>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-wrapper collapse in">
                <div class="panel-body">
                    <canvas class = "chart_elem" id="myChart${amt_chart}" height = "100"></canvas>
                </div>	
            </div>
        </div>
    </div>`;
    return template;
}
function init_chart_data(label,chart_data,count){
    var background_color_master = [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)',
    ];
    var border_color_master = [
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
    ];
    var datasets = [];
    for(var a = 0; a<chart_data.length; a++){
        var obj = {};
        obj.data = [];
        obj.backgroundColor = [];
        obj.borderColor = [];
        for(var b = 0; b<chart_data[a]["data"].length; b++){
            obj.data[b] = chart_data[a]["data"][b];
            obj.backgroundColor[b] = background_color_master[b%background_color_master.length];
            obj.borderColor[b] = border_color_master[b%border_color_master.length];
        }
        obj.label = chart_data[a]["label"];
        obj.borderWidth  = 1;
        datasets.push(obj);
    }
    var ctx = document.getElementById(`myChart${count}`);
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: label,
            datasets: datasets
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
}


