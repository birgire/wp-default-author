<?php
/*
Plugin Name: WP Default Author
Plugin URI:  http://wordpress.org/extend/plugins/wp-default-author/
Description: Default author settings (defined both globally and per user) 
Version:     1.0.6
Author:      birgire
Author URI:  http://profiles.wordpress.org/birgire
License:     GPLv2
*/


/**
 * No direct access
 */
defined( 'ABSPATH' ) or die( 'Nothing here!' );


/**
 * Init the Default_Author class
 */
if( ! class_exists( 'WP_Default_Author' ) ):
	
	/**
	 * Call the class
	 */
	function call_WP_Default_Author() {

		// only activate if the current user is an Editor
		if( current_user_can( 'edit_others_posts' ) )
			return new WP_Default_Author();

	}
	
	// we only need it for the backend
	if ( is_admin() )
		add_action( 'init', 'call_WP_Default_Author' );
	

	/**
	 *  Class Default_Author
	 */
	class WP_Default_Author{
		
		protected $plugin_domain 			= 'wpdeau';		
		protected $default_author_option_name 		= 'wpdeau_default_author';
		protected $default_global_author_option_name 	= 'wpdeau_global_default_author';
		
		/**
		 * Class init
		 */
		public function __construct(){

			add_action( 'admin_init', 		array( &$this, 'init' ) );
			add_action( 'show_user_profile', 	array( &$this, 'show_user_settings' ) );
			add_action( 'edit_user_profile', 	array( &$this, 'show_user_settings' ) );
			add_action( 'personal_options_update', 	array( &$this, 'save_user_settings' ) );
			add_action( 'edit_user_profile_update', array( &$this, 'save_user_settings' ) );
			add_filter( 'wp_insert_post_data', 	array( &$this, 'wp_insert_post_data_callback' ), '99', 2 );
			
		}


		/**
		 * Plugin init
		 * @since 1.0.3
		 * @return void
		 */

		public function init(){
			
			$plugin_dir = basename( dirname( __FILE__ ) );
			load_plugin_textdomain( $this->plugin_comain, FALSE, $plugin_dir . '/languages/' );

			register_setting( 
				'writing', 
				$this->default_global_author_option_name, 
				array( $this, 'validate_global_settings' ) 
			);
		
			add_settings_field(
				 $this->default_global_author_option_name,
				sprintf( '<label for="%s">%s</label>' , $this->default_global_author_option_name,  __( 'Global default author' , $this->plugin_domain ) ),
				array( &$this, 'show_global_settings_field' ),
				'writing'
			);
		
		}

		
		/**
		 * Modify the post author, just before the insert new post
		 * @since 1.0.1
		 * @return array $data Post data to save
		 */

		public function wp_insert_post_data_callback( $data , $pa ) {
		
			$current_user = wp_get_current_user();
			
			if( 'auto-draft' !== $data['post_status'] ):
				return $data;
			endif;
			
			if( 'post' !== $data['post_type'] ):
				return $data;
			endif;
			
			if( '0000-00-00 00:00:00' !== $data['post_date_gmt'] ):
				return $data;
			endif;
			
			
			// Check first if global author is set, 
			// if not then check if the current user has one
			// else use the current author as the post author
			
			$default_global_author 	= get_option( $this->default_global_author_option_name , TRUE );
            		$default_author 	= get_the_author_meta( $this->default_author_option_name , $current_user->ID );

			if( $default_author ){
				$data['post_author'] = $default_author;
			}elseif( $default_global_author ){
				$data['post_author'] = $default_global_author;
			}
			
			return $data;
		}
		
		/**
		 * Validate global settings field from the Options/Writing Settings page
		 * @since 1.0.3
		 * @param string $input
		 * @return string $output Sanitized input
		 */

		public function validate_global_settings( $input ) {
			$output = esc_attr( $input );
			return $output;
		}


		/**
		 * Show the global default author settings on the Options/Writing Settings page
		 * @since 1.0.3
		 * @return void
		 */

		public function show_global_settings_field() {
			$selected_global_author = get_option(  $this->default_global_author_option_name, TRUE );
						
			$args = array( 
				'name' 				=> $this->default_global_author_option_name, 
				'selected' 			=> $selected_global_author,
				'show_option_all' 	=> '  ',
				);
						
				wp_dropdown_users( $args );
			?>
				<span class="description">
					<?php _e( 'This will leave the default user defined per user intact', $this->plugin_domain ); ?>
				</span>
			<?php
		}

		
		/**
		 * Save default author settings on the Profile Settings page
		 * @since 1.0.1
		 * @param integer $user_id
		 * @return void
		 */

		public function save_user_settings( $user_id ) {
			
			// only allow editors to save
			if ( !current_user_can( 'edit_others_posts' ) )
				return FALSE;
			
			// check if the input is set
			$value = 0;
			if( isset( $_POST[$this->default_author_option_name] ) )
				$value = $_POST[$this->default_author_option_name];
			
			// update user meta
			if( $value > 0 )
				update_usermeta( $user_id, $this->default_author_option_name, $value );
			
		}

		
		/**
		 * Show the default author settings on the Profile Settings page
		 * @since 1.0.1
		 * @param object $user WP_User Object
		 * @return void
		 */		

		public function show_user_settings( $user ) { 
		?>
		<h3>
			<?php _e( 'Default Author Settings', $this->plugin_domain ); ?>
		</h3>
		<table class="form-table">		
			<tr>
				<th>
					<label for="<?php echo $this->default_author_option_name; ?>"><?php _e( 'Default author', $this->plugin_domain ); ?></label>
				</th>
				<td>
					<?php 
						$selected_author =  get_the_author_meta( $this->default_author_option_name, $user->ID );
						
						$args = array( 
						'name' 				=> $this->default_author_option_name, 
						'selected' 			=> $selected_author,
						'show_option_all' 	=> '  ',
						);
						
						wp_dropdown_users( $args );
					?>
					<span class="description">
						<?php _e( 'Select the default author, this will overwrite the global settings.', $this->plugin_domain ); ?>
					</span>
				</td>
			</tr>
			
		</table>
		<?php 
		}
		
		
	} // end class
	
endif; //end if class_exists
		
