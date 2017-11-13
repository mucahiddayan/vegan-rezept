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
    
    protected $slug = 'asana-finder';
    protected $recipe_book = '_recipe_book';
    protected $errors = array(
        'no_request_params' => array(
            'status' => 9000,
            'message'=> 'passed no parameter'
        ),
        'no_user_id' => array(
            'status' => 9001,
            'message'=> 'user id is not set'
        ),
        'no_user' => array(
            'status' => 9002,
            'message'=> 'no user with this id'
        ),
        'recipe_not_exist' => array(
            'status' => 9003,
            'message'=> 'recipe with this id does not exist'
        ),
        'no_veganer' => array(
            'status' => 9004,
            'message'=> 'you are not a veganer !'
        ),
        'no_recipe_id' => array(
            'status' => 9005,
            'message'=> 'recipe id is not set'
        ),
    );
    
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
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'userID' => get_current_user_id(),
            'book' => $this->get_book_content(get_current_user_id()),
            'bookContent' => json_encode($this->get_recipes_from_book())
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

        bp_core_new_nav_item( array(
            'name'                  => 'Rezept Buch',
            'slug'                  => 'recipe_book',
            'parent_url'            => $bp->displayed_user->domain,
            'parent_slug'           => $bp->profile->slug,
            'screen_function'       => array($this,'recipe_book'),			
            'position'              => 200,
            'default_subnav_slug'   => 'recipe_book'
        ) );
    }

    public function recipe_book(){
        add_action( 'bp_template_title', function(){echo "Rezept Book";} );
        add_action( 'bp_template_content', array($this,'show_recipe_book') );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
    }

    public function show_recipe_book(){
        ?>       
        <recipes ng-app="app" r-type="book"></recipes>
        <?php
    }

    public function get_recipes_from_book(){
        #$recipes = $this->get_book_content(get_current_user_id());
        $recipes = $this->get_book_content(bp_displayed_user_id());
        if(empty($recipes)){
            return;
        }
        $arr = array();

        foreach($recipes as $id){
            array_push($arr,get_post($id));
        }
        return $this->extract_to_array($arr);
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
        foreach($inArray as $recipe){            
            $recipe->img_url = get_the_post_thumbnail_url($recipe->ID,'td_218x150');
            $recipe->ingredients = get_post_meta($recipe->ID,'recipe_ingredients',false);
        }
        return $inArray;
    }

    public function show_recipes(){              
        ?>       
        <recipes ng-app="app"></recipes>
        <?php
               
    }

    ###########################  REST API FUNCTIONS  ##################################
    protected function get_book_content($userID){
        try{
            $book_content = get_post_meta($userID,$this->recipe_book,false);
        }catch(Exception $e){
            return $e->getMessage();
        }
        if(empty($book_content)){
            return;
        }
        if($book_content[0] == ''){
            return array();
        }
        return $book_content[0];        
    }

    public function get_my_book_content($request){
        $userID = get_current_user_id();
        return $this->get_book_content($userID);
    }

    protected function remove_all_from_book($userID){
        try{
            $book_content = update_post_meta($userID,$this->recipe_book,'');
        }catch(Exception $e){
            return $e->getMessage();
        }
        
        return $book_content;   
    }

    /* protected */ function remove_from_book($userID,$recipeID){
        return $this->update_book($userID,$recipeID,'remove');
    }

    public function remove_from_my_book($request){
        $userID = get_current_user_id();
        $params = $request->get_params();
        if(empty($params['recipeID'])){
            $result = $this->remove_all_from_book($userID);
        }else{
            $result = $this->remove_from_book($userID,$params['recipeID']);
        }
        return $result;
    }

    protected function add_to_book($userID,$recipeID){
        $res;
        if(empty($userID) || empty($recipeID)){
            if(empty($userID)){
                $res =  'User ID is empty';
            }
            if(empty($recipeID)){
                $res .= ' Recipe ID is empty';
            }
            return $res;
        }else{
            try{
                $recipe = get_post_type($recipeID); 
            }catch(Exception $e){
                return $e->getMessage();
            }
            if($recipe === 'recipe'){
                try{
                    $res = $this->update_book($userID,$recipeID,'add'); 
                }catch(Exception $e){
                    $res = $e->getMessage();
                }
            }else{
                $res = 'Recipe with ID '.$recipeID.' does not exist';
            }
            return $res;
        }
    }

    public function add_to_my_book($request){
        $userID = get_current_user_id();
        $params = $request->get_params();
        try{
            $res = $this->add_to_book($userID,$params['recipeID']);
        }catch(Exception $e){
            $res = $e->getMessage();
        }
        return $res;
    }

    protected function update_book($userID,$recipeID,$type){
        if(empty($userID) || empty($recipeID) ||empty($type)){
            return 'Any one of userID,recipeID,type can not be empty';
        }
        
        $current = get_post_meta($userID,$this->recipe_book,false)[0];
        $current = !is_array($current)?array():$current;
        if($type === 'add'){
            if(!in_array($recipeID,$current)){
                array_push($current,$recipeID);
            }            
        }
        if($type === 'remove'){
            $current = $this->remove_item_from_array($recipeID,$current);            
        }
        return update_post_meta($userID,$this->recipe_book,$current);
    }

    protected function remove_item_from_array($item,$arr){
        $newArr = array();
        foreach($arr as $i){
            if($i !== $item){
                array_push($newArr,$i);
            }
        }
        return $newArr;
    }

    public function test(){
        return 1;
    }

    public function custom_rest_api_end_points(){
        register_rest_route( 'wp/v2', '/mybook/', 
            array(
                array(
                    'methods' => 'GET',
                    'callback' => array($this,'get_my_book_content'),
                    'permission_callback' => function () {
                        return current_user_can( 'veganer' );
                    }
                ),
                array(
                    'methods' => 'POST',
                    'callback' => array($this,'add_to_my_book'),
                    'permission_callback' => function () {
                        return current_user_can( 'veganer' );
                    }
                ),
                array(
                    'methods' => 'DELETE',
                    'callback' => array($this,'remove_from_my_book'),
                    'permission_callback' => function () {
                        return current_user_can( 'veganer' );
                    }
                ),
            ) 
        );      
    }

    #bp_displayed_user_id()
}

$vegan_rezept = new VeganRezept();

?>