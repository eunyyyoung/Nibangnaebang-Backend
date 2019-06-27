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
  case 'Create':
    require_once('./Room/room.php');
    echo createSellRoom($postJSON);
    break;

  case 'Find':
    require_once('./Room/room.php');
    echo findARoom($postJSON);
    break;

  case 'ShowList':
    require_once('./Room/room.php');
    echo showRoomList($postJSON);
    break;

  case 'PostMsg' :
    echo postMessage($postJSON);
    break;
  default :
    echo sendWrongRequestMsg(); break;
}
 ?>
