<?php

function sendWrongRequestMsg(){
  $jsonObj = array();
  $jsonObj['HEAD'] = array();
  $jsonObj['HEAD'] += ['ResCode' => 400, 'ResMsg' => 'WRONG_REQUEST'];

  return json_encode($jsonObj);
}

 ?>
