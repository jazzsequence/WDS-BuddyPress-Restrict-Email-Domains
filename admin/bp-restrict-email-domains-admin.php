<?php

function bp_restrict_email_domains_admin() {
	global $bp;

	/* If the form has been submitted and the admin referrer checks out, save the settings */
	if ( isset( $_POST['submit'] ) && check_admin_referer('bp_restrict_email_domains_admin') ) {

		if ( $_POST['limited_email_domains'] != '' ) {

			$limited_email_domains = str_replace( ' ', "\n", $_POST['limited_email_domains'] );
			$limited_email_domains = split( "\n", stripslashes( $limited_email_domains ) );
			$limited_email = array();

			foreach ( (array) $limited_email_domains as $domain ) {
					$domain = trim( $domain );
				if ( ! preg_match( '/(--|\.\.)/', $domain ) && preg_match( '|^([a-zA-Z0-9-\.])+$|', $domain ) )
					$limited_email[] = trim( $domain );
			}

			update_site_option( 'limited_email_domains', $limited_email );

		} else {

			update_site_option( 'limited_email_domains', '' );

		}

		if ( $_POST['banned_email_domains'] != '' ) {

			$banned_email_domains = split( "\n", stripslashes( $_POST['banned_email_domains'] ) );
			$banned = array();

			foreach ( (array) $banned_email_domains as $domain ) {
				$domain = trim( $domain );
				if ( ! preg_match( '/(--|\.\.)/', $domain ) && preg_match( '|^([a-zA-Z0-9-\.])+$|', $domain ) )
					$banned[] = trim( $domain );
			}

			update_site_option( 'banned_email_domains', $banned );

		} else {

			update_site_option( 'banned_email_domains', '' );

		}

		$updated = true;
	}

?>
	<div class="wrap">
		<h2><?php _e( 'Restrict Email Domains', 'bp-restrict-email-domains' ); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings Updated.', 'bp-restrict-email-domains' ) . "</p></div>"; endif; ?>

		<form action="<?php echo site_url() . '/wp-admin/admin.php?page=bp-restrict-email-domains-settings' ?>" name="bp-restrict-email-domains-settings-form" id="bp-restrict-email-domains-settings-form" method="post">


		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="limited_email_domains"><?php _e( 'Limited Email Registrations' ) ?></label></th>
				<td>
					<?php $limited_email_domains = get_site_option( 'limited_email_domains' );
					$limited_email_domains = str_replace( ' ', "\n", $limited_email_domains ); ?>
					<textarea name="limited_email_domains" id="limited_email_domains" cols="45" rows="5"><?php echo wp_htmledit_pre( $limited_email_domains == '' ? '' : implode( "\n", (array) $limited_email_domains ) ); ?></textarea>
					<br />
					<?php _e( 'If you want to limit site registrations to certain domains. One domain per line.' ) ?>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="banned_email_domains"><?php _e('Banned Email Domains') ?></label></th>
				<td>
					<textarea name="banned_email_domains" id="banned_email_domains" cols="45" rows="5"><?php echo wp_htmledit_pre( get_site_option( 'banned_email_domains' ) == '' ? '' : implode( "\n", (array) get_site_option( 'banned_email_domains', 'buddypress' ) ) ); ?></textarea>
					<br />
					<?php _e( 'If you want to ban domains from site registrations. One domain per line.' ) ?>
				</td>
			</tr>

		</table>

			<?php wp_nonce_field( 'bp_restrict_email_domains_admin' ); ?>

			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>


		</form>


	</div>
<?php
}

?>