function populate_widget_data(widget_data,widget_title){
    var template = `
    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
        <div class="panel panel-default card-view pa-0 bg-gradient">
            <div class="panel-wrapper collapse in">
                <div class="panel-body pa-0">
                    <div class="sm-data-box">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 text-center pl-0 pr-0 data-wrap-left">
                                    <span class="txt-light block counter"><span class="counter-anim">${widget_data}</span></span>
                                    <span class="weight-1500 block font-13 txt-light">${widget_title}</span>
                                </div>
                            </div>	
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
    return template;
}