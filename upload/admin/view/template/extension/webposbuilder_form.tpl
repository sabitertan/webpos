<?php echo $header; ?><?php echo $column_left; ?>
<?php $pos_methods=array('nestpay', 'gvp', 'posnet', 'boa', 'get724', 'payflex', 'payu', 'ipara'); ?>
<?php $pos_models=array('classic', '3d_model', '3d_pay', '3d_hosting', 'hosting'); ?>
<div id="content">
<div class="page-header">
<div class="container-fluid">
<div class="pull-right">
<button type="submit" form="form-slider" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h3>
</div>
<div class="panel-body">
<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-slider" class="form-horizontal">
<div class="form-group required">
<label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
<div class="col-sm-10">
<input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
<?php if ($error_name) { ?>
	<div class="text-danger"><?php echo $error_name; ?></div>
	<?php } ?>
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label" for="input-image"><?php echo $entry_image; ?></label>
<div class="col-sm-10">
<a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?php echo $thumb; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
<input type="hidden" name="image" value="<?php echo $image; ?>" id="input-image"/>
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
<div class="col-sm-10">
<select name="status" id="input-status" class="form-control">
<?php if ($status) { ?>
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
<label class="col-sm-2 control-label" for="input-method"><?php echo $entry_method; ?></label>
<div class="col-sm-10">
<select name="method" id="input-method" class="form-control">
<?php foreach($pos_methods as $method_item) { 

if ($method_item==$method) { ?>
	<option value="<?php echo $method_item; ?>" selected="selected"><?php echo $method_item; ?></option>
	<?php } else { ?>
	<option value="<?php echo $method_item; ?>"><?php echo $method_item; ?></option>
	<?php } }?>
</select>
<?php if ($error_method) { ?>
	<div class="text-danger"><?php echo $error_method; ?></div>
	<?php } ?>
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label" for="input-model"><?php echo $entry_model; ?></label>
<div class="col-sm-10">
<select name="model" id="input-model" class="form-control">
<?php foreach($pos_models as $model_item) { 

if ($model_item==$model) { ?>
	<option value="<?php echo $model_item; ?>" selected="selected"><?php echo $model_item; ?></option>
	<?php } else { ?>
	<option value="<?php echo $model_item; ?>"><?php echo $model_item; ?></option>
	<?php } }?>
</select>
<?php if ($error_model) { ?>
	<div class="text-danger"><?php echo $error_model; ?></div>
	<?php } ?>
</div>
</div>
<div class="form-group">
<label class="col-sm-2 control-label" for="input-short"><?php echo $entry_short; ?></label>
<div class="col-sm-10">
<input type="text" name="short" value="<?php echo $short; ?>" placeholder="<?php echo $entry_short; ?>" id="input-short" class="form-control" />
<?php if ($error_short) { ?>
	<div class="text-danger"><?php echo $error_short; ?></div>
	<?php } ?>
</div>
</div>



</form>
</div>
</div>
</div>
<?php echo $footer; ?>
