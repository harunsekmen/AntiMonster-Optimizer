<?php
/**
 * Optimizer Engine functionality
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class AntiMonster_Optimizer_Engine {

	private $options;

	public function __construct() {
		$this->options = get_option( 'antimonster_optimizer_settings' );

		// Fallback to safe defaults if options are not set yet
		if ( false === $this->options || ! is_array( $this->options ) ) {
			$this->options = array(
				'disable_emojis'          => '1',
				'disable_embeds'          => '1',
				'hide_wp_version'         => '1',
				'remove_wlwmanifest_link' => '1',
				'remove_rsd_link'         => '1',
				'remove_shortlink'        => '1',
				'disable_dashicons'       => '1',
			);
		}

		$this->init_optimizations();
	}

	private function init_optimizations() {
		$optimizations = array(
			'disable_emojis'                    => array( 'init' => 'disable_emojis' ),
			'disable_dashicons'                 => array( 'wp_enqueue_scripts' => 'disable_dashicons' ),
			'disable_embeds'                    => array( array( 'init', 'disable_embeds', 9999 ) ),
			'disable_xmlrpc'                    => array( 'init' => 'disable_xmlrpc' ), // we'll route this to a method
			'remove_jquery_migrate'             => array( 'wp_default_scripts' => 'remove_jquery_migrate' ),
			'hide_wp_version'                   => array( 'init' => 'hide_wp_version' ), // route to a method
			'remove_wlwmanifest_link'           => array( array( 'init', 'remove_wlwmanifest_link' ) ), // route to method
			'remove_rsd_link'                   => array( array( 'init', 'remove_rsd_link' ) ), // route to method
			'remove_shortlink'                  => array( array( 'init', 'remove_shortlink' ) ), // route to method
			'disable_rss_feeds'                 => array( 'init' => 'init_disable_rss_feeds' ), // route to method
			'remove_feed_links'                 => array( array( 'init', 'remove_feed_links' ) ), // route to method
			'disable_self_pingbacks'            => array( array( 'init', 'init_disable_self_pingbacks' ) ), // route to method
			'disable_rest_api'                  => array( array( 'rest_authentication_errors', 'restrict_rest_api' ) ), // route to method
			'remove_rest_api_links'             => array( array( 'init', 'remove_rest_api_links' ) ), // route to method
			'disable_google_maps'               => array( array( 'clean_url', 'disable_google_maps', 99, 2 ) ),
			'disable_password_strength_meter'   => array( array( 'wp_print_scripts', 'disable_password_strength_meter', 100 ) ),
			'disable_comments'                  => array( array( 'init', 'init_disable_comments' ) ), // route to method
			'remove_comment_urls'               => array( array( 'init', 'init_remove_comment_urls' ) ), // route to method
			'blank_favicon'                     => array( array( 'init', 'init_blank_favicon' ) ), // route to method
			'remove_global_styles'              => array( array( 'wp_enqueue_scripts', 'remove_global_styles', 100 ) ),
			'disable_heartbeat'                 => array( array( 'init', 'disable_heartbeat', 1 ) ),
			'heartbeat_frequency'               => array( array( 'heartbeat_settings', 'heartbeat_frequency' ) ),
			'limit_post_revisions'              => array( array( 'wp_revisions_to_keep', 'limit_post_revisions', 10, 2 ) ),
			'autosave_interval'                 => array( array( 'init', 'init_autosave_interval' ) ), // route to method
			'disable_woocommerce_scripts'       => array( array( 'wp_enqueue_scripts', 'disable_woocommerce_scripts', 99 ) ),
			'disable_woocommerce_cart_fragmentation' => array( array( 'wp_enqueue_scripts', 'disable_woocommerce_cart_frag', 99 ) ),
			'disable_woocommerce_status'        => array( array( 'wp_dashboard_setup', 'disable_woocommerce_status_widget' ) ),
			'disable_woocommerce_widgets'       => array( array( 'widgets_init', 'disable_woocommerce_widgets', 99 ) ),
		);

		foreach ( $optimizations as $option_key => $actions ) {
			if ( ! empty( $this->options[ $option_key ] ) ) {
				foreach ( $actions as $hook => $callback_info ) {
					// Handle the array format like array('init', 'method', priority, accepted_args)
					if ( is_array( $callback_info ) ) {
						$action_hook = $callback_info[0];
						$method      = $callback_info[1];
						$priority    = isset( $callback_info[2] ) ? $callback_info[2] : 10;
						$args        = isset( $callback_info[3] ) ? $callback_info[3] : 1;

						// Determine if it's an action or filter based on hook name loosely, or just use add_filter for both
						add_filter( $action_hook, array( $this, $method ), $priority, $args );
					} else {
						// Simple format: 'hook' => 'method'
						add_filter( $hook, array( $this, $callback_info ) );
					}
				}
			}
		}
	}

	// === Hooks Callback Methods (which were previously closures or simple functions) === //

	public function init_disable_rss_feeds() {
		add_action( 'do_feed', array( $this, 'disable_feeds' ), 1 );
		add_action( 'do_feed_rdf', array( $this, 'disable_feeds' ), 1 );
		add_action( 'do_feed_rss', array( $this, 'disable_feeds' ), 1 );
		add_action( 'do_feed_rss2', array( $this, 'disable_feeds' ), 1 );
		add_action( 'do_feed_atom', array( $this, 'disable_feeds' ), 1 );
		add_action( 'do_feed_rss2_comments', array( $this, 'disable_feeds' ), 1 );
		add_action( 'do_feed_atom_comments', array( $this, 'disable_feeds' ), 1 );
	}

	public function init_disable_self_pingbacks() {
		add_action( 'pre_ping', array( $this, 'disable_self_pingbacks' ) );
		add_filter( 'wp_headers', array( $this, 'remove_x_pingback_header' ) );
	}

	public function init_disable_comments() {
		add_action( 'admin_init', array( $this, 'disable_comments_admin' ) );
		add_filter( 'comments_open', '__return_false', 20, 2 );
		add_filter( 'pings_open', '__return_false', 20, 2 );
		add_filter( 'comments_array', '__return_empty_array', 10, 2 );
		add_action( 'admin_menu', array( $this, 'disable_comments_menu' ) );
		$this->disable_comments_support();
	}

	public function init_remove_comment_urls() {
		add_filter( 'get_comment_author_url', '__return_empty_string' );
		add_filter( 'comment_form_default_fields', array( $this, 'remove_comment_url_field' ) );
	}

	public function init_blank_favicon() {
		add_action( 'wp_head', array( $this, 'add_blank_favicon' ) );
		add_action( 'admin_head', array( $this, 'add_blank_favicon' ) );
	}

	public function disable_xmlrpc() {
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}

	public function hide_wp_version() {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	public function remove_wlwmanifest_link() {
		remove_action( 'wp_head', 'wlwmanifest_link' );
	}

	public function remove_rsd_link() {
		remove_action( 'wp_head', 'rsd_link' );
	}

	public function remove_shortlink() {
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	}

	public function remove_feed_links() {
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}

	public function remove_rest_api_links() {
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	}

	public function init_autosave_interval() {
		if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
			define( 'AUTOSAVE_INTERVAL', (int) $this->options['autosave_interval'] );
		}
	}

	// === Core Methods === //

	public function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', function($p) { return is_array($p) ? array_diff($p, array('wpemoji')) : array(); } );
		add_filter( 'wp_resource_hints', function($u, $r) {
			if ( 'dns-prefetch' === $r ) {
				$u = array_diff( $u, array( apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' ) ) );
			}
			return $u;
		}, 10, 2 );
	}

	public function disable_dashicons() {
		if ( ! is_user_logged_in() ) {
			wp_deregister_style( 'dashicons' );
		}
	}

	public function disable_embeds() {
		global $wp;
		$wp->public_query_vars = array_diff( $wp->public_query_vars, array( 'embed' ) );
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );
		add_filter( 'embed_oembed_discover', '__return_false' );
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_filter( 'tiny_mce_plugins', function($p) { return array_diff($p, array('wpembed')); } );
		add_filter( 'rewrite_rules_array', function($r) {
			foreach((array)$r as $rule => $rewrite) { if(strpos($rewrite, 'embed=true') !== false) unset($r[$rule]); }
			return $r;
		} );
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
	}

	public function remove_jquery_migrate( $scripts ) {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
			$script = $scripts->registered['jquery'];
			if ( $script->deps ) {
				$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
			}
		}
	}

	public function disable_feeds() {
		wp_die( __( 'No feed available, please visit the <a href="'. esc_url( home_url( '/' ) ) .'">homepage</a>!' ) );
	}

	public function disable_self_pingbacks( &$links ) {
		$home = get_option( 'home' );
		foreach ( $links as $l => $link ) {
			if ( 0 === strpos( $link, $home ) ) {
				unset($links[$l]);
			}
		}
	}

	public function remove_x_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	public function restrict_rest_api( $result ) {
		if ( ! empty( $result ) ) return $result;
		$setting = $this->options['disable_rest_api'];
		if ( 'disable_non_admins' === $setting && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', __('REST API is disabled for non-admins.', 'antimonster-optimizer'), array( 'status' => 401 ) );
		}
		if ( 'disable_logged_out' === $setting && ! is_user_logged_in() ) {
			return new WP_Error( 'rest_not_logged_in', __('REST API is disabled for logged out users.', 'antimonster-optimizer'), array( 'status' => 401 ) );
		}
		return $result;
	}

	public function disable_google_maps( $url, $original_url ) {
		if ( strpos( $url, 'maps.google.com/maps/api/js' ) !== false || strpos( $url, 'maps.googleapis.com/maps/api/js' ) !== false ) {
			return '';
		}
		return $url;
	}

	public function disable_password_strength_meter() {
		wp_dequeue_script( 'zxcvbn-async' );
		wp_deregister_script( 'zxcvbn-async' );
		if ( ! is_page( 'my-account' ) && ! is_page( 'checkout' ) && ! is_account_page() && ! is_checkout() ) {
			wp_dequeue_script( 'wc-password-strength-meter' );
		}
	}

	public function disable_comments_admin() {
		global $pagenow;
		if ( 'edit-comments.php' === $pagenow || 'options-discussion.php' === $pagenow ) {
			wp_redirect( admin_url() );
			exit;
		}
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}
	
	public function disable_comments_menu() {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}
	
	public function disable_comments_support() {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	public function remove_comment_url_field( $fields ) {
		if ( isset( $fields['url'] ) ) {
			unset( $fields['url'] );
		}
		return $fields;
	}

	public function add_blank_favicon() {
		echo '<link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABmJLR0T///////8JWPfcAAAACXBIWXMAAABIAAAASABGyWs+AAAAF0lEQVRIx2NgGAWjYBSMglEwCkbBSHIAAAXmAAGf3DNcAAAAAElFTkSuQmCC" rel="icon" type="image/x-icon" />';
	}

	public function remove_global_styles() {
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-blocks-style' ); 
	}

	public function disable_heartbeat() {
		$setting = $this->options['disable_heartbeat'];
		if ( 'disable_everywhere' === $setting ) {
			wp_deregister_script( 'heartbeat' );
		} elseif ( 'allow_posts' === $setting ) {
			global $pagenow;
			if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
				wp_deregister_script( 'heartbeat' );
			}
		}
	}

	public function heartbeat_frequency( $settings ) {
		$settings['interval'] = (int) $this->options['heartbeat_frequency'];
		return $settings;
	}

	public function limit_post_revisions( $num, $post ) {
		$setting = $this->options['limit_post_revisions'];
		if ( 'false' === $setting ) return 0;
		return (int) $setting;
	}

	// === WooCommerce Methods === //

	public function disable_woocommerce_scripts() {
		if ( function_exists( 'is_woocommerce' ) ) {
			if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
				wp_dequeue_script( 'woocommerce' );
				wp_dequeue_script( 'wc-add-to-cart' );
				wp_dequeue_script( 'wc-cart-fragments' );
				wp_dequeue_style( 'woocommerce-general' );
				wp_dequeue_style( 'woocommerce-layout' );
				wp_dequeue_style( 'woocommerce-smallscreen' );
			}
		}
	}

	public function disable_woocommerce_cart_frag() {
		wp_dequeue_script( 'wc-cart-fragments' );
	}

	public function disable_woocommerce_status_widget() {
		remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );
	}

	public function disable_woocommerce_widgets() {
		unregister_widget( 'WC_Widget_Products' );
		unregister_widget( 'WC_Widget_Product_Categories' );
		unregister_widget( 'WC_Widget_Product_Tag_Cloud' );
		unregister_widget( 'WC_Widget_Cart' );
		unregister_widget( 'WC_Widget_Layered_Nav' );
		unregister_widget( 'WC_Widget_Layered_Nav_Filters' );
		unregister_widget( 'WC_Widget_Price_Filter' );
		unregister_widget( 'WC_Widget_Product_Search' );
		unregister_widget( 'WC_Widget_Recently_Viewed' );
		unregister_widget( 'WC_Widget_Recent_Reviews' );
		unregister_widget( 'WC_Widget_Top_Rated_Products' );
		unregister_widget( 'WC_Widget_Rating_Filter' );
	}
}
