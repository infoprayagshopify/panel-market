<?php

  if( $user["access"]["orders"] != 1  ):
    header("Location:".site_url("admin"));
    exit();
  endif;

  $smmapi = new SMMApi();
  $fapi     = new socialsmedia_api();


  if( route(2)  ==  "counter" ):
    $count          = $conn->prepare("SELECT * FROM orders WHERE dripfeed=:dripfeed && subscriptions_type=:sub $search_add ");
    $count        ->execute(array("dripfeed"=>1,"sub"=>1));
    $count          = $count->rowCount();
    $services = $conn->prepare("SELECT * FROM services");
    $services->execute(array());
    $services = $services->fetchAll(PDO::FETCH_ASSOC);
    $active   = $_POST["active"];
    echo '<li'; if( !$active ): echo ' class="active"'; endif; echo '>
            <a href="/admin/orders/all">All Orders ('.$count.')</a>
          </li>';
      foreach ($services as $service):
        echo '<li'; if( $service["service_id"] == $active ): echo ' class="active"'; endif; echo '>
                <a '; if( $service["service_type"] == 1 ): echo ' style="color: #c1c1c1;"'; endif; echo ' href="admin/orders/all?service_id='.$service["service_id"].'"><span class="label-id">'.$service["service_id"].'</span> '.$service["service_name"].' ('.countRow(["table"=>"orders","where"=>["service_id"=>$service["service_id"]]]).')</a>
              </li>';
      endforeach;
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

    $statusList = ["all","pending","inprogress","completed","partial","canceled","processing","fail","cronpending"];
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
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE {$search} && orders.dripfeed='1' && orders.subscriptions_type='1' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE {$search} && orders.dripfeed='1' && orders.subscriptions_type='1' ";
      $search_link  = "?search=".$search_word."&search_type=".$search_where;
    elseif( $_GET["search_type"] == "order_id" && $_GET["search"] ):
      $search_where = $_GET["search_type"];
      $search_word  = urldecode($_GET["search"]);
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id LIKE '%".$search_word."%' && orders.dripfeed='1' && orders.subscriptions_type='1' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE orders.order_id LIKE '%".$search_word."%'  && orders.dripfeed='1' && orders.subscriptions_type='1' ";
      $search_link  = "?search=".$search_word."&search_type=".$search_where;
    elseif( $_GET["search_type"] == "order_url" && $_GET["search"] ):
      $search_where = $_GET["search_type"];
      $search_word  = urldecode($_GET["search"]);
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_url LIKE '%".$search_word."%' && orders.dripfeed='1' && orders.subscriptions_type='1' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE orders.order_url LIKE '%".$search_word."%'  && orders.dripfeed='1' && orders.subscriptions_type='1' ";
      $search_link  = "?search=".$search_word."&search_type=".$search_where;
    elseif( $_GET["subscription"] ):
      $subs_id      = $_GET["subscription"];
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id LIKE '%".$search_word."%' && orders.dripfeed='1' && orders.subscriptions_type='1' && orders.subscriptions_id='$subs_id' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE orders.subscriptions_id='$subs_id'  && orders.dripfeed='1' && orders.subscriptions_type='1' ";
      $search_link  = "?subscription=".$_GET["subscription"];
    elseif( $_GET["dripfeed"] ):
      $drip_id      = $_GET["dripfeed"];
      $count        = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id LIKE '%".$search_word."%' && orders.dripfeed='1' && orders.subscriptions_type='1' && orders.dripfeed_id='$drip_id' ");
      $count        -> execute(array());
      $count        = $count->rowCount();
      $search       = "WHERE orders.dripfeed_id='$drip_id'  && orders.dripfeed='1' && orders.subscriptions_type='1' ";
      $search_link  = "?dripfeed=".$_GET["subscription"];
    elseif( $status != "all" ):
      if( $_GET["mode"] && $_GET["mode"] == "manuel" ):
        $search_add   = " && orders.order_api=0";
        $search_link  = "?mode=".$_GET["mode"];
      elseif( $_GET["mode"] && $_GET["mode"]== "auto" ):
        $search_add   = " && orders.order_api!=0";
        $search_link  = "?mode=".$_GET["mode"];
      elseif( $_GET["service_id"] ):
        $search_add   = " && orders.service_id=".$_GET["service_id"];
        $search_link  = "?service_id=".$_GET["service_id"];
      else:
        $search_add   = "";
      endif;
      if( $status == "fail" ):
        $search_add  .= ' && orders.order_error!="-" ';
        $count          = $conn->prepare("SELECT * FROM orders WHERE dripfeed=:dripfeed && subscriptions_type=:sub $search_add ");
        $count        ->execute(array("dripfeed"=>1,"sub"=>1));
        $count          = $count->rowCount();
        $search         = "WHERE orders.dripfeed='1' && orders.subscriptions_type='1' $search_add ";
      elseif( $status == "cronpending" ):
        $search_add  .= ' && orders.order_error="-" ';
        $count          = $conn->prepare("SELECT * FROM orders WHERE order_detail=:detail && dripfeed=:dripfeed && subscriptions_type=:sub $search_add ");
        $count        ->execute(array("dripfeed"=>1,"sub"=>1,"detail"=>"cronpending"));
        $count          = $count->rowCount();
        $search         = "WHERE orders.dripfeed='1' && orders.subscriptions_type='1' && order_detail='cronpending' $search_add ";
      else:
        $search_add  .= ' && orders.order_error="-" ';
        $count          = $conn->prepare("SELECT * FROM orders WHERE order_detail!=:detail && order_status=:status && dripfeed=:dripfeed && subscriptions_type=:sub $search_add ");
        $count        ->execute(array("dripfeed"=>1,"sub"=>1,"status"=>$status,"detail"=>"cronpending"));
        $count          = $count->rowCount();
        $search         = "WHERE orders.order_status='".$status."' && orders.dripfeed='1' && orders.subscriptions_type='1' && order_detail!='cronpending'  $search_add ";
      endif;
    elseif( $status == "all" ):
      if( $_GET["mode"] && $_GET["mode"] == "manuel" ):
        $search_add   = " && orders.order_api=0";
        $search_link  = "?mode=".$_GET["mode"];
      elseif( $_GET["mode"] && $_GET["mode"]== "auto" ):
        $search_add   = " && orders.order_api!=0";
        $search_link  = "?mode=".$_GET["mode"];
      elseif( $_GET["service_id"] ):
        $search_add   = " && orders.service_id=".$_GET["service_id"];
        $search_link  = "?service_id=".$_GET["service_id"];
      else:
        $search_add   = "";
      endif;
      $count          = $conn->prepare("SELECT * FROM orders WHERE dripfeed=:dripfeed && subscriptions_type=:sub $search_add ");
      $count        ->execute(array("dripfeed"=>1,"sub"=>1));
      $count          = $count->rowCount();
      $search         = "WHERE orders.dripfeed='1' && orders.subscriptions_type='1' $search_add ";
    endif;
    $to             = 100;
    $pageCount      = ceil($count/$to); if( $page > $pageCount ): $page = 1; endif;
    $where          = ($page*$to)-$to;
    $paginationArr  = ["count"=>$pageCount,"current"=>$page,"next"=>$page+1,"previous"=>$page-1];
    $orders         = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id=orders.client_id INNER JOIN services ON services.service_id=orders.service_id $search ORDER BY orders.order_id DESC LIMIT $where,$to ");
    $orders         -> execute(array());
    $orders         = $orders->fetchAll(PDO::FETCH_ASSOC);
    $failCount      = $conn->prepare("SELECT * FROM orders WHERE orders.dripfeed='1' && orders.subscriptions_type='1' && order_error!=:error ");
    $failCount     -> execute(array("error"=>"-"));
    $failCount      = $failCount->rowCount();
	
	
	//Cron bekleniyor
	$cronpendingcount      = $conn->prepare("SELECT * FROM orders WHERE orders.dripfeed='2' && orders.subscriptions_type='2' && dripfeed_status=:dripfeed_status");
    $cronpendingcount     -> execute(array("dripfeed_status"=>"active"));
    $cronpendingcount      = $cronpendingcount->rowCount();
	
	
	/// Yükleniyor
	$inprogresscount      = $conn->prepare("SELECT * FROM orders WHERE order_status=:order_status");
    $inprogresscount     -> execute(array("order_status"=>"inprogress"));
    $inprogresscount      = $inprogresscount->rowCount();
	
	//Tamamlandı
	$completedcount      = $conn->prepare("SELECT * FROM orders WHERE order_status=:order_status");
    $completedcount     -> execute(array("order_status"=>"completed"));
    $completedcount      = $completedcount->rowCount();
	
	//Kısmen Tamamlandı
	$partialcount      = $conn->prepare("SELECT * FROM orders WHERE order_status=:order_status");
    $partialcount     -> execute(array("order_status"=>"partial"));
    $partialcount      = $partialcount->rowCount();
	
	//Sırada / Sipariş Alındı
	$pendingcount      = $conn->prepare("SELECT * FROM orders WHERE order_status=:order_status");
    $pendingcount     -> execute(array("order_status"=>"pending"));
    $pendingcount      = $pendingcount->rowCount();
	
	//Gönderim Sırasında
	$processingcount      = $conn->prepare("SELECT * FROM orders WHERE order_status=:order_status");
    $processingcount     -> execute(array("order_status"=>"processing"));
    $processingcount      = $processingcount->rowCount();
	
	
	//İptal Edildi
	$canceledcount      = $conn->prepare("SELECT * FROM orders WHERE order_status=:order_status");
    $canceledcount     -> execute(array("order_status"=>"canceled"));
    $canceledcount      = $canceledcount->rowCount();
	
	
	

    function orderStatu($statu,$error,$cron){
      if( $cron == "cronpending" ):
        $statu  = "Cron bekleniyor";
      elseif( $error == "-" ):
        switch ($statu) {
          case 'pending':
            $statu  = "Pending";
          break;
          case 'inprogress':
            $statu  = "In Progress";
          break;
          case 'completed':
            $statu  = "Completed";
          break;
          case 'partial':
            $statu  = "Partial";
          break;
          case 'canceled':
            $statu  = "Canceled";
          break;
          case 'processing':
            $statu  = "Processing";
          break;
        }
      else:
        $statu  = "Failed";
      endif;
      return $statu;
    }

    if( $_POST ):

        if( route(2) == "set_orderurl" ):
          $id = route(3);
          $url= $_POST["url"];
          $update = $conn->prepare("UPDATE orders SET order_url=:url WHERE order_id=:id ");
          $update->execute(array("id"=>$id,"url"=>$url));
          header("Location:".site_url("admin/orders"));
        elseif( route(2) == "set_startcount" ):
          $id     = route(3);
          $start  = $_POST["start"];
          $update = $conn->prepare("UPDATE orders SET order_start=:start WHERE order_id=:id ");
          $update->execute(array("id"=>$id,"start"=>$start));
          header("Location:".site_url("admin/orders"));
        elseif( route(2) == "set_partial" ):
          $id     = route(3);
          $remains= $_POST["remains"];
          $order  = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id=:id ");
          $order ->execute(array("id"=>$id));
          $order  = $order->fetch(PDO::FETCH_ASSOC);

          if( empty($remains) || !is_numeric($remains) ):
            $error      = 1;
            $errorText  = "Gitmeyen miktar boş olamaz";
            $icon       = "error";
          elseif( $order["order_quantity"] < $remains ):
            $error      = 1;
            $errorText  = "Gitmeyen miktar, sipariş miktarından fazla olamaz";
            $icon       = "error";
          else:
            $price  = $order["order_charge"]/$order["order_quantity"]; ## 1 adet kaç TL
            $return = $price*$remains; ## İade edilecek para
            $balance= $order["balance"]+$return; ## Üye yeni bakiye
            $order["order_quantity"]=$order["order_quantity"]-$remains; ## Yeni sipariş miktarı
            $charge = $order["order_charge"]-$return; ## Sipariş yeni tutar
            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE orders SET order_remains=:remains, order_status=:statu, order_charge=:charge, order_quantity=:quantity WHERE order_id=:id ");
            $update = $update->execute(array("id"=>$id,"remains"=>$remains,"statu"=>"partial","charge"=>$charge,"quantity"=>$order["order_quantity"] ));
            $update2= $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id ");
            $update2= $update2->execute(array("id"=>$order["client_id"],"balance"=>$balance,"spent"=>$order["spent"]-$return ));
              if( $update && $update2 ):
                $conn->commit();
                $error      = 1;
                $errorText  = "Success";
                $icon       = "success";
                $referrer   = site_url("admin/orders");
              else:
                $conn->rollBack();
                $error      = 1;
                $errorText  = "Failed";
                $icon       = "error";
                $referrer   = site_url("admin/orders");
              endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
        elseif( route(2) == "multi-action" ):
          $orders   = $_POST["order"];
          $action   = $_POST["bulkStatus"];
          if( $action ==  "pending" ):
            foreach ($orders as $id => $value):
              $update = $conn->prepare("UPDATE orders SET order_status=:status WHERE order_id=:id ");
              $update->execute(array("status"=>"pending","id"=>$id));
            endforeach;
          elseif( $action ==  "inprogress" ):
            foreach ($orders as $id => $value):
              $update = $conn->prepare("UPDATE orders SET order_status=:status WHERE order_id=:id ");
              $update->execute(array("status"=>"inprogress","id"=>$id));
            endforeach;
          elseif( $action ==  "completed" ):
            foreach ($orders as $id => $value):
              $update = $conn->prepare("UPDATE orders SET order_status=:status WHERE order_id=:id ");
              $update->execute(array("status"=>"completed","id"=>$id));
            endforeach;
          elseif( $action ==  "canceled" ):
            foreach ($orders as $id => $value):
              $order  = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id=:id ");
              $order ->execute(array("id"=>$id));
              $order  = $order->fetch(PDO::FETCH_ASSOC);
              $balance= $order["balance"]+$order["order_charge"];
              $spent  = $order["spent"]-$order["order_charge"];
              $order["order_quantity"]=$order["order_quantity"];
              $conn->beginTransaction();
              $update = $conn->prepare("UPDATE orders SET api_charge=:api_charge, order_profit=:order_profit, order_status=:status, order_error=:error, order_charge=:price, order_quantity=:quantity, order_remains=:remains WHERE order_id=:id ");
              $update = $update->execute(array("api_charge"=>0,"order_profit"=>0,"status"=>"canceled","price"=>0,"quantity"=>0,"remains"=>$order["order_quantity"],"error"=>"-","id"=>$id));
              $update2= $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id ");
              $update2= $update2->execute(array("id"=>$order["client_id"],"balance"=>$balance,"spent"=>$spent ));
                if( $update && $update2 ):
                  $conn->commit();
                else:
                  $conn->rollBack();
                endif;
            endforeach;
          elseif( $action ==  "resend" ):
            foreach ($orders as $id => $value):
              $order  = $conn->prepare("SELECT * FROM orders INNER JOIN services ON services.service_id = orders.service_id INNER JOIN service_api ON services.service_api = service_api.id WHERE orders.order_id=:id ");
              $order ->execute(array("id"=>$id));
              $order  = $order->fetch(PDO::FETCH_ASSOC);

              /* API SİPARİŞİ GEÇ BAŞLA */
              if( $order["api_type"] == 1 ):
                ## Standart api başla ##
                  if( $order["service_package"] == 1 || $order["service_package"] == 2  || $order["service_package"] == 11 || $order["service_package"] == 12 ):
                    ## Standart başla ##
                    $get_order    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'add','service'=>$order["api_service"],'link'=>$order["order_url"],'quantity'=>$order["order_quantity"]),$order["api_url"]);
                    if( @!$get_order->order ):
                      $error    = json_encode($get_order);
                      $order_id = "";
                    else:
                      $error    = "-";
                      $order_id = @$get_order->order;
                    endif;
                    ## Standart bitti ##
                  elseif( $order["service_package"] == 3 ):
                    ## Custom comments başla ##
                    $get_order    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'add','service'=>$order["api_service"],'link'=>$order["order_url"],'comments'=>$comments),$order["api_url"]);
                    if( @!$get_order->order ):
                      $error    = json_encode($get_order);
                      $order_id = "";
                    else:
                      $error    = "-";
                      $order_id = @$get_order->order;
                    endif;
                    ## Custom comments bitti ##
                  else:
                  endif;
                  $orderstatus= $smmapi->action(array('key' =>$order["api_key"],'action' =>'status','order'=>$order_id),$order["api_url"]);
                  $balance    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'balance'),$order["api_url"]);
                  $api_charge = $orderstatus->charge;
                    if( !$api_charge ): $api_charge = 0; endif;
                    $currency   = $balance->currency;
                    if( $currency == "TRY" ):
                      $currencycharge = 1;
                    elseif( $currency == "USD" ):
                      $currencycharge = $settings["dolar_charge"];
                    elseif( $currency == "EUR" ):
                      $currencycharge = $settings["euro_charge"];
                    endif;
                ## Standart api bitti ##
              elseif( $order["api_type"] == 3 ):
                if( $order["service_package"] == 1 || $order["service_package"] == 2 ):
                    ## Standart başla ##
                    $get_order    = $fapi->query(array('cmd'=>'orderadd','token' =>$order["api_key"],'apiurl'=>$order["api_url"],'orders'=>[['service'=>$order["api_service"],'amount'=>$order["order_quantity"],'data'=>$order["order_url"]]] ));
                    if( @!$get_order[0][0]['status'] == "error" ):
                      $error    = json_encode($get_order);
                      $order_id = "";
                      $api_charge = "0";
                      $currencycharge = 1;
                    else:
                      $error    = "-";
                      $order_id = @$get_order[0][0]["id"];
                      $orderstatus= $fapi->query(array('cmd'=>'orderstatus','token' => $order["api_key"],'apiurl'=>$order["api_url"],'orderid'=>[$order_id]));
                      $balance    = $fapi->query(array('cmd'=>'profile','token' =>$order["api_key"],'apiurl'=>$order["api_url"]));
                      $api_charge = $orderstatus[$order_id]["order"]["price"];
                      $currency   = "TRY";
                      if( $currency == "TRY" ):
                        $currencycharge = 1;
                      elseif( $currency == "USD" ):
                        $currencycharge = $settings["dolar_charge"];
                      elseif( $currency == "EUR" ):
                        $currencycharge = $settings["euro_charge"];
                      endif;
                    endif;
                    ## Standart bitti ##
                  endif;
              else:
              endif;
              /* API SİPARİŞ GEÇ BİTTİ */
              $update = $conn->prepare("UPDATE orders SET order_api=:api, api_serviceid=:serviceid, order_error=:error, api_orderid=:orderid, order_detail=:detail, api_charge=:api_charge, api_currencycharge=:api_currencycharge, order_profit=:profit  WHERE order_id=:id ");
              $update->execute(array("error"=>$error,"api"=>$order["id"],"serviceid"=>$order["api_service"],"orderid"=>$order_id,"detail"=>json_encode($get_order),"id"=>$order["order_id"],"profit"=>$api_charge*$currencycharge,"api_charge"=>$api_charge,"api_currencycharge"=>$currencycharge ));
            endforeach;
          endif;
          header("Location:".site_url("admin/orders"));
        endif;
      exit();
    endif;

  require admin_view('orders');

  if( route(2) == "order_cancel" ):
    $id     = route(3);
    $order  = $conn->prepare("SELECT * FROM orders INNER JOIN clients ON clients.client_id = orders.client_id WHERE orders.order_id=:id ");
    $order ->execute(array("id"=>$id));
    $order  = $order->fetch(PDO::FETCH_ASSOC);
    $balance= $order["balance"]+$order["order_charge"];
    $spent  = $order["spent"]-$order["order_charge"];
    $order["order_quantity"]=$order["order_quantity"];
    $conn->beginTransaction();
    $update = $conn->prepare("UPDATE orders SET api_charge=:api_charge, order_profit=:order_profit, order_status=:status, order_error=:error, order_charge=:price, order_quantity=:quantity, order_remains=:remains WHERE order_id=:id ");
    $update = $update->execute(array("api_charge"=>0,"order_profit"=>0,"status"=>"canceled","price"=>0,"error"=>"-","quantity"=>0,"remains"=>$order["order_quantity"],"id"=>$id));
    $update2= $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id ");
    $update2= $update2->execute(array("id"=>$order["client_id"],"balance"=>$balance,"spent"=>$spent ));
      if( $update && $update2 ):
        $conn->commit();
      else:
        $conn->rollBack();
      endif;
    header("Location:".site_url("admin/orders"));
  elseif( route(2) == "order_complete" ):
    $id     = route(3);
    $update = $conn->prepare("UPDATE orders SET order_status=:status WHERE order_id=:id ");
    $update->execute(array("status"=>"completed","id"=>$id));
    header("Location:".site_url("admin/orders"));
  elseif( route(2) == "order_inprogress" ):
    $id     = route(3);
    $update = $conn->prepare("UPDATE orders SET order_status=:status WHERE order_id=:id ");
    $update->execute(array("status"=>"inprogress","id"=>$id));
    header("Location:".site_url("admin/orders"));
  elseif( route(2) == "order_resend" ):
    $id     = route(3);
    $order  = $conn->prepare("SELECT * FROM orders INNER JOIN services ON services.service_id = orders.service_id INNER JOIN service_api ON services.service_api = service_api.id WHERE orders.order_id=:id ");
    $order ->execute(array("id"=>$id));
    $order  = $order->fetch(PDO::FETCH_ASSOC);

    /* API SİPARİŞİ GEÇ BAŞLA */
    if( $order["api_type"] == 1 ):
      ## Standart api başla ##
        if( $order["service_package"] == 1 || $order["service_package"] == 2 || $order["service_package"] == 11 || $order["service_package"] == 12 ):
          ## Standart başla ##
          $get_order    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'add','service'=>$order["api_service"],'link'=>$order["order_url"],'quantity'=>$order["order_quantity"]),$order["api_url"]);
          if( @!$get_order->order ):
            $error    = json_encode($get_order);
            $order_id = "";
          else:
            $error    = "-";
            $order_id = @$get_order->order;
          endif;
          ## Standart bitti ##
        elseif( $order["service_package"] == 3 ):
          ## Custom comments başla ##
          $get_order    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'add','service'=>$order["api_service"],'link'=>$order["order_url"],'comments'=>$comments),$order["api_url"]);
          if( @!$get_order->order ):
            $error    = json_encode($get_order);
            $order_id = "";
          else:
            $error    = "-";
            $order_id = @$get_order->order;
          endif;
          ## Custom comments bitti ##
        else:
        endif;
        $orderstatus= $smmapi->action(array('key' =>$order["api_key"],'action' =>'status','order'=>$order_id),$order["api_url"]);
        $balance    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'balance'),$order["api_url"]);
        $api_charge = $orderstatus->charge;
          if( !$api_charge ): $api_charge = 0; endif;
          $currency   = $balance->currency;
          if( $currency == "TRY" ):
            $currencycharge = 1;
          elseif( $currency == "USD" ):
            $currencycharge = $settings["dolar_charge"];
          elseif( $currency == "EUR" ):
            $currencycharge = $settings["euro_charge"];
          endif;
      ## Standart api bitti ##
    elseif( $order["api_type"] == 3 ):
      if( $order["service_package"] == 1 || $order["service_package"] == 2 ):
          ## Standart başla ##
          $get_order    = $fapi->query(array('cmd'=>'orderadd','token' =>$order["api_key"],'apiurl'=>$order["api_url"],'orders'=>[['service'=>$order["api_service"],'amount'=>$order["order_quantity"],'data'=>$order["order_url"]]] ));
          if( @!$get_order[0][0]['status'] == "error" ):
            $error    = json_encode($get_order);
            $order_id = "";
            $api_charge = "0";
            $currencycharge = 1;
          else:
            $error    = "-";
            $order_id = @$get_order[0][0]["id"];
            $orderstatus= $fapi->query(array('cmd'=>'orderstatus','token' => $order["api_key"],'apiurl'=>$order["api_url"],'orderid'=>[$order_id]));
            $balance    = $fapi->query(array('cmd'=>'profile','token' =>$order["api_key"],'apiurl'=>$order["api_url"]));
            $api_charge = $orderstatus[$order_id]["order"]["price"];
            $currency   = "TRY";
            if( $currency == "TRY" ):
              $currencycharge = 1;
            elseif( $currency == "USD" ):
              $currencycharge = $settings["dolar_charge"];
            elseif( $currency == "EUR" ):
              $currencycharge = $settings["euro_charge"];
            endif;
          endif;
          ## Standart bitti ##
        endif;
    else:
    endif;
    /* API SİPARİŞ GEÇ BİTTİ */
    $update = $conn->prepare("UPDATE orders SET order_api=:api, api_serviceid=:serviceid, order_error=:error, api_orderid=:orderid, order_detail=:detail, api_charge=:api_charge, api_currencycharge=:api_currencycharge, order_profit=:profit WHERE order_id=:id ");
    $update->execute(array("error"=>$error,"api"=>$order["id"],"serviceid"=>$order["api_service"],"orderid"=>$order_id,"detail"=>json_encode($get_order),"id"=>$order["order_id"],"profit"=>$api_charge*$currencycharge,"api_charge"=>$api_charge,"api_currencycharge"=>$currencycharge ));
    header("Location:".site_url("admin/orders"));
  endif;
