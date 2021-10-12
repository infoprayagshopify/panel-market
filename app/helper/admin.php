<?php

function admin_controller($controllerName){
  $controllerName = strtolower($controllerName);
  return PATH.'/admin/controller/'.$controllerName.'.php';
}

function admin_view($viewName){
  $viewName = strtolower($viewName);
  return PATH.'/admin/views/'.$viewName.'.php';
}

function servicePackageType($type){
  switch ($type) {
    case '1':
      return "Servis";
      break;
    case '2':
      return "Paket";
      break;
    case '3':
      return "Özel yorum";
      break;
    case '4':
      return "Paket yorum";
      break;

    default:
      return "Abonelik";
      break;
  }
}