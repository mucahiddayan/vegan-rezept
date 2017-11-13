<?php 

require_once  $_SERVER["DOCUMENT_ROOT"]."/wp-load.php";

#$vegan_rezept->remove_all_from_book(21);
#echo $vegan_rezept->add_to_book(21,48713);
#echo $vegan_rezept->add_to_book(21,48766);



class Test extends VeganRezept{
    
    function rfb($uid,$rid){
        parent::remove_from_book($uid,$rid);
    }
    
    function gbc($uid){
        echo '<pre>';
        print_r(parent::get_book_content($uid));
        echo '</pre>';
    }
    
    function atb($uid,$rid){
        echo parent::add_to_book($uid,$rid);
    }
    
    function rafb($uid){
        echo parent::remove_all_from_book($uid);
    }
}

$test = new Test();

#$test-> rfb(21,48713);
#$test-> atb(21,12);
$test-> rafb(21);
$test-> gbc(21);
