<?php
header('Content-Type: application/json');
require_once('./Message/message.php');

$postJSON = json_decode(file_get_contents("php://input"), true);

switch($postJSON['query']){
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

  default :
    echo sendWrongRequestMsg(); break;
}
 ?>
