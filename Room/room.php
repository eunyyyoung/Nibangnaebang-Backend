<?php

function showRoomList(){

    require_once('./DBConfig/DBConfig.php');

    $STMT = $_CONN->prepare('SELECT NN_ROOM.No, NN_ROOM.Pay, NN_ROOM.ALStart, NN_ROOM.ALEnd, NN_ROOM.Title, NN_ROOM.School, NN_ROOMIMG.Dir
                                FROM NN_ROOM LEFT JOIN NN_ROOMIMG ON NN_ROOM.No = NN_ROOMIMG.RoomNo
                                Where IsView = 1 ORDER BY LogDate DESC');
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

    $STMT = $_CONN->prepare('SELECT NN_ROOM.No AS Num,Seller,(SELECT Id FROM NN_USER WHERE No=NN_ROOM.Seller) AS SellerName, (SELECT Gender FROM NN_USER WHERE No=NN_ROOM.Seller) AS SellerGender,Title,Address,ALStart,ALEnd,Detail,Pay,LogDate,IsView,School,SameGender
      FROM NN_ROOM RIGHT JOIN NN_ROOMIMG ON NN_ROOM.No = NN_ROOMIMG.RoomNo WHERE NN_ROOM.No=?');

    @$STMT->bind_param("i",$originJSON['RoomNo']);

    $STMT->execute();

    $RES = $STMT->get_result();

    $ROW = mysqli_fetch_assoc($RES);
    $jsonObj = array();
    $jsonObj += ['No'=>$ROW['Num'],
                        'Seller'=>$ROW['Seller'],
                        'SellerName' => $ROW['SellerName'],
                        'SellerGender' => $ROW['SellerGender'],
                        'Title'=>$ROW['Title'],
                        'Address'=>$ROW['Address'],
                        'ALStart'=>$ROW['ALStart'],
                        'ALEnd'=>$ROW['ALEnd'],
                        'Detail'=>$ROW['Detail'],
                        'Pay'=>$ROW['Pay'],
                        'LogDate'=>$ROW['LogDate'],
                        'IsView'=>$ROW['IsView'],
                        'School'=>$ROW['School'],
                        'RoomNo' => $ROW['Num'],
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

    $STMT = $_CONN->prepare("INSERT INTO NN_ROOM(ALStart, ALEnd, Pay, Address, Title, Detail, Seller, SameGender ,School, LogDate, IsView) VALUES(?,?,?,?,?,?,?,?,?,?,1)");
    @$STMT->bind_param("ssisssiiss", trim($originJSON['room']['ALStart']),
                                    trim($originJSON['room']['ALEnd']),
                                    $originJSON['room']['pay'],
                                    trim($originJSON['room']['address']),
                                    trim($originJSON['room']['title']),
                                    trim($originJSON['room']['detail']),
                                    $originJSON['room']['userNo'],
                                    $originJSON['room']['sameGender'],
                                    trim($originJSON['room']['school']),
                                    trim($nowDate));
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

  $STMT = $_CONN->prepare("SELECT COUNT(*) FROM NN_ROOMIMG WHERE RoomNo=? and Dir=?");
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

function searchFilter($originJSON){
  if(!(isset($originJSON['searchKey']))){
      return sendWrongRequestMsg();
  }

  $totalQuery = "SELECT NN_ROOM.No AS Num,Seller,(SELECT Id FROM NN_USER WHERE No=NN_ROOM.Seller) AS SellerName, (SELECT Gender FROM NN_USER WHERE No=NN_ROOM.Seller) AS SellerGender,Title,Address,ALStart,ALEnd,Detail,Pay,LogDate,IsView,School,SameGender FROM NN_ROOM WHERE School='".$originJSON['searchKey']."' and IsView=1";

  if(isset($originJSON['opt'])){
    $optStr = $originJSON['opt'];
    $sharpPos = strpos($optStr, "#");

    while($sharpPos !== false){
      $orPos = strpos($optStr, "|");
      $oneStr = subStr($optStr, $sharpPos+1, $orPos-$sharpPos);
      $sameSymPos = strpos($oneStr, "=");
      $keyStr = substr($oneStr, 0, $sameSymPos);
      $valueStr = substr($oneStr,$sameSymPos+1, -1);

      switch($keyStr){
        case 'Price' :
        $totalQuery.=" AND ";
          $totalQuery .= "Pay <=".$valueStr;
          break;
        case 'Gender' :
          //$totalQuery .= "Gender=".$valueStr;
          break;
        case 'During' :
        $totalQuery.=" AND ";
          $waveSymPos = strpos($valueStr, "~");
          $prevDate = substr($valueStr,0,$waveSymPos);
          $backDate = substr($valueStr, $waveSymPos + 1);

          $totalQuery .= "(date('".$backDate."') >= ALStart AND date('".$prevDate."') <= ALEnd)";
          break;
      }

      $optStr = substr($optStr, $orPos + 1);
      $sharpPos = strpos($optStr, "#");
    }
  }
  require_once('./DBConfig/DBConfig.php');
  $RES = mysqli_query($_CONN, $totalQuery);
  $jsonObj = array();
  while($ROW = mysqli_fetch_assoc($RES)){

          array_push($jsonObj,array('No'=>$ROW['Num'],
                      'Seller'=>$ROW['Seller'],
                      'SellerName' => $ROW['SellerName'],
                      'SellerGender' => $ROW['SellerGender'],
                      'Title'=>$ROW['Title'],
                      'Address'=>$ROW['Address'],
                      'ALStart'=>$ROW['ALStart'],
                      'ALEnd'=>$ROW['ALEnd'],
                      'Detail'=>$ROW['Detail'],
                      'Pay'=>$ROW['Pay'],
                      'LogDate'=>$ROW['LogDate'],
                      'IsView'=>$ROW['IsView'],
                      'School'=>$ROW['School'],
                      'SameGender'=>$ROW['SameGender']));
    }

    return json_encode($jsonObj);

}

function acceptRoom($originJSON){
  if(!isset($originJSON['RoomNo'])){
    return sendWrongRequestMsg();
  }

  require_once('./DBConfig/DBConfig.php');
  $STMT = $_CONN->prepare("UPDATE NN_ROOM SET IsView=0 WHERE No=?");
  @$STMT->bind_param("i",$originJSON['RoomNo']);
  $STMT->execute();

  $jsonObj = array();
  $jsonObj += ['code' => 'success'];
  return json_encode($jsonObj);
}

 ?>
