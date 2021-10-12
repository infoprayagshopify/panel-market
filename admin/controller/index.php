<?php

$failCount      = $conn->prepare("SELECT * FROM orders WHERE orders.dripfeed='1' && orders.subscriptions_type='1' && order_error!=:error ");
  $failCount     -> execute(array("error"=>"-"));
  $failCount      = $failCount->rowCount();

  if( route(2) == "delete" ):
    $id     = route(3);
    $delete = $conn->prepare("DELETE FROM serviceapi_alert WHERE id=:id ");
    $delete->execute(array("id"=>$id));
    header("Location:".site_url("admin"));
  elseif( route(2) == "multi-action" ):
    $logs     = $_POST["log"];
    $action   = $_POST["bulkStatus"];
    foreach ($logs as $id => $value):
      $delete = $conn->prepare("DELETE FROM serviceapi_alert WHERE id=:id ");
      $delete->execute(array("id"=>$id));
    endforeach;
    header("Location:".site_url("admin"));
  endif;

require admin_view('index');
