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

    private $pluginDirUrl;
    private $slug = 'asana-finder';

    public function __construct(){
        $this->pluginDirUrl = plugin_dir_url( __FILE__ );
        add_action( 'wp_enqueue_scripts', array( $this,'vegan_rezept_styles') );
       
    }


    /**
     * Style and JS Files is being embedded
     */
    public function vegan_rezept_styles(){
        echo $this-> pluginDirUrl;
        wp_enqueue_style( 'vegan_rezept_style', $this->$pluginDirUrl .'css/style.css', '', true );
        wp_enqueue_style('fontAwesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', '3.2.1', 'all' );
        wp_register_script('vegan_rezept_js',$this->$pluginDirUrl . 'js/main.js', array(), '1.0', true );
        wp_localize_script('vegan_rezept_js','veganRezept',array(
                                        'nonce'=>wp_create_nonce( 'wp_rest' ),
                                        'pluginDirUrl' => $this->pluginDirUrl
                                )
                                    );
        wp_enqueue_script( 'vegan_rezept_js');
    }


}

$vegan_rezept = new VeganRezept();


?>