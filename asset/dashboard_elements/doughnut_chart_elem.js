function populate_doughnut_data(doughnut_title,amt_doughnut){
    var template = `
    <div class="col-lg-6" style = "width:50%">
        <div class="panel panel-default card-view">
            <div class="panel-heading">
                <div class="pull-left">
                    <h6 class="panel-title txt-dark">${doughnut_title}</h6>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-wrapper collapse in">
                <div class="panel-body">
                    <canvas class = "doughnut_elem" id="doughnut${amt_doughnut}" height = "100"></canvas>
                </div>	
            </div>
        </div>
    </div>`;
    return template;
}
function init_doughnut_data(doughnut_data,count){
    var background_color_master = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)',
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
    var color_control = [];
    var color_index;
    var backgroundColor = [];
    var borderColor = [];

    for(var a = 0; a<doughnut_data["data"].length; a++){
        do{
            color_index = Math.floor(Math.random() * 6);
        }
        while(color_control.includes(color_index));
        color_control.push(color_index);
        
        backgroundColor[a] = background_color_master[color_index];
        borderColor[a] = border_color_master[color_index];
    }

    label = doughnut_data["label"];
    datasets.push(
        {
            data:doughnut_data["data"],
            backgroundColor:backgroundColor,
            hoverBackgroundColor:borderColor
        }
    );
    console.log(label);
    var ctx = document.getElementById(`doughnut${count}`);
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: label,
            datasets: datasets
        },
        options: {
            animation: {
                duration:	3000
            },
            responsive: true,
            legend: {
                labels: {
                fontFamily: "Montserrat",
                fontColor:"#878787"
                }
            },
            tooltip: {
                backgroundColor:'rgba(33,33,33,1)',
                cornerRadius:0,
                footerFontFamily:"'Montserrat'"
            },
            elements: {
                arc: {
                    borderWidth: 0
                }
            }
        }
    });
}


