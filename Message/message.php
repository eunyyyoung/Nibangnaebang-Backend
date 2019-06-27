<?php

function sendWrongRequestMsg(){
  $jsonObj = array();
  $jsonObj += ['ResCode' => 400, 'ResMsg' => 'WRONG_REQUEST'];

  return json_encode($jsonObj);
}

 ?>
