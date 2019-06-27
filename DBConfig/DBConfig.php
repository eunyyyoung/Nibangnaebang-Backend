<?php

define("DB_HOST", "localhost");
define("USER_NAME", "alld");
define("PASS", "alp03100716");
define("DB_NAME", "alld");

$_CONN = mysqli_connect(DB_HOST, USER_NAME, PASS, DB_NAME); //DataBase Connection

//Set database's charset to utf-8
$STMT = $_CONN->prepare("set session character_set_connection=utf8;");
$STMT->execute();
$STMT = $_CONN->prepare("set session character_set_results=utf8;");
$STMT->execute();
$STMT = $_CONN->prepare("set session character_set_client=utf8;");
$STMT->execute();

//Find exception and processing
if(!$_CONN){
  $jsonObj = array();
  $jsonObj['HEAD'] = array();
  $jsonObj['HEAD'] += ['ResCode' => 500, 'ResMsg' => "DB_CONNECTION_ERROR"];

  echo json_encode($jsonObj); exit;
}

 ?>
