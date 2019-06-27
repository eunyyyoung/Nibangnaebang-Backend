<?php

function sendWrongRequestMsg(){
  $jsonObj = array();
  $jsonObj += ['ResCode' => 400, 'ResMsg' => 'WRONG_REQUEST'];

  return json_encode($jsonObj);
}

function postMessage($originJSON){
  if(!(isset($originJSON['sendUser']) && isset($originJSON['receiveUser']) && isset($originJSON['msg']))){
    return sendWrongRequestMsg();
  }

  require_once('./DBConfig/DBConfig.php');
  $STMT = $_CONN -> prepare("SELECT COUNT(*),No FROM NN_MESSAGEBOX WHERE (user1No=? and user2No=?) or (user1No=? and user2No=?)");
  @$STMT->bind_param('iiii',$originJSON['sendUser'],$originJSON['receiveUser'],$originJSON['receiveUser'],$originJSON['sendUser']);
  $STMT->execute();
  $RES = $STMT->get_result();

  $ROW = mysqli_fetch_assoc($RES);

  $ROOMNO = 0;
  if($ROW['COUNT(*)'] == 1){
    $ROOMNO = $ROW['No'];
  }else {
    $STMT = $_CONN -> prepare("INSERT INTO NN_MESSAGEBOX(user1No, user2No) VALUES(?,?)");
    @$STMT->bind_param('ii', $originJSON['sendUser'], $originJSON['receiveUser']);
    $STMT->execute();

    $STMT = $_CONN -> prepare("SELECT No FROM NN_MESSAGEBOX WHERE user1No=? and user2No=?");
    @$STMT->bind_param('ii', $originJSON['sendUser'], $originJSON['receiveUser']);
    $STMT->execute();
    $RES = $STMT->get_result();
    $ROW = mysqli_fetch_assoc($RES);

    $ROOMNO = $ROW['No'];
  }

  $STMT = $_CONN -> prepare("INSERT INTO NN_MESSAGE(SendUserNo, ReceiveUserNo, Msg, LogDate, Parent) VALUES(?,?,?,?,?)");
  $logDate = date('Y-m-d H:i:s');
  @$STMT->bind_param('iissi', trim($originJSON['sendUser']), trim($originJSON['receiveUser']),trim($originJSON['msg']), $logDate, $ROOMNO);
  $STMT->execute();

  $STMT = $_CONN -> prepare("SELECT COUNT(*) FROM NN_MESSAGE WHERE LogDate=?");
  @$STMT->bind_param('s', $logDate);
  $STMT->execute();
  $RES = $STMT->get_result();

  $ROW = mysqli_fetch_assoc($RES);
  $jsonObj = array();
  if($ROW['COUNT(*)'] == 1){
    $jsonObj += ['ResCode' => 200, 'ResMsg' => 'Success'];
  }else{
    $jsonObj += ['ResCode' => 200, 'ResMsg' => 'Fail'];
  }

  return json_encode($jsonObj);
}


 ?>
