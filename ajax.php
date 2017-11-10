<?php 
require_once  $_SERVER["DOCUMENT_ROOT"]."/wp-load.php";

if(isset($_POST["func"])){
    echo $_POST["func"];
}else{
    var_dump($_POST);
}