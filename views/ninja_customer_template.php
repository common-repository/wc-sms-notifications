<?php
$ninja_forms = wcsmsnNinjaForms::get_ninja_forms(); 
if(!empty($ninja_forms)){ 
?>
<!-- accordion -->	
<div class="cvt-accordion">
	<div class="accordion-section">
	<?php foreach($ninja_forms as $ks => $vs) { ?>		
		<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>">
			<input type="checkbox" name="wcsmsn_ninja_general[ninja_order_status_<?php echo $vs; ?>]" id="wcsmsn_ninja_general[ninja_order_status_<?php echo $vs; ?>]" class="notify_box" <?php echo ((wcsmsn_get_option( 'ninja_order_status_'.$vs, 'wcsmsn_ninja_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e( ucwords(str_replace('-', ' ', $vs )), wcsmsnConstants::TEXT_DOMAIN ) ?></label>
			<span class="expand_btn"></span>
		</a>		 
		<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
			<table class="form-table">
			    <tr>
					<td><input data-parent_id="wcsmsn_ninja_general[ninja_order_status_<?php echo $vs; ?>]" type="checkbox" name="wcsmsn_ninja_general[ninja_message_<?php echo $vs; ?>]" id="wcsmsn_ninja_general[ninja_message_<?php echo $vs; ?>]" class="notify_box" <?php echo ((wcsmsn_get_option( 'ninja_message_'.$vs, 'wcsmsn_ninja_general', 'on')=='on')?"checked='checked'":''); ?>/><label>Enable Message</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<div class="wcsmsn_tokens"><?php echo wcsmsnNinjaForms::getNinjavariables($ks); ?></div>
						<textarea data-parent_id="wcsmsn_ninja_general[ninja_message_<?php echo $vs; ?>]" name="wcsmsn_ninja_message[ninja_sms_body_<?php echo $vs; ?>]" id="wcsmsn_ninja_message[ninja_sms_body_<?php echo $vs; ?>]" <?php echo((wcsmsn_get_option( 'ninja_order_status_'.$vs, 'wcsmsn_ninja_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php echo wcsmsn_get_option('ninja_sms_body_'.$vs, 'wcsmsn_ninja_message', sprintf(__('Hello %s, Thank you for contacting us.',wcsmsnConstants::TEXT_DOMAIN), '[name]')); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						Select Phone Field : <select name="wcsmsn_ninja_general[ninja_sms_phone_<?php echo $vs; ?>]">
						<?php
						$fields = wcsmsnNinjaForms::getNinjavariables($ks,true);
						?>
						<option value="">--select field--</option>
						<?php
						foreach($fields as $field)
						{
						   if(!is_array($field))
						   {
							?>
							<option value="<?php echo $field; ?>" <?php echo (trim(wcsmsn_get_option( 'ninja_sms_phone_'.$vs, 'wcsmsn_ninja_general', '')) == $field) ? 'selected="selected"' : ''; ?> ><?php echo $field; ?></option>
							<?php
						   }
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td><input data-parent_id="wcsmsn_ninja_general[ninja_order_status_<?php echo $vs; ?>]" type="checkbox" name="wcsmsn_ninja_general[ninja_otp_<?php echo $vs; ?>]" id="wcsmsn_ninja_general[ninja_otp_<?php echo $vs; ?>]" class="notify_box" <?php echo ((wcsmsn_get_option( 'ninja_otp_'.$vs, 'wcsmsn_ninja_general', 'on')=='on')?"checked='checked'":''); ?>/><label>Enable Mobile Verification</label>
					</td>
				</tr>
			</table>
		</div>
	<?php } ?>	
	</div>
</div>
<!--end accordion-->
<?php 
}else{
	echo "<h3>No Form publish</h3>";
}
?>		