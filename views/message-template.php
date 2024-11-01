<?php
add_thickbox();
$url = add_query_arg( array(
    'action'    => 'foo_modal_box',
	'TB_iframe' => 'true',
    'width'     => '800',
    'height'    => '500',
), admin_url( 'admin.php?page=all-order-variable' ) );
?>
<!-- Admin-accordion -->
<div class="cvt-accordion"><!-- cvt-accordion -->
	<div class="accordion-section">
		<?php foreach($templates as $template ){ ?>
		<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $checkTemplateFor; ?>_<?php echo $template['status']; ?>">
			<input type="checkbox" name="<?php echo $template['checkboxNameId']; ?>" id="<?php echo $template['checkboxNameId']; ?>" class="notify_box" <?php echo ($template['enabled']=='on')?"checked='checked'":''; ?> <?php echo (!empty($template['chkbox_val']))?"value='".$template['chkbox_val']."'":''; ?>  /><label><?php _e( $template['title'], wcsmsnConstants::TEXT_DOMAIN ) ?></label>
			<span class="expand_btn"></span>
		</a>
		<div id="accordion_<?php echo $checkTemplateFor; ?>_<?php echo $template['status']; ?>" class="cvt-accordion-body-content">
			<table class="form-table">
				<tr valign="top">
					<td>
						<div class="wcsmsn_tokens">
							<?php
							foreach($template['token'] as $vk => $vv)
							{
								echo sprintf( "<a href='#' val='%s'>%s</a> | " , $vk , __($vv,wcsmsnConstants::TEXT_DOMAIN) );
							}
							?>
							<?php if($checkTemplateFor =='wc_admin' || $checkTemplateFor == 'wc_customer'){?>
							<a href="<?php echo $url; ?>" class="thickbox search-token-btn">[...More]</a>
							<?php } ?>
						</div>
						<textarea name="<?php echo $template['textareaNameId']; ?>" id="<?php echo $template['textareaNameId']; ?>" data-parent_id="<?php echo $template['checkboxNameId']; ?>" <?php echo(($template['enabled']=='on')?'' : "readonly='readonly'"); ?>><?php echo $template['text-body'] ?></textarea>
					</td>
				</tr>
			</table>
		</div>
		<?php } ?>
	</div>
</div>
<!-- /-cvt-accordion -->

<!-- Delivery driver -->
<?php if($checkTemplateFor == 'delivery_drivers'){?>
	<div class="submit">
	<a href="users.php?role=driver" class="button action alignright"><?php _e( 'View Drivers', wcsmsnConstants::TEXT_DOMAIN ) ?></a>
	</div>
<?php } ?>
<!-- /- Delivery driver -->
<!-- Backinstock -->
<?php if($checkTemplateFor == 'backinstock'){?>
	<div class="submit">
		<a href="admin.php?page=all-subscriber" class="button action alignright"><?php _e( 'View Subscriber', wcsmsnConstants::TEXT_DOMAIN ) ?></a>
	</div>
<?php } ?>
<!-- /- Backinstock -->
<!-- Cartbounty -->
<?php
if($checkTemplateFor == 'cartbounty'){
	$options = get_option( 'cartbounty_notification_frequency' );
	if($options['hours'] == 0) {
?>
<br>
<div class="cvt-accordion" style="padding: 0px 10px 10px 10px;">
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<td>
				<p><span class="dashicons dashicons-info"></span> <b><?php _e( 'Please enable Email Notification at Cart Bounty Setting page.', wcsmsnConstants::TEXT_DOMAIN ) ?></b> <a href="<?php echo admin_url().'admin.php?page=cartbounty&tab=settings'?>"><?php _e( 'Click Here', wcsmsnConstants::TEXT_DOMAIN ) ?></a></p>
			</td>
		</tr>
	</tbody></table>
</div>
<?php } } ?>
<!-- -/ Cartbounty -->