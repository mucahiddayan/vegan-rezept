<?php 
require_once  $_SERVER["DOCUMENT_ROOT"]."/wp-load.php";

if(isset($_POST["func"]) || isset($_GET['func'])){
    $func = $_POST['func'] | $_GET['func'];
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
        try{
            $ids = $vegan_rezept->get_recipes_from_my_book();
        }catch(Exception $e){
            $ids = $e->getMessage();
        }
        echo json_encode(array(
            "recipes" => $ids ));
    }
}else{
    echo json_encode(array('message' => 'no request'));
}
