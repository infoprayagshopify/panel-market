<?php

  if( $user["access"]["dripfeed"] != 1  ):
    header("Location:".site_url("admin"));
    exit();
  endif;


  if( $_SESSION["client"]["data"] ):
    $data = $_SESSION["client"]["data"];
    foreach ($data as $key => $value) {
      $$key = $value;
    }
    unset($_SESSION["client"]);
  endif;

    if( route(2) && is_numeric(route(2)) ):
      $page = route(2);
    else:
      $page = 1;
    endif;

    $statusList = ["all","active","paused","completed","canceled","expired","limit"];
    if( route(3) && in_array(route(3),$statusList) ):
      $status   = route(3);
    elseif( !route(3) || !in_array(route(3),$statusList) ):
      $status   = "all";
    endif;

    if( $_GET["search_type"] == "username" && $_GET["search"] ):
      $search_where = $_GET["search_type"];
      $search_word  = urldecode($_GET["search"]);
      $clients      = $conn->prepare("SELECT client_id FROM clients WHERE username LIKE '%".$search_word."%' ");
      $clients     -> execute(array());
      $clients      = $clients->fetchAll(PDO::FETCH_ASSOC);
      $id=  "("; foreach ($clients as $client) { $id.=$client["client_id"].","; } if( substr($id,-1) == "," ):  $id = substr($id,0,-1); endif; $id.=")";
      $search       = " orders.client_id IN ".$id;
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE {$search} && orders.dripfeed='2' && orders.subscriptions_type='1' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE {$search} && orders.dripfeed='2' && orders.subscriptions_type='1' ";
      $search_link  = "?search=".$search_word."&search_type=".$search_where;
    elseif( $_GET["search_type"] == "order_id" && $_GET["search"] ):
      $search_where = $_GET["search_type"];
      $search_word  = urldecode($_GET["search"]);
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id LIKE '%".$search_word."%' && orders.dripfeed='2' && orders.subscriptions_type='1' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE orders.order_id LIKE '%".$search_word."%'  && orders.dripfeed='2' && orders.subscriptions_type='1' ";
      $search_link  = "?search=".$search_word."&search_type=".$search_where;
    elseif( $_GET["search_type"] == "order_url" && $_GET["search"] ):
      $search_where = $_GET["search_type"];
      $search_word  = urldecode($_GET["search"]);
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_url LIKE '%".$search_word."%' && orders.dripfeed='2' && orders.subscriptions_type='1' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE orders.order_url LIKE '%".$search_word."%'  && orders.dripfeed='2' && orders.subscriptions_type='1' ";
      $search_link  = "?search=".$search_word."&search_type=".$search_where;
    elseif( $status != "all" ):
      $count          = $conn->prepare("SELECT * FROM orders WHERE dripfeed_status=:status && dripfeed=:dripfeed && subscriptions_type=:sub ");
      $count        ->execute(array("dripfeed"=>1,"sub"=>2,"status"=>$status));
      $count          = $count->rowCount();
      $search         = "WHERE orders.dripfeed_status='".$status."' && orders.dripfeed='2' && orders.subscriptions_type='1' ";
    elseif( $status == "all" ):
      $count          = $conn->prepare("SELECT * FROM orders WHERE dripfeed=:dripfeed && subscriptions_type=:sub ");
      $count        ->execute(array("dripfeed"=>2,"sub"=>1));
      $count          = $count->rowCount();
      $search         = "WHERE orders.dripfeed='2' && orders.subscriptions_type='1' ";
    endif;
    $to             = 50;
    $pageCount      = ceil($count/$to); if( $page > $pageCount ): $page = 1; endif;
    $where          = ($page*$to)-$to;
    $paginationArr  = ["count"=>$pageCount,"current"=>$page,"next"=>$page+1,"previous"=>$page-1];
    $orders         = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id=orders.client_id INNER JOIN services ON services.service_id=orders.service_id $search ORDER BY orders.order_id DESC LIMIT $where,$to ");
    $orders         -> execute(array());
    $orders         = $orders->fetchAll(PDO::FETCH_ASSOC);
    function orderStatu($statu){

      switch ($statu) {
        case 'active':
          $statu  = "Aktif";
        break;
        case 'completed':
          $statu  = "Tamamlandı";
        break;
        case 'canceled':
          $statu  = "İptal";
        break;
      }

      return $statu;
    }

  require admin_view('dripfeeds');

  if( route(2) ==  "dripfeed_canceled" ):
      $update = $conn->prepare("UPDATE orders SET dripfeed_status=:status WHERE order_id=:id ");
      $update->execute(array("status"=>"canceled","id"=>route(3)));
      header("Location:".site_url("admin/dripfeeds"));
  elseif( route(2) ==  "dripfeed_completed" ):
      $update = $conn->prepare("UPDATE orders SET dripfeed_status=:status WHERE order_id=:id ");
      $update->execute(array("status"=>"completed","id"=>route(3)));
      header("Location:".site_url("admin/dripfeeds"));
  elseif( route(2) ==  "dripfeed_canceledbalance" ):
    $id     = route(3);
    $order  = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id=:id ");
    $order ->execute(array("id"=>$id));
    $order  = $order->fetch(PDO::FETCH_ASSOC);
    $price  = ($order["dripfeed_totalcharges"]/$order["dripfeed_runs"])*($order["dripfeed_runs"]-$order["dripfeed_delivery"]); ## İade edilecek tutar
      $conn->beginTransaction();
      $update = $conn->prepare("UPDATE orders SET dripfeed_status=:status, dripfeed_totalcharges=:charges, dripfeed_runs=:runs, dripfeed_totalquantity=:quantity WHERE order_id=:id ");
      $update = $update->execute(array("status"=>"canceled","id"=>route(3),"charges"=>$order["dripfeed_totalcharges"]-$price,"runs"=>$order["dripfeed_delivery"],"quantity"=>$order["dripfeed_delivery"]*$order["order_quantity"] ));
      $update2= $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id ");
      $update2= $update2->execute(array("id"=>$order["client_id"],"spent"=>$order["spent"]-$price,"balance"=>$order["balance"]+$price ));
      if( $update && $update2 ):
        $conn->commit();
      else:
        $conn->rollBack();
      endif;
      header("Location:".site_url("admin/dripfeeds"));
  elseif( route(2) == "multi-action" ):
    $orders   = $_POST["order"];
    $action   = $_POST["bulkStatus"];
    if( $action ==  "canceled" ):
      foreach ($orders as $id => $value):
        $update = $conn->prepare("UPDATE orders SET dripfeed_status=:status WHERE order_id=:id ");
        $update->execute(array("status"=>"canceled","id"=>$id));
      endforeach;
    elseif( $action ==  "completed" ):
      foreach ($orders as $id => $value):
        $update = $conn->prepare("UPDATE orders SET dripfeed_status=:status WHERE order_id=:id ");
        $update->execute(array("status"=>"completed","id"=>$id));
      endforeach;
    elseif( $action ==  "canceledbalance" ):
      foreach ($orders as $id => $value):
        $order  = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id=:id ");
        $order ->execute(array("id"=>$id));
        $order  = $order->fetch(PDO::FETCH_ASSOC);
        $price  = ($order["dripfeed_totalcharges"]/$order["dripfeed_runs"])*($order["dripfeed_runs"]-$order["dripfeed_delivery"]); ## İade edilecek tutar
          $conn->beginTransaction();
          $update = $conn->prepare("UPDATE orders SET dripfeed_status=:status, dripfeed_totalcharges=:charges, dripfeed_runs=:runs, dripfeed_totalquantity=:quantity WHERE order_id=:id ");
          $update = $update->execute(array("status"=>"canceled","id"=>$id,"charges"=>$order["dripfeed_totalcharges"]-$price,"runs"=>$order["dripfeed_delivery"],"quantity"=>$order["dripfeed_delivery"]*$order["order_quantity"] ));
          $update2= $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id ");
          $update2= $update2->execute(array("id"=>$order["client_id"],"spent"=>$order["spent"]-$price,"balance"=>$order["balance"]+$price ));
          if( $update && $update2 ):
            $conn->commit();
          else:
            $conn->rollBack();
          endif;
        endforeach;
      endif;
    header("Location:".site_url("admin/dripfeeds"));
  endif;
