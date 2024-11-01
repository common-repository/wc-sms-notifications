<div id="cf7si-sms-sortables" class="meta-box-sortables ui-sortable">
	<h3><?php _e("Admin SMS Notifications"); ?></h3>
	<fieldset>
		<legend><?php _e("In the following fields, you can use these tags:"); ?>
			<br />
			<?php $data['form']->suggest_mail_tags(); ?>
		</legend>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wpcf7-sms-recipient"><?php _e("To:"); ?></label>
					</th>
					<td>
						<input type="text" id="wpcf7-sms-recipient" name="wpcf7wcsmsn-settings[phoneno]" class="wide" size="70" value="<?php echo $data['phoneno']; ?>">
						<br/> <?php _e("<small>Enter Numbers By <code>,</code> for multiple</small>"); ?>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcf7-mail-body"><?php _e("Message body:"); ?></label>
					</th>
					<td>
						<textarea id="wpcf7-mail-body" name="wpcf7wcsmsn-settings[text]" cols="100" rows="6" class="large-text code"><?php echo trim($data['text']); ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	
	<hr/>
	<h3><?php _e("Visitor SMS Notifications"); ?></h3>
	<fieldset>
		<legend><?php _e("In the following fields, you can use these tags:"); ?>
			<br />
			<?php $data['form']->suggest_mail_tags(); ?>
		</legend>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wpcf7-mail-body"><?php _e("Visitor Mobile::"); ?></label>
					</th>
					<td>
						<select name="wpcf7wcsmsn-settings[visitorNumber]" id="visitorNumber">
						<?php
						$wpcf7 = WPCF7_ContactForm::get_current();
						$ContactForm = WPCF7_ContactForm::get_instance( $wpcf7->id() );
                        $form_fields = $ContactForm->scan_form_tags();
						if(!empty($form_fields))
						{
							foreach($form_fields as $form_field)
							{
								$field = json_decode(json_encode($form_field), true);
								if($field['name']!='')
								{
							?>
							<option value="<?php echo '['.$field['name'].']'; ?>" <?php echo (@$data['visitorNumber'] == '['.$field['name'].']') ? 'selected="selected"' : ''; ?>><?php echo $field['name']; ?></option>
							<?php
								}
							}
						}
						?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcf7-mail-body"><?php _e("Message body:"); ?></label>
					</th>
					<td>
						<textarea id="wpcf7-mail-body" name="wpcf7wcsmsn-settings[visitorMessage]" cols="100" rows="6" class="large-text code"><?php echo @$data['visitorMessage']; ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	
	<!--Group Sync-->
		<hr/>
	<h3><?php _e("Create Contacts in WC SMS Notifications Group"); ?></h3>
	<fieldset>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wpcf7-mail-body"><?php _e("Add To Group:"); ?></label>
					</th>
					<td>
						<select name="wpcf7wcsmsn-settings[wcsmsn_group]" id="wcsmsn_group">
						<?php
						 $groups = json_decode(wcsmsncURLOTP::group_list(),true);
						if(!is_array($groups['description']) || array_key_exists('desc', $groups['description']))
						{
							?>
							<option value="0"><?php _e( 'SELECT', wcsmsnConstants::TEXT_DOMAIN ) ?></option>
							<?php
						}
						else
						{
							foreach($groups['description'] as $group)
							{
							?>
							<option value="<?php echo $group['Group']['name']; ?>" <?php echo (@$data['wcsmsn_group'] == $group['Group']['name']) ? 'selected="selected"' : ''; ?>><?php echo $group['Group']['name']; ?></option>
							<?php
							}
						}
						?>
						</select>
						<?php
						if(!empty($groups) && (!is_array($groups['description']) || array_key_exists('desc', $groups['description'])))
						{
						?>
							<a href="javascript:void(0)" onclick="create_group(this);" id="create_group" style="text-decoration: none;"><?php _e( 'Create Group', wcsmsnConstants::TEXT_DOMAIN ) ?></a>
						<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpcf7-mail-body"><?php _e("Name Field:"); ?></label>
					</th>
					<td>
						<select name="wpcf7wcsmsn-settings[wcsmsn_name]" id="wcsmsn_name">
						<?php
						 $username 		= wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
		                 $password 		= wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
						
						$wpcf7 = WPCF7_ContactForm::get_current();
						$ContactForm = WPCF7_ContactForm::get_instance( $wpcf7->id() );
                        $form_fields = $ContactForm->scan_form_tags();
						if(!empty($form_fields))
						{
							foreach($form_fields as $form_field)
							{
								$field = json_decode(json_encode($form_field), true);
								if($field['name']!='')
								{
							?>
							<option value="<?php echo '['.$field['name'].']'; ?>" <?php echo (@$data['wcsmsn_name'] == '['.$field['name'].']') ? 'selected="selected"' : ''; ?>><?php echo $field['name']; ?></option>
							<?php
								}
							}
						}
						?>
						<input type="hidden" name="wcsmsn_gateway[wcsmsn_name]" id="wcsmsn_gateway[wcsmsn_name]" value="<?php echo $username; ?>" data-id="wcsmsn_name" class="hidden">
						<input type="hidden" name="wcsmsn_gateway[wcsmsn_password]" id="wcsmsn_gateway[wcsmsn_password]" value="<?php echo $password; ?>" data-id="wcsmsn_password" class="hidden">
						</select>
					</td>
				</tr>
				
			</tbody>
		</table>
	</fieldset>
	<!--/-Group Sync-->
	
</div>