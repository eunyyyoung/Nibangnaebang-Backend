<?php

function showRoomList(){

    require_once('./DBConfig/DBConfig.php');

    $STMT = $_CONN->prepare('SELECT No, Pay, ALStart, ALEnd, Title FROM NN_ROOM Where IsView = 0 ORDER BY LogDate DESC');
    $STMT->execute();
    $RES = $STMT->get_result();

    $jsonObj = array();

    while($ROW = mysqli_fetch_assoc($RES)){
      array_push($jsonObj, $ROW);
    }

    return json_encode($jsonObj);
}

function findARoom($originJSON){
    require_once('./DBConfig/DBConfig.php');
    $STMT = $_CONN->prepare('SELECT * FROM NN_ROOM WHERE No=?');
    @$STMT->bind_param("i",$originJSON['RoomNo']);
    $STMT->execute();
    $RES = $STMT->get_result();

    $ROW = mysqli_fetch_assoc($RES);

    return json_encode($ROW);
}

function createSellRoom($originJSON){
    $nowDate = date("Y-m-d/H:i:s");

    require_once('./DBConfig/DBConfig.php');
    
    $STMT = $_CONN->prepare("INSERT INTO NN_ROOM(ALStart, ALEnd, Pay, Address, Title, Detail, IsLoad, Seller, IsView, Lat, Lng ,School, LogDate, PayType) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    @$STMT->bind_param("ssisssiiiddsss", trim($originJSON['room']['ALStart']),
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
                                    trim($originJSON['room']['school']),
                                    trim($nowDate),
                                    trim($originJSON['room']['payType']));
    $STMT->execute();
    $RES = $STMT->get_result();
    $jsonObj = array();

    if($RES == false){
        $jsonObj += [ 'code' => 'success', 'RoomNo' => mysqli_insert_id($_CONN)];

        require_once('./FileManage/fileManage.php');
        $names = uploadFile();

        foreach($names as $name){
          $STMT = $_CONN->prepare("INSERT INTO NN_ROOMIMG(RoomNo) VALUES(?)");
          @$STMT->bind_param("i", mysqli_insert_id($_CONN));
          $STMT->execute();
        }

    }else{
        $jsonObj += [ 'code' => 'error', 'msg' => 'create sell room Fail'];
    }
    return json_encode($jsonObj);
}


 ?>
