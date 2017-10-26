<?php
/**
* Plugin Name: Vegan Rezept
* Plugin URI: https://mücahiddayan.com
* Description: Vegan Rezept
* Version: 1.0.0
* Author: Mücahid Dayan
* Author URI: https://mücahiddayan.com
* License: GPL2
*/

class VeganRezept {
    
    private $slug = 'asana-finder';
    
    public function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this,'vegan_rezept_styles') );
        add_action( 'bp_core_activated_user', array( $this,'bp_custom_registration_role'),10 , 3);
        add_action( 'bp_setup_nav', array( $this,'add_rezept_tab') , 100);
    }
    
    
    /**
    * Style and JS Files is being embedded
    */
    public function vegan_rezept_styles(){
        wp_enqueue_style( 'vegan_rezept_style', plugin_dir_url( __FILE__ ) .'css/style.css', '', true );
        wp_enqueue_style('fontAwesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', '3.2.1', 'all' );
        wp_register_script('vegan_rezept_js',plugin_dir_url( __FILE__ ) . 'js/main.js', array(), '1.0', true );
        wp_localize_script('vegan_rezept_js','veganRezept',array(
            'nonce'=>wp_create_nonce( 'wp_rest' ),
            'pluginDirUrl' => plugin_dir_url( __FILE__ )
            )
        );
        wp_enqueue_script( 'vegan_rezept_js');
    }
    
    /**
     * If a user registers
     * he gets the User Role "Veganer"
     */
    public function bp_custom_registration_role($user_id, $key, $user) {
        $userdata = array();
        $userdata['ID'] = $user_id;
        $userdata['role'] = 'veganer'; 
        wp_update_user($userdata);   
    }

    public function add_rezept_tab(){
        global $bp;
        
        bp_core_new_nav_item( array(
            'name'                  => 'Rezepte',
            'slug'                  => 'recipes',
            'parent_url'            => $bp->displayed_user->domain,
            'parent_slug'           => $bp->profile->slug,
            'screen_function'       => array($this,'recipes_screen'),			
            'position'              => 200,
            'default_subnav_slug'   => 'recipes'
        ) );
    }

    public function recipes_screen(){
        add_action( 'bp_template_title', array($this,'get_tab_title') );
        add_action( 'bp_template_content', array($this,'get_recipes') );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
    }

    public function get_tab_title(){
        echo 'Rezepte';
    }

    public function get_recipes(){
        $args = array(
            'author'        =>  bp_displayed_user_id(),
            'orderby'       =>  'post_date',
            'order'         =>  'ASC',
            'post_type'     =>  'recipe',
        );
        $query = new WP_Query($args);
        ob_start();
        echo '<script type="text/javascript">var recipes= `'.json_encode($query->posts).'`;</script>';
        ?>
        <recipes ng-app="app" ng-controller="mainController" r-init="recipes"></recipes>
        <?php
        ob_end_clean();
        
        
    }

    #bp_displayed_user_id()
}

$vegan_rezept = new VeganRezept();


?>