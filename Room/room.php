<?php

function showRoomList(){

    require_once('./DBConfig/DBConfig.php');

    $STMT = $_CONN->prepare('SELECT NN_ROOM.No, NN_ROOM.Pay, NN_ROOM.ALStart, NN_ROOM.ALEnd, NN_ROOM.Title, NN_ROOM.School, NN_ROOMIMG.Dir
                                FROM NN_ROOM LEFT JOIN NN_ROOMIMG ON NN_ROOM.No = NN_ROOMIMG.RoomNo
                                Where IsView = 0 ORDER BY LogDate DESC');
    $STMT->execute();
    $RES = $STMT->get_result();

    $jsonObj = array();
    $jsonObj2 = array();

    while($ROW = mysqli_fetch_assoc($RES)){
      array_push($jsonObj, $ROW);
    }
    $jsonObj2 += [ 'code' => 'success', 'room' => $jsonObj];
    return json_encode($jsonObj2);
}

function findARoom($originJSON){
    require_once('./DBConfig/DBConfig.php');

    $STMT = $_CONN->prepare('SELECT NN_ROOM.No AS Num,Seller,Title,Address,Lat,Lng,ALStart,ALEnd,Detail,Pay,IsLoad,LogDate,IsView,PayType,School,SameGender
      FROM NN_ROOM RIGHT JOIN NN_ROOMIMG ON NN_ROOM.No = NN_ROOMIMG.RoomNo WHERE NN_ROOM.No=?');

    @$STMT->bind_param("i",$originJSON['RoomNo']);

    $STMT->execute();

    $RES = $STMT->get_result();

    $ROW = mysqli_fetch_assoc($RES);
    $jsonObj = array();
    $jsonObj += ['No'=>$ROW['Num'],
                        'Seller'=>$ROW['Seller'],
                        'Title'=>$ROW['Title'],
                        'Address'=>$ROW['Address'],
                        'Lat'=>$ROW['Lat'],
                        'Lng'=>$ROW['Lng'],
                        'ALStart'=>$ROW['ALStart'],
                        'ALEnd'=>$ROW['ALEnd'],
                        'Detail'=>$ROW['Detail'],
                        'Pay'=>$ROW['Pay'],
                        'IsLoad'=>$ROW['IsLoad'],
                        'LogDate'=>$ROW['LogDate'],
                        'IsView'=>$ROW['IsView'],
                        'PayType'=>$ROW['PayType'],
                        'School'=>$ROW['School'],
                        'SameGender'=>$ROW['SameGender']];

     $jsonObj['images'] = array();

     $STMT = $_CONN->prepare('SELECT Dir FROM NN_ROOMIMG WHERE RoomNo=?');
     @$STMT->bind_param("i", $originJSON['RoomNo']);
     $STMT->execute();
     $RES = $STMT->get_result();

     while($ROW = mysqli_fetch_assoc($RES)){
       array_push($jsonObj['images'], array('RoomNo'=>$originJSON['RoomNo'], 'Dir'=>$ROW['Dir']));
     }


    return json_encode($jsonObj);
}

function createSellRoom($originJSON){
    $nowDate = date("Y-m-d/H:i:s");

    require_once('./DBConfig/DBConfig.php');

    $STMT = $_CONN->prepare("INSERT INTO NN_ROOM(ALStart, ALEnd, Pay, Address, Title, Detail, IsLoad, Seller, IsView, Lat, Lng, SameGender ,School, LogDate, PayType) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    @$STMT->bind_param("ssisssiiiddisss", trim($originJSON['room']['ALStart']),
                                    trim($originJSON['room']['ALEnd']),
                                    $originJSON['room']['pay'],
                                    trim($originJSON['room']['address']),
                                    trim($originJSON['room']['title']),
                                    trim($originJSON['room']['detail']),
                                    $originJSON['room']['isLoad'],
                                    $originJSON['room']['userNo'],
                                    $originJSON['room']['isView'],
                                    $originJSON['room']['Lat'],
                                    $originJSON['room']['Lng'],
                                    $originJSON['room']['sameGender'],
                                    trim($originJSON['room']['school']),
                                    trim($nowDate),
                                    trim($originJSON['room']['payType']));
    $STMT->execute();
    $RES = $STMT->get_result();
    $jsonObj = array();

    if($RES == false){
        $jsonObj += [ 'code' => 'success', 'RoomNo' => mysqli_insert_id($_CONN)];
    }else{
        $jsonObj += [ 'code' => 'error', 'msg' => 'create sell room Fail'];
    }
    return json_encode($jsonObj);
}

function uploadRoomImg($originJSON){
  if(!(isset($_FILES) && isset($originJSON['roomNo']))){
      return sendWrongRequestMsg();
  }

  require_once('./FileManage/fileManage.php');
  $names = uploadFile();

  require_once('./DBConfig/DBConfig.php');

  $STMT = $_CONN->prepare("INSERT INTO NN_ROOMIMG(RoomNo,Dir) VALUES(?,?)");
  @$STMT->bind_param("is",$originJSON['roomNo'], trim($names));
  $STMT->execute();

  $STMT = $_CONN->prepare("SELECT COUNT(*) FROM NN_ROOMING WHERE RoomNo=? and Dir=?");
  @$STMT->bind_param("is",$originJSON['roomNo'], $names);
  $STMT->execute();
  $RES = $STMT->get_result();

  $ROW = mysqli_fetch_assoc($RES);

  $jsonObj = array();

  if($ROW['COUNT(*)'] >= 1){
    $jsonObj += [ 'code' => 'success'];
  }else {
    $jsonObj += [ 'code' => 'fail'];
  }

  return json_encode($jsonObj);
}


 ?>