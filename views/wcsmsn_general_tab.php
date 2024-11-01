<div class="wcsmsn_wrapper cvt-accordion" style="padding: 5px 10px 10px 10px;">
	<strong><?php _e( $wcsmsn_helper, wcsmsnConstants::TEXT_DOMAIN ); ?></strong>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Username',wcsmsnConstants::TEXT_DOMAIN); ?>
				<span class="tooltip" data-title="Enter your freebulksmsonline.com account Username"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td style="vertical-align: top;">
				<?php if($islogged){echo $wcsmsn_name;}?>
				<input type="text" name="wcsmsn_gateway[wcsmsn_name]" id="wcsmsn_gateway[wcsmsn_name]" value="<?php echo $wcsmsn_name; ?>" data-id="wcsmsn_name" class="<?php echo $hidden?>">
				<input type="hidden" name="action" value="save_wcsmsn_settings" />
				<span class="<?php echo $hidden?>"><?php _e( 'your freebulksmsonline.com account Username', wcsmsnConstants::TEXT_DOMAIN ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Token', wcsmsnConstants::TEXT_DOMAIN ) ?>
				<span class="tooltip" data-title="Enter freebulksmsonline.com account token"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td>
				<?php if($islogged){echo '*****';}?>
				<input type="text" name="wcsmsn_gateway[wcsmsn_password]" id="wcsmsn_gateway[wcsmsn_password]" value="<?php echo $wcsmsn_password; ?>" data-id="wcsmsn_password" class="<?php echo $hidden?>">
				<span class="<?php echo $hidden?>"><?php _e( 'your freebulksmsonline.com account token', wcsmsnConstants::TEXT_DOMAIN ); ?></span>
			</td>
		</tr>
		<?php do_action('verify_senderid_button')?>
		<tr valign="top">
			<th scope="row">
				<?php _e( 'User Type', wcsmsnConstants::TEXT_DOMAIN ) ?>
				<span class="tooltip" data-title="Will autofill after token validation"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td>
				<?php if($islogged){?>
					<?php echo $wcsmsn_api;?>
					<input type="hidden" value="<?php echo $wcsmsn_api;?>" name="wcsmsn_gateway[wcsmsn_api]" id="wcsmsn_gateway[wcsmsn_api]">
				<?php }else{?>
				<select name="wcsmsn_gateway[wcsmsn_api]" id="wcsmsn_gateway[wcsmsn_api]" disabled>
					<option value="SELECT"><?php _e( 'SELECT', wcsmsnConstants::TEXT_DOMAIN ); ?></option>
				</select>
				<span class="<?php echo $hidden?>"><?php _e( 'user account type', wcsmsnConstants::TEXT_DOMAIN ); ?></span>
				<?php } ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
			</th>
			<td>
				<?php if($islogged){?>
				<a href="#" class="button-primary" onclick="logout(); return false;"><?php echo _e( 'Logout', wcsmsnConstants::TEXT_DOMAIN );?></a>
				<?php }?>
			</td>
		</tr>
	</table>
</div>
<br>
<?php if($islogged){  ?>
<?php if($hasWoocommerce || $hasWPAM || $hasEMBookings){?>
<div class="cvt-accordion" style="padding: 0px 10px 10px 10px;">
	<table class="form-table">
		<?php if($hasWoocommerce || $hasWPAM || $hasEMBookings){?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Send Admin SMS To', wcsmsnConstants::TEXT_DOMAIN ) ?>
				<span class="tooltip" data-title="Please make sure that the number must be without country code (e.g.: 8010551055)"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td>
				<select id="send_admin_sms_to" onchange="toggle_send_admin_alert(this);">
					<option value=""><?php _e( 'Custom', wcsmsnConstants::TEXT_DOMAIN ) ?></option>
					<option value="post_author" <?php echo (trim($sms_admin_phone) == 'post_author') ? 'selected="selected"' : ''; ?>><?php _e( 'Post Author', wcsmsnConstants::TEXT_DOMAIN ) ?></option>
				</select>
				<script>
				function toggle_send_admin_alert(obj)
				{
					if(obj.value == "post_author")
					{
						tagInput1.addTag(obj.value);
					}
				}
				</script>
				<input type="text" name="wcsmsn_message[sms_admin_phone]" class="admin_no" id="wcsmsn_message[sms_admin_phone]" <?php echo (trim($sms_admin_phone) == 'post_author') ? 'readonly="readonly"' : ''; ?> value="<?php echo $sms_admin_phone; ?>"><br /><br />
				<span><?php _e( 'Admin order sms notifications will be send in this number.', wcsmsnConstants::TEXT_DOMAIN ); ?></span>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
<?php } } ?>