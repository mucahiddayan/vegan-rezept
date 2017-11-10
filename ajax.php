<?php 
require_once  $_SERVER["DOCUMENT_ROOT"]."/wp-load.php";

if(isset($_POST["func"])){
    $func = $_POST['func'];    
}else if(isset($_GET['func'])){
    $func = $_GET['func'];
}else{
    echo json_encode(array('message' => 'no request'));
}


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