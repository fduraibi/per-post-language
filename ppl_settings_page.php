<?php

// Add settings page entry under WordPress default Settings menu
add_action( 'admin_menu', 'ppl_settings_menu' );
function ppl_settings_menu() {
	add_options_page(
		esc_html__('Settings') . ' - Per Post Language ',
		'Per Post Language',
		'manage_options',
		'ppl_settings_page',
		'ppl_settings_page'
	);
}

// The settings page for the plugin
function ppl_settings_page(){
	?>
	<div class="wrap">
		<h1><?php esc_html_e('Settings'); ?> - Per Post Language</h1>
		<?php
		if( isset( $_POST['submit'] ) ) {
			if( ! isset( $_POST['_ppl_nonce'] ) || ! wp_verify_nonce( $_POST['_ppl_nonce'], 'updating_language_settings' )) {
				echo '<div class="error settings-error notice is-dismissible"><p><strong>';
				esc_html_e('Error: Could not validate the data integrity!', 'perpostlanguage');
				echo '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__('Dismiss this notice.') . '</span></button></div>';
			} else {
				$pplSaveLanguages = array();
				foreach ($_POST as $key => $value) {
					if ( substr( $key, 0, 8) == 'pplLang:' ) {
						$pplSaveLanguages[substr( $key, 8)] = $value;
					}
				}
				update_option( "ppl_options", $pplSaveLanguages);
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>';
				esc_html_e('Settings saved.');
				echo '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__('Dismiss this notice.') . '</span></button></div>';
			}
		}
		?>
		<p><?php esc_html_e('Select the language that you like to be available for your posts from the menu:', 'perpostlanguage'); ?></p>
		<table class="form-table"><tbody>
			<tr>
			<th style="padding: 0 10px 0 0;"><label for="langList"><h2><?php esc_html_e('Available languages', 'perpostlanguage'); ?></h2></label></th>
			<td>
				<select id="langList">
					<option value="en_US">English (United States)</option>
					<?php
					require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
					$langs = wp_get_available_translations();
					foreach ($langs as $key => $value) {
						?><option value="<?php echo $value['language']; ?>"><?php echo $value['native_name']; ?></option><?php
					}
				?></select>
				<span class="button" onClick="pplAddNewLang()"><?php esc_html_e('Add Language', 'perpostlanguage'); ?></span>
			</td>
			</tr>
			<tr>
				<th style="padding: 0 10px 0 0;"><h2><?php esc_html_e('Selected languages:', 'perpostlanguage'); ?></h2></th>
			</tr>
		</tbody></table>
		<?php
		$pplOptions=get_option("ppl_options");
		?><form method="POST" action="">
			<table id="langTable"><tbody>
				<?php
				if( $pplOptions != false ) {
					foreach ($pplOptions as $key => $value) {
						if ( $key != 'en_US' ) {
							$downloadResult = wp_download_language_pack( $key  );
							if ( $downloadResult == false ) {
								if ( wp_can_install_language_pack() == true ) {
									echo '<div class="error settings-error notice is-dismissible"><p><strong>';
									esc_html_e('Error: Could not download language files for', 'perpostlanguage');
									echo ' ' . $value . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__('Dismiss this notice.') . '</span></button></div>';
								} else {
									echo '<div class="error settings-error notice is-dismissible"><p><strong>';
									esc_html_e('Error: Could not save language files for', 'perpostlanguage');
									echo ' ' . $value . ', ';
									esc_html_e('check the language folder write permission.', 'perpostlanguage');
									echo '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__('Dismiss this notice.') . '</span></button></div>';
								}
							}
						}
						?><tr>
							<td style="text-align: right; padding: 5px 10px;"><span class="button" onclick="pplRemoveLang(this)"><?php esc_html_e('Remove Language', 'perpostlanguage'); ?></span></td>
							<td style="padding: 5px 10px;"><input type="text" name="pplLang:<?php echo $key; ?>" value="<?php echo $value; ?>" readonly style="background-color: white;"></td>
						</tr><?php
					}
				}
				?>
			</tbody></table>

			<p class="submit">
				<?php wp_nonce_field( 'updating_language_settings', '_ppl_nonce' ); ?>
				<input name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Save Changes'); ?>" type="submit">
				<br />
				<strong><?php esc_html_e('* When saving new languages their translation files will be downloaded from wordpress.org if they do not exist.', 'perpostlanguage'); ?></strong>
			</p>
		</form>
	</div>
	<script>
		function pplAddNewLang() {
			var langList = document.getElementById("langList");
			var key = langList.options[langList.selectedIndex].value;
			var value = langList.options[langList.selectedIndex].text;

			var table = document.getElementById("langTable");
			var row = table.insertRow(table.rows.length);
			var cell1 = row.insertCell(0);
			var cell2 = row.insertCell(1);
			
			cell1.innerHTML = '<span class="button" onclick="pplRemoveLang(this)"><?php esc_html_e('Remove Language', 'perpostlanguage'); ?></span>';
			cell1.style = 'text-align: right; padding: 5px 10px;';
			cell2.innerHTML = '<input type="text" name="pplLang:' + key + '" value="' + value + '" readonly style="background-color: white;">';
			cell2.style = 'padding: 5px 10px;';
		}

		function pplRemoveLang(rowRef) {
			var pNode=rowRef.parentNode.parentNode;
			pNode.parentNode.removeChild(pNode);
		}
	</script>
<?php
}
?>
