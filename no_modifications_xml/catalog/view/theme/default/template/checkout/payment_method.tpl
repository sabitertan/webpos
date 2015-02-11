<?php if ($error_warning) { ?>
<div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($payment_methods) { ?>
<p><?php echo $text_payment_method; ?></p>
<?php foreach ($payment_methods as $payment_method) { ?>
<div class="radio">
  <label>
    <?php if ($payment_method['code'] == $code || !$code) { ?>
    <?php $code = $payment_method['code']; ?>
    <input type="radio" name="payment_method" value="<?php echo $payment_method['code']; ?>" checked="checked" />
    <?php } else { ?>
    <input type="radio" name="payment_method" value="<?php echo $payment_method['code']; ?>" />
    <?php } ?>
    <?php echo $payment_method['title']; ?>
    <?php if ($payment_method['terms']) { ?>
    (<?php echo $payment_method['terms']; ?>)
    <?php } ?>
  </label>
</div>
<?php } ?>
<!-- webpos -->
<div id="instalments" class="row" style="display: none;"></div>
<!-- webpos -->
<?php } ?>
<p><strong><?php echo $text_comments; ?></strong></p>
<p>
  <textarea name="comment" rows="8" class="form-control"><?php echo $comment; ?></textarea>
</p>
<?php if ($text_agree) { ?>
<div class="buttons">
  <div class="pull-right"><?php echo $text_agree; ?>
    <?php if ($agree) { ?>
    <input type="checkbox" name="agree" value="1" checked="checked" />
    <?php } else { ?>
    <input type="checkbox" name="agree" value="1" />
    <?php } ?>
    &nbsp;
    <input type="button" value="<?php echo $button_continue; ?>" id="button-payment-method" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" />
  </div>
</div>
<?php } else { ?>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_continue; ?>" id="button-payment-method" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" />
  </div>
</div>
<?php } ?>
<!--  @TODO:move this code to modification install.xml //webpos start  -->
<script type="text/javascript"><!--
$(document).ready(function () {
	//$('input[name=payment_method]').parent().parent().parent().first('p').after('<div id="instalments" style="display: none;"></div>');
	
	//
	  $.ajax({
        url: 'index.php?route=payment/webpos/instalments', 
        type: 'get',
		dataType: 'html',
        success: function(html) {
			$('div#instalments').html(html);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
	//
    var payment_check = $("input[type=radio][name=payment_method]:checked").val();
		if (payment_check=='webpos') {
			$('div#instalments').css('display','block');
		} else {
			$('div#instalments').hide();		
		}
	$("input[name=payment_method]").click(function(){
		var payment_check=$(this).val();
		if (payment_check=='webpos') {
			$('div#instalments').css('display','block');
		} else {
			$('div#instalments').hide();		
		}
	});
});
//--></script> 
<!-- webpos end-->
