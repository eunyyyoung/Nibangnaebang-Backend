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

 ?>
