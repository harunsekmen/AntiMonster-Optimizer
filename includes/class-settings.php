<?php
/**
 * Admin Settings functionality
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class AntiMonster_Optimizer_Settings {

	private $options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_init', array( $this, 'handle_export_import' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles_scripts' ) );
	}

	public function admin_styles_scripts( $hook ) {
		if ( 'settings_page_antimonster-optimizer' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'antimonster-optimizer-admin-css', ANTIMONSTER_OPTIMIZER_URL . 'assets/css/admin.css', array(), ANTIMONSTER_OPTIMIZER_VERSION );
		wp_enqueue_script( 'antimonster-optimizer-admin-js', ANTIMONSTER_OPTIMIZER_URL . 'assets/js/admin.js', array(), ANTIMONSTER_OPTIMIZER_VERSION, true );
	}

	public function add_plugin_page() {
		add_options_page(
			__( 'AntiMonster Optimizer', 'antimonster-optimizer' ),
			__( 'AntiMonster Optimizer', 'antimonster-optimizer' ),
			'manage_options',
			'antimonster-optimizer',
			array( $this, 'create_admin_page' )
		);
	}

	public function create_admin_page() {
		$this->options = get_option( 'antimonster_optimizer_settings', array() );
		$export_data = wp_json_encode( $this->options );
		?>

		<div class="wrap" style="margin:0;">
			<div class="antimonster-optimizer-header">
				<h1><span class="dashicons dashicons-performance"></span> <?php esc_html_e('AntiMonster Optimizer', 'antimonster-optimizer'); ?> <span style="font-size: 13px; color: #8F9CB5; margin-left:15px; font-weight: normal;"><?php esc_html_e('v1.0.1 Premium', 'antimonster-optimizer'); ?></span></h1>
			</div>

			<form method="post" action="options.php" id="antimonster-optimizer-options-form">
				<?php settings_fields( 'antimonster_optimizer_group' ); ?>

				<div class="antimonster-optimizer-wrapper">

					<div class="antimonster-subnav">
						<a href="#options-general" class="active"><span class="dashicons dashicons-dashboard"></span><?php esc_html_e('General', 'antimonster-optimizer'); ?></a>
						<a href="#options-woocommerce"><span class="dashicons dashicons-cart"></span><?php esc_html_e('WooCommerce', 'antimonster-optimizer'); ?></a>
						<a href="#options-tools"><span class="dashicons dashicons-admin-tools"></span><?php esc_html_e('Tools', 'antimonster-optimizer'); ?></a>
					</div>

					<!-- GENERAL OPTIONS -->
					<section id="options-general" class="antimonster-section-content active">
						<h2><?php esc_html_e('General Options', 'antimonster-optimizer'); ?></h2>
						<p class="antimonster-subheading"><?php esc_html_e('Select which WordPress core performance options you would like to enable.', 'antimonster-optimizer'); ?></p>

						<table class="form-table">
							<tbody>
							<?php
								$general_fields = array(
									'disable_emojis' => array('type' => 'toggle', 'title' => __('Disable Emojis', 'antimonster-optimizer'), 'desc' => __('Removes WordPress Emojis JavaScript file and styles.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/print_emoji_detection_script/'),
									'disable_dashicons' => array('type' => 'toggle', 'title' => __('Disable Dashicons', 'antimonster-optimizer'), 'desc' => __('Disables dashicons on the front end when not logged in.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/resource/dashicons/'),
									'disable_embeds' => array('type' => 'toggle', 'title' => __('Disable Embeds', 'antimonster-optimizer'), 'desc' => __('Removes WordPress Embed JavaScript file (wp-embed.min.js).', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/wp_oembed_add_discovery_links/'),
									'disable_xmlrpc' => array('type' => 'toggle', 'title' => __('Disable XML-RPC', 'antimonster-optimizer'), 'desc' => __('Disables WordPress XML-RPC functionality.', 'antimonster-optimizer'), 'doc' => 'https://codex.wordpress.org/XML-RPC_Support'),
									'remove_jquery_migrate' => array('type' => 'toggle', 'title' => __('Remove jQuery Migrate', 'antimonster-optimizer'), 'desc' => __('Removes jQuery Migrate JavaScript file (jquery-migrate.min.js).', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/wp_default_scripts/'),
									'hide_wp_version' => array('type' => 'toggle', 'title' => __('Hide WP Version', 'antimonster-optimizer'), 'desc' => __('Removes WordPress version meta tag.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/the_generator/'),
									'remove_wlwmanifest_link' => array('type' => 'toggle', 'title' => __('Remove wlwmanifest Link', 'antimonster-optimizer'), 'desc' => __('Remove wlwmanifest (Windows Live Writer) link tag.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/wlwmanifest_link/'),
									'remove_rsd_link' => array('type' => 'toggle', 'title' => __('Remove RSD Link', 'antimonster-optimizer'), 'desc' => __('Remove RSD (Real Simple Discovery) link tag.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/rsd_link/'),
									'remove_shortlink' => array('type' => 'toggle', 'title' => __('Remove Shortlink', 'antimonster-optimizer'), 'desc' => __('Remove Shortlink link tag.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/wp_shortlink_wp_head/'),
									'disable_rss_feeds' => array('type' => 'toggle', 'title' => __('Disable RSS Feeds', 'antimonster-optimizer'), 'desc' => __('Disable WordPress generated RSS feeds.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/hooks/do_feed/'),
									'remove_feed_links' => array('type' => 'toggle', 'title' => __('Remove RSS Feed Links', 'antimonster-optimizer'), 'desc' => __('Disable WordPress generated RSS feed link tags from HTML head.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/feed_links/'),
									'disable_self_pingbacks' => array('type' => 'toggle', 'title' => __('Disable Self Pingbacks', 'antimonster-optimizer'), 'desc' => __('Disable Self Pingbacks (generated when linking to an article on your own blog).', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/pingback/'),
									'disable_rest_api' => array(
										'type' => 'select',
										'title' => __('Disable REST API', 'antimonster-optimizer'),
										'desc' => __('Disables REST API requests. Default (Enabled)', 'antimonster-optimizer'),
										'options' => array(
											'' => __('Default (Enabled)', 'antimonster-optimizer'),
											'disable_non_admins' => __('Disable for Non-Admins', 'antimonster-optimizer'),
											'disable_logged_out' => __('Disable When Logged Out', 'antimonster-optimizer')
										),
										'doc' => 'https://developer.wordpress.org/rest-api/'
									),
									'remove_rest_api_links' => array('type' => 'toggle', 'title' => __('Remove REST API Links', 'antimonster-optimizer'), 'desc' => __('Removes REST API link tag from the front end.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/rest_output_link_wp_head/'),
									'disable_google_maps' => array('type' => 'toggle', 'title' => __('Disable Google Maps', 'antimonster-optimizer'), 'desc' => __('Removes any instances of Google Maps API being loaded.', 'antimonster-optimizer')),
									'disable_password_strength_meter' => array('type' => 'toggle', 'title' => __('Disable Password Strength Meter', 'antimonster-optimizer'), 'desc' => __('Removes Password Strength Meter scripts from non essential pages.', 'antimonster-optimizer')),
									'disable_comments' => array('type' => 'toggle', 'title' => __('Disable Comments', 'antimonster-optimizer'), 'desc' => __('Disables WordPress comments completely.', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/comments_open/'),
									'remove_comment_urls' => array('type' => 'toggle', 'title' => __('Remove Comment URLs', 'antimonster-optimizer'), 'desc' => __('Removes the WordPress comment author link and website field.', 'antimonster-optimizer')),
									'blank_favicon' => array('type' => 'toggle', 'title' => __('Add Blank Favicon', 'antimonster-optimizer'), 'desc' => __('Adds a blank favicon to prevent 404 errors on speed tests.', 'antimonster-optimizer')),
									'remove_global_styles' => array('type' => 'toggle', 'title' => __('Remove Global Styles', 'antimonster-optimizer'), 'desc' => __('Remove the inline global styles (CSS and SVG block library code).', 'antimonster-optimizer'), 'doc' => 'https://developer.wordpress.org/reference/functions/wp_enqueue_global_styles/'),
									'disable_heartbeat' => array(
										'type' => 'select',
										'title' => __('Disable Heartbeat', 'antimonster-optimizer'),
										'desc' => __('Disable WordPress Heartbeat everywhere or in certain areas.', 'antimonster-optimizer'),
										'options' => array(
											'' => __('Default', 'antimonster-optimizer'),
											'disable_everywhere' => __('Disable Everywhere', 'antimonster-optimizer'),
											'allow_posts' => __('Only Allow When Editing Posts/Pages', 'antimonster-optimizer')
										),
										'doc' => 'https://developer.wordpress.org/reference/functions/wp_auth_check_load/'
									),
									'heartbeat_frequency' => array(
										'type' => 'select',
										'title' => __('Heartbeat Frequency', 'antimonster-optimizer'),
										'desc' => __('Controls how often the WordPress Heartbeat API is allowed to run.', 'antimonster-optimizer'),
										'options' => array(
											'' => __('15 Seconds (Default)', 'antimonster-optimizer'),
											'30' => __('30 Seconds', 'antimonster-optimizer'),
											'45' => __('45 Seconds', 'antimonster-optimizer'),
											'60' => __('60 Seconds', 'antimonster-optimizer')
										)
									),
									'limit_post_revisions' => array(
										'type' => 'select',
										'title' => __('Limit Post Revisions', 'antimonster-optimizer'),
										'desc' => __('Limits the maximum amount of revisions allowed.', 'antimonster-optimizer'),
										'options' => array(
											'' => __('Default', 'antimonster-optimizer'),
											'false' => __('Disable Post Revisions', 'antimonster-optimizer'),
											'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
											'10' => '10', '15' => '15', '20' => '20', '25' => '25', '30' => '30'
										),
										'doc' => 'https://wordpress.org/documentation/article/revisions/'
									),
									'autosave_interval' => array(
										'type' => 'select',
										'title' => __('Autosave Interval', 'antimonster-optimizer'),
										'desc' => __('Controls how often WordPress will auto save.', 'antimonster-optimizer'),
										'options' => array(
											'' => __('1 Minute (Default)', 'antimonster-optimizer'),
											'120' => __('2 Minutes', 'antimonster-optimizer'),
											'180' => __('3 Minutes', 'antimonster-optimizer'),
											'240' => __('4 Minutes', 'antimonster-optimizer'),
											'300' => __('5 Minutes', 'antimonster-optimizer')
										)
									)
								);

								$this->render_fields($general_fields);
							?>
							</tbody>
						</table>
					</section>

					<!-- WOOCOMMERCE OPTIONS -->
					<section id="options-woocommerce" class="antimonster-section-content">
						<h2><?php esc_html_e('WooCommerce', 'antimonster-optimizer'); ?></h2>
						<p class="antimonster-subheading"><?php esc_html_e('Disable specific elements of WooCommerce.', 'antimonster-optimizer'); ?></p>
						<table class="form-table">
							<tbody>
							<?php
								$woo_fields = array(
									'disable_woocommerce_scripts' => array('type' => 'toggle', 'title' => __('Disable Scripts', 'antimonster-optimizer'), 'desc' => __('Disables WooCommerce scripts/styles except on product, cart, checkout.', 'antimonster-optimizer')),
									'disable_woocommerce_cart_fragmentation' => array('type' => 'toggle', 'title' => __('Disable Cart Fragmentation', 'antimonster-optimizer'), 'desc' => __('Completely disables WooCommerce cart fragmentation AJAX script.', 'antimonster-optimizer')),
									'disable_woocommerce_status' => array('type' => 'toggle', 'title' => __('Disable Status Meta Box', 'antimonster-optimizer'), 'desc' => __('Disables WooCommerce status meta box from Dashboard.', 'antimonster-optimizer')),
									'disable_woocommerce_widgets' => array('type' => 'toggle', 'title' => __('Disable Widgets', 'antimonster-optimizer'), 'desc' => __('Disables all WooCommerce widgets from loading.', 'antimonster-optimizer'))
								);

								$this->render_fields($woo_fields);
							?>
							</tbody>
						</table>
					</section>
					
					<div class="antimonster-submit-wrap">
						<?php submit_button( __('Save Changes', 'antimonster-optimizer'), 'primary', 'submit', false ); ?>
					</div>
			</form>

					<!-- TOOLS OPTIONS (EXPORT / IMPORT) -->
					<section id="options-tools" class="antimonster-section-content import-export-area">
						<h2><?php esc_html_e('Export / Import Settings', 'antimonster-optimizer'); ?></h2>
						<p class="antimonster-subheading"><?php esc_html_e('Export your current settings as JSON or import settings from another site.', 'antimonster-optimizer'); ?></p>

						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row"><label><?php esc_html_e('Export Settings', 'antimonster-optimizer'); ?></label></th>
									<td>
										<textarea readonly="readonly" onclick="this.focus();this.select()"><?php echo esc_textarea( $export_data ); ?></textarea>
										<p class="description"><?php esc_html_e('Copy this JSON data and save it to a secure location.', 'antimonster-optimizer'); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="antimonster_import_data"><?php esc_html_e('Import Settings', 'antimonster-optimizer'); ?></label></th>
									<td>
										<textarea name="antimonster_import_data" id="antimonster_import_data" placeholder='{"disable_emojis":"1", "disable_embeds":"1"}'></textarea>
										<p class="description"><?php esc_html_e('Paste your exported JSON data here and click Import.', 'antimonster-optimizer'); ?></p>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="submit" name="antimonster_import_submit" id="submit" class="button button-secondary" value="<?php esc_attr_e('Import Settings', 'antimonster-optimizer'); ?>">
							<?php wp_nonce_field( 'antimonster_import_nonce', 'antimonster_import_nonce' ); ?>
						</p>
					</section>

				</div>
			</form>
		<?php
	}

	private function render_fields($fields) {
		foreach ( $fields as $id => $data ) {
			?>
			<tr>
				<th scope="row">
					<span class="antimonster-title-wrapper">
						<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $data['title'] ); ?></label>
						<?php if ( ! empty( $data['doc'] ) ) : ?>
							<a href="<?php echo esc_url( $data['doc'] ); ?>" target="_blank" title="<?php esc_attr_e( 'View WordPress Documentation', 'antimonster-optimizer' ); ?>" style="margin-left: 10px; color: #8F9CB5; text-decoration: none;"><span class="dashicons dashicons-editor-help" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span></a>
						<?php endif; ?>
					</span>
				</th>
				<td>
				<?php 
				if ( $data['type'] === 'toggle' ) {
					$checked = isset( $this->options[ $id ] ) && 1 == $this->options[ $id ] ? 'checked="checked"' : ''; ?>
					<label for="<?php echo esc_attr( $id ); ?>" class="switch">
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="antimonster_optimizer_settings[<?php echo esc_attr( $id ); ?>]" value="1" <?php echo $checked; ?>>
						<div class="slider"></div>
					</label>
				<?php 
				} elseif ( $data['type'] === 'select' ) { 
					$selected = isset( $this->options[ $id ] ) ? $this->options[ $id ] : ''; ?>
					<select id="<?php echo esc_attr( $id ); ?>" class="antimonster-select" name="antimonster_optimizer_settings[<?php echo esc_attr( $id ); ?>]">
						<?php foreach( $data['options'] as $val => $label ) { ?>
							<option value="<?php echo esc_attr($val); ?>" <?php selected( $selected, (string)$val ); ?>><?php echo esc_html($label); ?></option>
						<?php } ?>
					</select>
				<?php 
				} 
				?>
					<span class="antimonster-tooltip-text"><?php echo esc_html( $data['desc'] ); ?></span>
				</td>
			</tr>
			<?php
		}
	}

	public function page_init() {
		register_setting(
			'antimonster_optimizer_group',
			'antimonster_optimizer_settings',
			array( $this, 'sanitize' )
		);
	}

	public function handle_export_import() {
		if ( isset( $_POST['antimonster_import_submit'] ) && isset( $_POST['antimonster_import_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['antimonster_import_nonce'] ) ), 'antimonster_import_nonce' ) ) {
				add_action( 'admin_notices', function() {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Security check failed. Please try again.', 'antimonster-optimizer' ) . '</p></div>';
				} );
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', function() {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'You do not have permission to import settings.', 'antimonster-optimizer' ) . '</p></div>';
				} );
				return;
			}

			if ( ! empty( $_POST['antimonster_import_data'] ) ) {
				$import_string = sanitize_textarea_field( wp_unslash( $_POST['antimonster_import_data'] ) );
				// Use htmlspecialchars_decode to handle quotes
				$import_data = json_decode( htmlspecialchars_decode( $import_string ), true );

				if ( is_array( $import_data ) ) {
					// Validate through our sanitizer
					$sanitized_data = $this->sanitize( $import_data );
					update_option( 'antimonster_optimizer_settings', $sanitized_data );

					// Add admin notice indicating success
					add_action( 'admin_notices', function() {
						echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings imported successfully!', 'antimonster-optimizer' ) . '</p></div>';
					} );
				} else {
					add_action( 'admin_notices', function() {
						echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid JSON data provided for import.', 'antimonster-optimizer' ) . '</p></div>';
					} );
				}
			}
		} elseif ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully!', 'antimonster-optimizer' ) . '</p></div>';
			} );
		}
	}

	public function sanitize( $input ) {
		$new_input = array();

		// Define allowed integer/string fields for stricter validation
		$allowed_select_fields = array(
			'disable_rest_api'     => array( '', 'disable_non_admins', 'disable_logged_out' ),
			'disable_heartbeat'    => array( '', 'disable_everywhere', 'allow_posts' ),
			'heartbeat_frequency'  => array( '', '30', '45', '60' ),
			'limit_post_revisions' => array( '', 'false', '1', '2', '3', '4', '5', '10', '15', '20', '25', '30' ),
			'autosave_interval'    => array( '', '120', '180', '240', '300' )
		);

		if ( is_array( $input ) ) {
			foreach ( $input as $key => $val ) {
				$sanitized_key = sanitize_key( $key );

				if ( array_key_exists( $sanitized_key, $allowed_select_fields ) ) {
					if ( in_array( $val, $allowed_select_fields[ $sanitized_key ], true ) ) {
						$new_input[ $sanitized_key ] = sanitize_text_field( $val );
					}
				} else {
					// Toggle fields are typically 1 or empty
					$new_input[ $sanitized_key ] = ( 1 == $val ) ? '1' : '';
				}
			}
		}
		return $new_input;
	}
}
