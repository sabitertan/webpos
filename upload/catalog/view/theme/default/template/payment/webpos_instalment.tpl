<?php if ($banks) { ?>
<div class="col-sm-12">
<div class="radio">
  <label>
    <input type="radio" name="instalment" value="<?php echo $webpos_other_id.'_0x'.$single_order_total; ?>" checked="checked"/>
    <?php echo $text_no_instalment.$webpos_single_title.$single_order_total; ?>
  </label>
</div>
<h3><?php echo $text_instalments; ?></h3>
</div>
<?php foreach ($banks as $bank) { 
if(!empty($bank['instalment']) || $bank['instalment']!=''){
?>

<div class="col-sm-3">
				<?php if (!empty($bank['image'])) {?>
							<img src="<?php echo $bank['image']; ?>"/>
				<?php } else { ?>
							<strong><?php echo $bank['name']; ?></strong>
				<?php } ?>

<?php 
	
		foreach($bank['instalments'] as $instalment) { ?>
	<div class="radio">
		<label>
			<input type="radio" name="instalment" value="<?php echo $bank['bank_id'].'_'.$instalment['count'].'x'.$instalment['price'].'_'.$instalment['ratio']; ?>" />
		<?php echo $instalment['count'].$text_instalment.$instalment['total'].'('.$instalment['count'].'x'.$instalment['price'].')'; ?>
		</label>
	</div>
<?php 	}
	} ?>
</div>
<?php } ?>
<?php } ?>

