<?php 
require_once  $_SERVER["DOCUMENT_ROOT"]."/wp-load.php";

if(isset($_POST["func"])){
    $func = $_POST['func'];
    if($func === 'add_to_my_book'){
        $recipe_id = $_POST['recipe_id'];
        if(!empty($recipe_id)){
            try{
                $vegan_rezept->add_to_my_book();
            }catch(Exception $e){
                echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
            }
        }
    }
    if($func === 'get_recipes_from_my_book'){
        echo json_encode(array(
            "recipes" => $vegan_rezept->get_recipes_from_my_book()
        ),0,10);
    }
}

echo  json_encode(array(
    "recipes" => $vegan_rezept->get_recipes_from_my_book()));