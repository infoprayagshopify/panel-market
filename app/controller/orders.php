<?php

$title .= $languageArray["orders.title"];

if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}

$smmapi   = new SMMApi();
$fapi     = new socialsmedia_api();

$status_list  = ["all","pending","inprogress","completed","partial","processing","canceled"];
$search_statu = route(1); if( !route(1) ):  $route[1] = "all";  endif;

  if( !in_array($search_statu,$status_list) ):
    $route[1]         = "all";
  endif;

  if( route(2) ):
    $page         = route(2);
  else:
    $page         = 1;
  endif;
    if( route(1) != "all" ): $search  = "&& order_status='".route(1)."'"; else: $search = ""; endif;
    if( !empty(urldecode($_GET["search"])) ): $search.= " && ( order_url LIKE '%".urldecode($_GET["search"])."%' || order_id LIKE '%".urldecode($_GET["search"])."%' ) "; endif;
    if( !empty($_GET["subscription"]) ): $search.= " && ( subscriptions_id LIKE '%".$_GET["subscription"]."%'  ) "; endif;
    if( !empty($_GET["dripfeed"]) ): $search.= " && ( dripfeed_id LIKE '%".$_GET["dripfeed"]."%'  ) "; endif;
    $c_id       = $user["client_id"];
    $to         = 25;
    $count      = $conn->query("SELECT * FROM orders WHERE client_id='$c_id' && dripfeed='1' && subscriptions_type='1' $search ")->rowCount();
    $pageCount  = ceil($count/$to);
      if( $page > $pageCount ): $page = 1; endif;
    $where      = ($page*$to)-$to;
    $paginationArr = ["count"=>$pageCount,"current"=>$page,"next"=>$page+1,"previous"=>$page-1];

    $orders = $conn->prepare("SELECT * FROM orders INNER JOIN services WHERE services.service_id = orders.service_id && orders.dripfeed=:dripfeed && orders.subscriptions_type=:subs && orders.client_id=:c_id $search ORDER BY orders.order_id DESC LIMIT $where,$to ");
    $orders-> execute(array("c_id"=>$user["client_id"],"dripfeed"=>1,"subs"=>1 ));
    $orders = $orders->fetchAll(PDO::FETCH_ASSOC);

  $ordersList = [];

    foreach ($orders as $order) {
      $o["id"]    = $order["order_id"];
      $o["date"]  = date("Y-m-d H:i:s", (strtotime($order["order_create"])+$user["timezone"]));
      $o["link"]    = $order["order_url"];
      $o["charge"]  = $order["order_charge"];
      $o["start_count"]  = $order["order_start"];
      $o["quantity"]  = $order["order_quantity"];
      $o["service"]  = $order["service_name"];
      $o["status"]  = $languageArray["orders.status.".$order["order_status"]];
      $o["is_refill"]  = $order["is_refill"];
      if( $order["order_status"] == "completed" && substr($order["order_remains"], 0,1) == "-" ):
        $o["remains"]  = "+".substr($order["order_remains"], 1);
      else:
        $o["remains"]  = $order["order_remains"];
      endif;
      array_push($ordersList,$o);
    }
    
if( $_POST ):
    
  $refill_orderid = htmlspecialchars($_POST["order_id"]);
  $orders = $conn->prepare("SELECT * FROM orders INNER JOIN service_api ON service_api.id=orders.order_api WHERE orders.order_id=:id ");
  $orders-> execute(array("id"=>$_POST["order_id"] ));
  $orders = $orders->fetchAll(PDO::FETCH_ASSOC);
   
//   print_r($orders);
  
  $result =  $smmapi->action(array('key' =>$orders['api_key'],'action' =>'refill','order' =>$refill_orderid),$orders['api_url']);
  $result = json_decode( json_encode($result), true);
  
  if($result['refill'] != ""){
      $success = "1";
      $successText = "Refill Successfull";
  }else{
      $error = "1";
      $errorText = "Refill not allowed";
  }
  
   
   
   
endif;    
