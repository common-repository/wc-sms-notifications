<?php
$current_user_id = get_current_user_id();
$phone = (get_user_meta($current_user_id,'billing_phone',true) != '')?get_user_meta($current_user_id,'billing_phone',true) : '';
?>
<section class="wcsmsn_instock-subscribe-form wcsmsn_instock-subscribe-form-<?php echo $variation_id;?>">
	<div class="panel panel-primary wcsmsn_instock-panel-primary">
		<form class="panel-body">
			<div class="row">
				<fieldset>
				<div class="col-md-12">
					<div class="panel-heading wcsmsn_instock-panel-heading">
						<h4 style="text-align: center;padding:10px;color:currentColor">	
							<?php _e( 'Message when stock available', wcsmsnConstants::TEXT_DOMAIN ) ?>
						</h4>
					</div>
					<div class="col-md-12">
					<div class="form-row">
						<input type="text" class="input-text phone-valid" id="wcsmsn_bis_phone" name="wcsmsn_bis_phone_phone" placeholder="<?php _e( 'Enter Phone Number', wcsmsnConstants::TEXT_DOMAIN ) ?>" value="<?php echo $phone ;?>"/>
					</div>
					<input type="hidden" id="sa-product-id" name="sa-product-id" value="<?php echo $product_id; ?>"/>
					<input type="hidden" id="sa-variation-id" name="sa-variation-id" value="<?php echo $variation_id; ?>"/>
					
					<div class="form-group center-block" style="text-align:center;margin-top:10px">
						<input type="submit" id="wcsmsn_bis_submit" name="wcsmsn_submit" class="button" value="Subscribe" style="width:100%"/>
					</div>

					<div class="sastock_output"></div>
					<script>
					jQuery(document).off('click').on('click', '#wcsmsn_bis_submit', function () {
							var self = this;
							jQuery(self).val('Please wait....').attr( 'disabled', 'disabled' );
							var phone_number = jQuery('#wcsmsn_bis_phone').val();
							var product_id = jQuery('#sa-product-id').val();
							var var_id = jQuery('#sa-variation-id').val();
							
								var data = {
									product_id: product_id,
									variation_id: var_id,
									user_phone: phone_number,
									action: 'wcsmsnbackinstock'
								};
								jQuery.ajax({
									type: 'post',
									data: data,
									success: function (msg) {
									var r= jQuery.parseJSON(msg);
									jQuery(self).val('Subscribe');	jQuery('.sastock_output').html(r.description).fadeIn().delay(3000).fadeOut();
									
									jQuery(self).removeAttr( 'disabled', 'disabled' )
									
									},
									error: function (request, status, error) {
										
										var r= jQuery.parseJSON(msg);
										jQuery(self).val('Subscribe');	jQuery('.sastock_output').html(r.description).fadeIn().delay(3000).fadeOut();
									}
								});
							
							return false;
						});
					</script>
					</div>
				</div>
				</fieldset>
			</div>
			<!-- End ROW -->
		</form>
	</div>
</section>
<script>
jQuery(".single_variation_wrap").on("show_variation", function (event, variation) {
	// Fired when the user selects all the required dropdowns / attributes
	// and a final variation is selected / shown
	var vid = variation.variation_id;
	jQuery('.wcsmsn_instock-subscribe-form').hide(); //remove existing form
	jQuery('.wcsmsn_instock-subscribe-form-' + vid).show(); //add subscribe form to show
});
</script>