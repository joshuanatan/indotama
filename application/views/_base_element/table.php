<div class="form-group">
  <?php if (isset($ctrl_model) && isset($excel_title)) : ?>
    <a href="<?php echo base_url(); ?>plugin/excel/get?ctrl_model=<?php echo $ctrl_model; ?>&title=<?php echo $excel_title; ?>" class='text-success'><i class='md-assignment-returned'></i> Download Excel</a><br /><br />
  <?php endif; ?>
  <h5>Search Data Here</h5>
  <input id="search_box" placeholder="Search data here..." type="text" class="form-control input-sm " onkeyup="search()" style="width:25%">
</div>
<nav aria-label="Page navigation example">
  <ul class="pagination justify-content-center pagination_container">
  </ul>
</nav>
<div class="table-responsive">
  <table class="table table-bordered table-striped" id="table_container">
    <thead id="col_title_container">
    </thead>
    <tbody id="content_container" class="content_container">
    </tbody>
  </table>
</div>
<nav aria-label="Page navigation example">
  <ul class="pagination justify-content-center pagination_container">
  </ul>
</nav>