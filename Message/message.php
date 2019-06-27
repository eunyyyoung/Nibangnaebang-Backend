<?php

function sendWrongRequestMsg(){
  $jsonObj = array();
  $jsonObj += ['ResCode' => 400, 'ResMsg' => 'WRONG_REQUEST'];

  return json_encode($jsonObj);
}

function getSummaryMessage($originJSON){
  if(!(isset($originJSON['nowUser']))){
    return sendWrongRequestMsg();
  }

  require_once('./DBConfig/DBConfig.php');
  $STMT = $_CONN -> prepare("SELECT * FROM NN_MESSAGEBOX WHERE (user1No=? or user2No=?)");
  @$STMT->bind_param("ii", $originJSON['nowUser'], $originJSON['nowUser']);
  $STMT->execute();
  $RES = $STMT->get_result();
  $jsonObj = array();
  while($ROW = mysqli_fetch_assoc($RES)){

    $OtherUser = 0;

    if($ROW['user1No'] == $originJSON['nowUser']){
      $OtherUser = $ROW['user2No'];
    }else if($ROW['user2No'] == $originJSON['nowUser']){
      $OtherUser = $ROW['user1No'];
    }

    $STMT = $_CONN -> prepare("SELECT Msg FROM NN_MESSAGE WHERE SendUserNo=? ORDER BY No DESC LIMIT 1");
    @$STMT->bind_param("i", $OtherUser);
    $STMT->execute();
    $RES2 = $STMT->get_result();
    $ROW2 = mysqli_fetch_assoc($RES2);
    $MSG = $ROW2['Msg'];

    if($MSG == NULL){
      $MSG = "";
    }

    array_push($jsonObj,array('roomNo'=>$ROW['No'], 'otherUser' => $OtherUser, 'lastMsg' => $MSG));

  }
  return json_encode($jsonObj);
}

function getFullMessage($originJSON){
  if(!(isset($originJSON['roomNo']))){
    return sendWrongRequestMsg();
  }
  require_once('./DBConfig/DBConfig.php');

  $STMT = $_CONN -> prepare("SELECT * FROM NN_MESSAGE WHERE Parent=? ORDER BY no ASC");
  @$STMT->bind_param("i", $originJSON['roomNo']);
  $STMT->execute();

  $RES = $STMT->get_result();
  $jsonObj = array();
  while($ROW = mysqli_fetch_assoc($RES)){
    array_push($jsonObj, array('no'=>$ROW['No'],
                               'sendUser'=>$ROW['SendUserNo'],
                               'receiveUser'=>$ROW['ReceiveUserNo'],
                               'msg'=>$ROW['Msg'],
                               'logDate'=>$ROW['LogDate']));
  }

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
