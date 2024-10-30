<?php
/*
Plugin Name: Brainshark
Plugin URI: http://www.brainshark.com/
Description: A WordPress plugin to easily display Brainshark presentations
Version: 1.1
Author: Chris Caruso
Author URI: http://www.brainshark.com/
*/

if ( ! class_exists( 'Brainshark_Admin' ) ) {

	class Brainshark_Admin {

		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_options_page('Brainshark Config', 'Brainshark', 10, basename(__FILE__),array('Brainshark_Admin','config_page'));
				add_filter( 'plugin_action_links', array( 'Brainshark_Admin', 'filter_plugin_actions'), 10, 2 );
				add_filter( 'ozh_adminmenu_icon', array( 'Brainshark_Admin', 'add_ozh_adminmenu_icon' ) );				
			}
		}
		
		function add_ozh_adminmenu_icon( $hook ) {
			static $brainsharkicon;
			if (!$brainsharkicon) {
				$brainsharkicon = WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)). '/page_white_brainshark.png';
			}
			if ($hook == 'brainshark.php') return $brainsharkicon;
			return $hook;
		}

		function filter_plugin_actions( $links, $file ){
			//Static so we don't call plugin_basename on every plugin row.
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
			
			if ( $file == $this_plugin ){
				$settings_link = '<a href="options-general.php?page=brainshark.php">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link ); // before other links
			}
			return $links;
		}
		function config_page() {
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Brainshark options.'));
				check_admin_referer('brainshark-updatesettings');

				if (isset($_POST['brainsharkpostwidth'])) {
					$options['postwidth'] = $_POST['brainsharkpostwidth'];
				}
				
				update_option('Brainshark', $options);
			}

			$options  = get_option('Brainshark');
			?>
			<div class="wrap">
				<h2>Brainshark Configuration</h2>
				<form action="" method="post" id="brainshark-conf">
					<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('brainshark-updatesettings'); } ?>
					<table class="form-table">
						<tr>
							<td>
								<label for="brainsharkpostwidth">Default width:</label>
							</td>
							<td>
								<input size="5" type="text" id="brainsharkpostwidth" name="brainsharkpostwidth" value="<?php echo $options['postwidth'];?>"/> pixels
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<span class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></span>
							</td>
					</table>
				</form>
				<h3>Explanation of usage</h3>
				<p>
					Just copy and paste the "Embed (wordpress.com)" code from <a href="http://www.brainshark.com/" title="Brainshark (share powerpoint presentations online, slideshows, slide shows, download presentations)">Brainshark</a>, and you're done.
				</p>
				<h3>Explanation of default width</h3>
				<p>
					If you enter nothing here, you can change the width by hand by changing the w= value, that is bolded and red here:
				</p>
				<pre>[brainshark id=1234&amp;doc=how-to-change-the-width-123456789-1&amp;<strong style="color:red;">w=425</strong>]</pre>
				<p>
					If you <em>do</em> enter a value, it will always replace the width with that value.
				</p>
			</div>
			<?php
		}
	}
}

function brainshark_insert($atts, $content=null) {	
	$options = get_option('Brainshark');
	
	if(isset($atts)) {
		$args = str_replace('&amp;','&',$atts['id']);
		$r = wp_parse_args($args);
	
		if ($options['postwidth'] == '') {
			$width = $r['w'];			
		} else {
			$width = $options['postwidth'];
		}
		
		$height = intval($width / 1.202279);

		$pid = $r['pid'];
		
		$content = '<div><iframe src="http://www.brainshark.com/brainshark/vu/view.asp?pi=' . $pid . '&dm=5&pause=1&nrs=1" frameborder="0" width="' . $width . 'px" height="' . $height . 'px" scrolling="no" style="border:1px solid #999999"></iframe></div>';
	}
	
	return $content;
}

add_action('admin_menu', array('Brainshark_Admin','add_config_page'));
add_shortcode('brainshark', 'brainshark_insert');

?>
