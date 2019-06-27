<?php

function uploadFile(){
  /*$uploads_dir = './Images';
  $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

  $error = $_FILES['file']['error'];
  $name = $_FILES['file']['name'];
  $ext = @array_pop(explode('.', $name));

  $name = date("YmdHis").'.'.$ext;

  if( $error != UPLOAD_ERR_OK ) {
  	switch( $error ) {
  		case UPLOAD_ERR_INI_SIZE:
  		case UPLOAD_ERR_FORM_SIZE:
  			return "ERROR";
  			break;
  		case UPLOAD_ERR_NO_FILE:
  			return "ERROR";
  			break;
  		default:
  			return "ERROR";
  	}
  	exit;
  }

  // 확장자 확인
  if( !in_array($ext, $allowed_ext) ) {
  	return "ERROR";
  	exit;
  }

  // 파일 이동
  move_uploaded_file( $_FILES['file']['tmp_name'], "$uploads_dir/$name");

  return $name; */

  $uploads_dir = './Images/';
  $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
  $fileNames = array();

  foreach($_FILES['file']['name'] as $f => $name){
    $name = $_FILES['file']['name'][$f];

    $uploadName = explode('.', $name);

    $uploadname = time().$f.'.'.$uploadName[1];
    $uploadFile = $uploads_dir.$uploadname;


    if( $error != UPLOAD_ERR_OK ) {
    	switch( $error ) {
    		case UPLOAD_ERR_INI_SIZE:
    		case UPLOAD_ERR_FORM_SIZE:
    			return "ERROR";
    			break;
    		case UPLOAD_ERR_NO_FILE:
    			return "ERROR";
    			break;
    		default:
    			return "ERROR";
    	}
    	exit;
    }

    move_uploaded_file( $_FILES['file']['tmp_name'], $uploadFile);

    array_push($fileNames, $uploadname);
  }

  return $fileNames;
}

 ?>
