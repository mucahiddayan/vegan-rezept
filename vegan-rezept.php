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
    private $recipe_book = '_recipe_book';
    
    public function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this,'vegan_rezept_styles') );
        add_action( 'bp_core_activated_user', array( $this,'bp_custom_registration_role'),10 , 3);
        add_action( 'bp_setup_nav', array( $this,'add_rezept_tab') , 100);
        add_action( 'rest_api_init', array( $this,'custom_rest_api_end_points'));
    }
    
    
    /**
    * Style and JS Files is being embedded
    */
    public function vegan_rezept_styles(){
        wp_enqueue_style( 'vegan_rezept_style', plugin_dir_url( __FILE__ ) .'css/style.css', '', true );
        wp_enqueue_style('fontAwesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', '3.2.1', 'all' );
        wp_register_script('vegan_rezept_js',plugin_dir_url( __FILE__ ) . 'js/main.js', array(), '1.0', true );
        wp_enqueue_script('angularjs','https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js', '1.0', true );
        wp_localize_script('vegan_rezept_js','veganRezept',array(
            'nonce'=>wp_create_nonce( 'wp_rest' ),
            'pluginDirUrl' => plugin_dir_url( __FILE__ ),
            'recipes' => json_encode($this->get_recipes()),
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
        add_action( 'bp_template_content', array($this,'show_recipes') );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
    }

    public function get_tab_title(){
        echo 'Rezepte';
    }

    public function get_recipes($id = 'X'){
        $id = $id == 'X'?bp_displayed_user_id():$id;
        $args = array(
            'author'        =>  $id,
            'orderby'       =>  'post_date',
            'order'         =>  'ASC',
            'post_type'     =>  'recipe',
        );
        $query = new WP_Query($args);
        $recipes = $this->extract_to_array($query->posts);
        return $recipes;
    }

    public function extract_to_array($inArray){
        $newPost;
        foreach($inArray as $recipe){            
            $recipe->img_url = get_the_post_thumbnail_url($recipe->ID,'td_218x150');
            $recipe->ingredients = get_post_meta($recipe->ID,'recipe_ingredients',true);
        }
        return $inArray;
    }

    public function show_recipes(){              
        ?>       
        <recipes ng-app="app"></recipes>
        <?php
               
    }

    public function add_to_my_book($recipe_id){
        if(!is_user_logged_in() || !current_user_can('veganer') ){
            throw new Exception("Du bist kein Veganer! Verpiss dich");
        }
        if(empty($recipe_id)){
            throw new Exception("Rezept Id darf nicht fehlen");
        }
        try{
            $post = get_post($recipe_id);
        } catch(Exception $e){
            echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
        }
        if(!$post){
            throw new Exception("Dieses Rezept existiert nicht");
        }
        $userID = get_current_user_id();
        update_post_meta($userID,$this->recipe_book,$recipe_id);
    }

    public function remove_from_my_book($recipe_id){
        $current = $this->get_recipes_from_my_book();
    }

    public function get_recipes_from_my_book(){
        $userID = get_current_user_id();
        try{
            $recipe_ids = get_post_meta($userID,$this->recipe_book,false);
        }catch(Exception $e){
            echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
        }        
        return $userID;
    }

    public function custom_rest_api_end_points(){
        register_rest_route( 'wp/v2', '/book/', 
            array(
                array(
                    'methods' => array('GET','POST'),
                    'callback' => array($this,'get_recipes_from_my_book'),
                ),
            ) 
        );      
    }

    #bp_displayed_user_id()
}

$vegan_rezept = new VeganRezept();

?>