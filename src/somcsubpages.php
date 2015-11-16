<?php
/*
Plugin Name: Somc Sub Pages
Description: Display current page sub pages, option to include the plugin's shortcode "[somc-subpages]" or add it as a Widget to the sidebar
Version: 1.0.0
Author: Mile Milosheski
Author URI: https://mk.linkedin.com/in/mmilosheski
*/
if ( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if(class_exists('SomcSubPages')) {    
    register_activation_hook(__FILE__, array('SomcSubPages', 'activate'));
    register_deactivation_hook(__FILE__, array('SomcSubPages', 'deactivate'));
	
    new SomcSubPages();
}

class SomcSubPages {
	
	//Constructor
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'somc-subpages', array( $this, 'somc_subpages_shortcode' ) );
		add_action('admin_menu', array( $this, 'admin_page' ) );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_links' ) );

		if( is_admin() === true ) {
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'jquery-form' );
			wp_enqueue_script( 'scsp-admin-js', plugins_url( 'js/somc-subpages-admin.js', __FILE__ ), array('jquery','jquery-form') );
			add_action( 'wp_ajax_update_options', array( $this, 'update_options' ) );
		}
	}
	

	function plugin_links( $links ) {
	   $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=somc-subpages') ) .'">Settings</a>';
	   return $links;
	}
	// Add admin page
	public function admin_page() {
		if( is_admin() === true ) {
			add_menu_page( 'Somc Sub Pages', 'Somc Sub Pages', 'manage_options', 'somc-subpages', array($this,'render_admin_page'), 'dashicons-feedback', 6 );
		}
	}
	//Actual admin page
	public function render_admin_page() {
		if( is_admin() === true ) {
			?>
			<h3><span class="dashicons-before dashicons-feedback"></span> Somc Sub Pages Settings</h3>
			<form name="somc-subpages" id="somc-subpages" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
			<?php wp_nonce_field( 'wpsocsp-s', 'wpsocspnonce' ); ?>
				<label class="selectit">
					<input type="checkbox" name="scsp-asc-desc-chk"<?php echo esc_attr( get_option('scsp-asc-desc') )== 1 ? ' checked="checked"' : ''; ?>> Tick for DESC / Default is ASC order
				</label>
				<input type="submit" value="Save" />
			</form>
			<?php
		}
	}
	//Actual function to fetch all the sub pages of the current page where the shortcode is called or widget is called in widget area
	public function somc_subpages() {

		global $post;
		
		//Check if is page, we don't want this to go fire up, for posts/custom post types
		if( is_page() ) {
			$somc_subpages_content = '<ul class="somc-subpages">';
			$somc_subpages_content .= '<li class="somc-current-page">'.$post->post_title.'   <a id="hide" href="javascript:void();"><i class="fa fa-minus"></i></a></li>';
			if (get_option('scsp-asc-desc') == 0 ) {
				$SomcSubPages = get_pages( array( 'child_of' => $post->ID, 'sort_column' => 'post_date', 'sort_order' => 'asc' ) );
			} else {
				$SomcSubPages = get_pages( array( 'child_of' => $post->ID, 'sort_column' => 'post_date', 'sort_order' => 'desc' ) );
			}	
			foreach( $SomcSubPages as $SomcSubpage ) {		
				$num_of_sub_pages = count($SomcSubpage);
				if ( $num_of_sub_pages > 0 ) {// Check if no sub pages
					$somc_subpages_content .= '<li class="somc-sub-page"><i class="fa fa-angle-double-right"></i>  <a href="'.get_page_link( $SomcSubpage->ID ).'">'.$SomcSubpage->post_title.'</a></li>';
				}
			}
			$somc_subpages_content .= '</ul>';
			
			return $somc_subpages_content;
		}
		
	}

	//registering the shortcode 
	public function somc_subpages_shortcode($atts) {
		
		$atts = shortcode_atts( array(), $atts, 'somc-subpages' );

		$content = new SomcSubPages();
		
		return $content->somc_subpages();
	}

	// this will enque the plugins style for front end list
	public function enqueue_assets() {
		wp_enqueue_style( "wpscsp-style", plugins_url( 'css/somcsubpages.css', __FILE__ ));
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'jquery-form' );
		wp_enqueue_script( "wpscp-front-js", plugins_url( 'js/somc-subpages-front.js', __FILE__ ), array('jquery','jquery-form'));
	}

	//This callback adds our button to the toolbar
	public function somc_subpages_add_button($buttons) {
	    //Add the button ID to the $button array
		$buttons[] = 'somc_subpages';
		return $buttons;
	}
	public function update_options() {
		check_ajax_referer( 'wpsocsp-s', 'wpsocspnonce' );
		
		if( isset( $_POST[ 'scsp-asc-desc-chk' ] ) ) {
			update_option( 'scsp-asc-desc', 1 );
		} else {
			update_option( 'scsp-asc-desc', 0 );
		}
		wp_die();
	}
	public function activate() {
		$config[ 'scsp-asc-desc' ] = 0;
		
		foreach( $config as $key => $value ) {
			delete_option( $key );
			add_option($key, $value);
		}
	}
	public function deactivate() {
		$config = array('scsp-asc-desc');
		foreach( $config as $key ) {
			delete_option( $key );
		}
	}
}

class SomcSubPages_Widget extends WP_Widget {
    //Constructor
    public function __construct() {
        $widget_ops = array(
            'classname' => 'widget_somc_subpages', 
            'description' => __( 'Somc Sub Pages Widget') 
        );
        parent::__construct('SomcSubPages', __('SomcSubPages Widget'), $widget_ops);
    }
    //Registering the widget atts and output
    public function widget( $args, $instance ) {
        $title = apply_filters( 
            'widget_title', 
             empty($instance['title']) ? '' : $instance['title'], 
             $instance, 
             $this->id_base 
        );

        echo $args['before_widget'];

        if ( $title )
        { echo $args['before_title'] . $title . $args['after_title']; }

        echo do_shortcode('[somc-subpages]');

        echo $args['after_widget'];
    }
    //Widget form for admin panel Appeareances/Widgets
    public function update( $new_instance, $old_instance ) {
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
    //Widget form for admin panel Appeareances/Widgets
    public function form( $instance ) {
        //Defaults
        $title = esc_attr( $instance['title'] ); 
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
	<?php
	}
}

// Register your Widget
function SomcSubPages_init() {
    register_widget('SomcSubPages_Widget');
}
add_action('widgets_init', 'SomcSubPages_init');