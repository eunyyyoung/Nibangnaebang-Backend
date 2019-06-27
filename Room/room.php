<?php

function showRoomList(){
    
    require_once('./DBConfig/DBConfig.php');

    $STMT = $_CONN->prepare('SELECT * FROM NN_ROOM');
    $STMT->execute();
    $RES = $STMT->get_result();

}

function findARoom($originJSON){

    require_once('./DBConfig/DBConfig.php');

    $STMT = $_CONN->prepare('SELECT * FROM NN_ROOM WHERE No=?');
    @$STMT->bind_param("i", trim($originJSON['room']['RoomNo']));
    $STMT->execute();
    $RES = $STMT->get_result();

    return json_encode($RES);
}

function createSellRoom($originJSON){
    $nowDate = date("Y-m-d/H:i:s");

    $STMT = $_CONN->prepare("INSERT INTO NN_ROOM(ALEnd, ALStart, Pay, Address, Title, Detail, IsLoad, LogDate) VALUES(?,?,0,?,?,?,0,?)");
    @$STMT->bind_param("ssisssis", trim($postJSON['room']['ALStart']), 
                                    trim($postJSON['room']['ALEnd']),
                                    $postJSON['room']['pay'],
                                    trim($postJSON['room']['address']),
                                    trim($postJSON['room']['title']),
                                    trim($postJSON['room']['detail']),
                                    $postJSON['room']['isLoad'],
                                    trim($nowDate));
    $STMT->execute();

    $jsonObj = array();

    $result = findARoom();
    $jsonObj += [ 'code' => 'success', 'RoomNo' => $result['No']];
        
    return json_encode($jsonObj);
}


 ?>