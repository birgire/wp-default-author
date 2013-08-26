<?php
/*
Plugin Name: Default Author
Plugin URI:  http://wordpress.org/extend/plugins/wp-default-author/
Description: Default author settings
Version:     1.0.2
Author:      birgire
Author URI:  http://profiles.wordpress.org/birgire
License:     GPLv2
*/
    
/**
 * Init the Default_Author class
*/
if( ! class_exists( 'Default_Author' ) ):
    
    /**
    * Calls the class
    */
    function call_WP_Default_Author() {

        // only activate if the current user is an Editor
        if( current_user_can( 'edit_others_posts' ) ):
            return new WP_Default_Author();
        else:
            return;
        endif;
    }
    
    if ( is_admin() ):
        add_action( 'init', 'call_WP_Default_Author' );
    endif;
    
    /**
     * Class Default_Author
     */
    class WP_Default_Author{
        
        protected $plugin_domain                = 'wpdeau';     
        protected $default_author_option_name   = 'wpdeau_default_author';
        
        /**
         * Class init
         */
        public function __construct(){

            add_action( 'admin_init',               array( &$this,'init' ) );
            add_action( 'admin_init',               array( &$this, 'show_global_settings') );
            add_action( 'show_user_profile',        array( &$this, 'show_settings' ) );
            add_action( 'edit_user_profile',        array( &$this, 'show_settings' ) );
            add_action( 'personal_options_update',  array( &$this, 'save_settings' ) );
            add_action( 'edit_user_profile_update', array( &$this, 'save_settings' ) );
            add_filter( 'wp_insert_post_data',      array( &$this, 'wp_insert_post_data_callback' ), '99', 2 );
            
        }
        
        /**
         * Modify the post author, just before the insert new post
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
            
            $default_author = get_the_author_meta( $this->default_author_option_name , $current_user->ID );
            
            if( $default_author ):
                $data['post_author'] = $default_author;
            endif;
            
            return $data;
        }
        
        /**
         * Save settings
         */
        public function save_settings( $user_id ) {
            
            // only allow editors to save
            if ( !current_user_can( 'edit_others_posts' ) ):
                return false;
            endif;
            
            // check if the input is set
            $value = 0;
            if( isset( $_POST[$this->default_author_option_name] ) ):
                $value = $_POST[$this->default_author_option_name];
            endif;
            
            // update user meta
            if( $value > 0 ):
                update_usermeta( $user_id, $this->default_author_option_name, $value);
            endif;
        }
        
        /**
         * Settings page
         */     
        public function show_settings( $user ) { ?>
        <h3>
            <?php _e('Default Author Settings', $this->plugin_domain ); ?>
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
                        'name'              => $this->default_author_option_name, 
                        'selected'          => $selected_author,
                        'show_option_all'   => '  ',
                        );
                        
                        wp_dropdown_users( $args );
                    ?>
                    <span class="description">
                        <?php _e('Please select the default author', $this->plugin_domain ); ?>
                    </span>
                </td>
            </tr>
            
        </table>
        <?php }
        
        public function show_global_settings() {
           add_settings_section('global_deau', 'Global Default Author', array( &$this, 'global_deau_callback'), 'writing');
        }
        
        public function global_deau_callback(){
           echo '<p>Select global default author:<p>';
        }

    } // end class
    
endif; //end if class_exists
