<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-webpos" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-webpos" class="form-horizontal">
		          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
			<?php foreach ($banks as $tab_bank) { ?>
            <li><a href="#tab-bank-<?php echo $tab_bank['bank_id']; ?>" data-toggle="tab"><?php echo $tab_bank['name']; ?></a></li>
            <?php } ?>
          </ul>
		<div class="tab-content">
         <div class="tab-pane active in" id="tab-general">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-mode"><?php echo $entry_mode; ?></label>
            <div class="col-sm-10">
              <select name="webpos_mode" id="input-mode" class="form-control">
                <?php if ($webpos_mode == 'live') { ?>
                <option value="live" selected="selected"><?php echo $text_live; ?></option>
                <?php } else { ?>
                <option value="live"><?php echo $text_live; ?></option>
                <?php } ?>
                <?php if ($webpos_mode == 'test') { ?>
                <option value="test" selected="selected"><?php echo $text_test; ?></option>
                <?php } else { ?>
                <option value="test"><?php echo $text_test; ?></option>
                <?php } ?>
				<?php if ($webpos_mode == 'debug') { ?>
                <option value="debug" selected="selected"><?php echo $text_debug; ?></option>
                <?php } else { ?>
                <option value="debug"><?php echo $text_debug; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		  
		  
		  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-other"><?php echo $entry_other; ?></label>
            <div class="col-sm-10">
              <select name="webpos_other_id" id="input-other" class="form-control">
                <?php foreach ($banks as $bank) { ?>
                <?php if ($bank['bank_id'] == $webpos_other_id) { ?>
                <option value="<?php echo $bank['bank_id']; ?>" selected="selected"><?php echo $bank['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $bank['bank_id']; ?>"><?php echo $bank['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
		  
		  
		  
           <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="webpos_total" value="<?php echo $webpos_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
            <div class="col-sm-10">
              <select name="webpos_order_status_id" id="input-order-status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $webpos_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
            <div class="col-sm-10">
              <select name="webpos_geo_zone_id" id="input-geo-zone" class="form-control">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $webpos_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="webpos_status" id="input-status" class="form-control">
                <?php if ($webpos_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="webpos_sort_order" value="<?php echo $webpos_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
		 </div>
		 <!-- banks start-->
		 			<?php foreach ($banks as $tab_bank) { ?>
					<div class="tab-pane" id="tab-bank-<?php echo $tab_bank['bank_id']; ?>">
					<input type="hidden" name="webpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][bank_id]" value="<?php echo $tab_bank['bank_id']; ?>" />
					<input type="hidden" name="webpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][name]" value="<?php echo $tab_bank['name']; ?>" />
					<input type="hidden" name="webpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][method]" value="<?php echo $tab_bank['method']; ?>" />
					<input type="hidden" name="webpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][model]" value="<?php echo $tab_bank['model']; ?>" />
					<input type="hidden" name="webpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][status]" value="<?php echo $tab_bank['status']; ?>" />
					<?php echo $tab_bank['name'].' , '.$tab_bank['method'].' , '.$tab_bank['model'].' , '.$tab_bank['status']; 
					foreach ($tab_bank['entries'] as $entry=>$value) { ?>
					
			<div class="form-group">
            <label class="col-sm-2 control-label" for="input-<?php echo $entry; ?>"><?php echo ${'entry_'.$entry}; ?></label>
            <div class="col-sm-10">
              <input type="text" name="webpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][<?php echo $entry; ?>]" value="<?php echo $value; ?>" placeholder="<?php echo $entry; ?>" id="input-<?php echo $entry; ?>" class="form-control" />
            </div>
          </div>
					<?php } ?>
		 </div>
            <?php } ?>
		 
		 <!-- banks end-->
         </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>