<?php 
$ninja_forms = wcsmsnNinjaForms::get_ninja_forms();
if(!empty($ninja_forms)){
?>
<div class="cvt-accordion">
	<div class="accordion-section">			      
	<?php foreach($ninja_forms as $ks => $vs){ ?>		
		<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>">
			<input type="checkbox" name="wcsmsn_ninja_general[ninja_admin_notification_<?php echo $vs; ?>]" id="wcsmsn_ninja_general[ninja_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((wcsmsn_get_option( 'ninja_admin_notification_'.$vs, 'wcsmsn_ninja_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e(ucwords(str_replace('-', ' ', $vs )), wcsmsnConstants::TEXT_DOMAIN ) ?></label>
			<span class="expand_btn"></span>
		</a>		 
		<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
			<table class="form-table">
				<tr valign="top">
				<td><div class="wcsmsn_tokens"><?php echo wcsmsnNinjaForms::getNinjavariables($ks); ?></div>
				<textarea data-parent_id="wcsmsn_ninja_general[ninja_admin_notification_<?php echo $vs; ?>]" name="wcsmsn_ninja_message[ninja_admin_sms_body_<?php echo $vs; ?>]" id="wcsmsn_ninja_message[ninja_admin_sms_body_<?php echo $vs; ?>]" <?php echo((wcsmsn_get_option( 'ninja_admin_notification_'.$vs, 'wcsmsn_ninja_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php echo wcsmsn_get_option('ninja_admin_sms_body_'.$vs, 'wcsmsn_ninja_message', sprintf(__('Dear admin, %s has submitted a form.',wcsmsnConstants::TEXT_DOMAIN), '[name]'));?></textarea>
				</td>
				</tr>
			</table>
		</div>
	<?php } ?>
	</div>	
</div>
<?php 
}else{
	echo "<h3>No Form publish</h3>";
}
?>	