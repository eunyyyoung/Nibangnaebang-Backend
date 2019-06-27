<?php
header('Content-Type: application/json');
require_once('./Message/message.php');

$postJSON = json_decode(file_get_contents("php://input"), true);

switch($postJSON['query']){
  case 'Login':
    require_once('./User/user.php');
    echo Login($postJSON);
    break;
  default :
    echo sendWrongRequestMsg(); break;
}
 ?>
