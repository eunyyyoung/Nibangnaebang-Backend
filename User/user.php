<?php

function Login($originJSON){

  if(!(isset($originJSON['id']) && isset($originJSON['pwd']))){
    return sendWrongRequestMsg();
  }

  require_once('./DBConfig/DBConfig.php');

  $STMT = $_CONN->prepare('SELECT COUNT(*), No FROM NN_USER WHERE Id=? and Pwd=? and AuthOk=1');
  @$STMT->bind_param('ss',trim($originJSON['id']), trim($originJSON['pwd']));
  $STMT->execute();
  $RES = $STMT->get_result();

  $jsonObj = array();

  if($ROW = mysqli_fetch_assoc($RES)){

    if($ROW['COUNT(*)'] == 1){
        $jsonObj += [ 'IsExistUser' => 1, 'UserNo' => $ROW['No']];
        return json_encode($jsonObj);
    }
    else {
        $jsonObj += ['IsExistUser' => 0];
        return json_encode($jsonObj);
    }
  }
}


function SignUp($originJSON){

  if(!(isset($originJSON['user']) && isset($originJSON['user']['id']) && isset($originJSON['user']['pwd']) && isset($originJSON['user']['gender'])
   && isset($originJSON['user']['school']) && isset($originJSON['user']['token']))){
    return sendWrongRequestMsg();
  }

  $jsonObj = array();
  require_once('./DBConfig/DBConfig.php');

  $STMT = $_CONN->prepare('SELECT COUNT(*) FROM NN_USER WHERE Id=?');
  @$STMT->bind_param("s", trim($originJSON['user']['id']));
  $STMT->execute();
  $RES = $STMT->get_result();

  if($ROW = mysqli_fetch_assoc($RES)){
    if($ROW['COUNT(*)'] == 1){
      $jsonObj += [ 'ResCode' => 400, 'ResMsg' => 'Already_Exist_ID' ];
      return json_encode($jsonObj);
    }
  }

  if(!isset($_FILES['file'])){
    return sendWrongRequestMsg();
  }

  require_once('./FileManage/fileManage.php');
  $name = uploadFile();

  $STMT = $_CONN -> prepare('INSERT INTO NN_USER(Id, Pwd, Gender, School, AuthOk, Token, IdCardDir) VALUES(?,?,?,?,0,?,?)');
  @$STMT->bind_param('sssiss',  trim($originJSON['user']['id']),
                               trim($originJSON['user']['pwd']),
                               trim($originJSON['user']['gender']),
                               $originJSON['user']['school'],
                               trim($originJSON['user']['token']),
                               trim($name));


  $STMT->execute();

  $STMT = $_CONN->prepare('SELECT COUNT(*) FROM NN_USER WHERE Id=?');
  @$STMT->bind_param("s", trim($originJSON['user']['id']));
  $STMT->execute();
  $RES = $STMT->get_result();

  if($ROW = mysqli_fetch_assoc($RES)){
    if($ROW['COUNT(*)'] == 1){
      $jsonObj += ['ResCode' => 200, 'ResMsg' => 'Success'];
      return json_encode($jsonObj);
    }else {
      $jsonObj += ['ResCode' => 200, 'ResMsg' => 'Fail'];
      return json_encode($jsonObj);
    }
  }
}

 ?>
