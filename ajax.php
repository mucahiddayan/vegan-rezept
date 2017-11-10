<?php 
require_once  $_SERVER["DOCUMENT_ROOT"]."/wp-load.php";

if(isset($_POST['func'])){
    echo json_encode(array('output'=>$_POST['func']));
}