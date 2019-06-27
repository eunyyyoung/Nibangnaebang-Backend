<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Content-Type: application/json');
require_once('./Message/message.php');

$postJSON = json_decode($_POST['jsonString'],true);

switch($postJSON['query']){
  case 'Login':
    require_once('./User/user.php');
    echo Login($postJSON);
    break;
  case 'SignUp' :
    require_once('./User/user.php');
    echo SignUp($postJSON);
    break;
  default :
    echo sendWrongRequestMsg(); break;
}
 ?>
