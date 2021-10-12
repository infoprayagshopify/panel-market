<?php

require '../vendor/autoload.php';
require '../app/init.php';

$smmapi   = new SMMApi();
$fapi     = new socialsmedia_api();

$orders = $conn->prepare("SELECT *,services.service_id as service_id,services.service_api as api_id FROM orders
  INNER JOIN clients ON clients.client_id=orders.client_id
  INNER JOIN services ON services.service_id=orders.service_id
  LEFT JOIN categories ON categories.category_id=services.category_id
  INNER JOIN service_api ON service_api.id=services.service_api
  WHERE orders.dripfeed=:dripfeed && orders.subscriptions_type=:subs && orders.order_status=:statu && orders.order_error=:error && orders.order_detail=:detail LIMIT 10 ");
$orders->execute(array("dripfeed"=>1,"subs"=>1,"statu"=>"pending","detail"=>"cronpending","error"=>"-"));
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);


	foreach( $orders as $order )
	{
		$user 		      =	$conn->prepare("SELECT * FROM clients WHERE client_id=:id");
    $user 		      ->	execute(array("id"=>$order["client_id"]));
    $user 		      =	$user->fetch(PDO::FETCH_ASSOC);
		$price  		    = $order["order_charge"];
		$clientBalance	= $user["balance"];
		$clientSpent	  = $user["spent"];
		$balance_type	  = $order["balance_type"];
		$balance_limit	= $order["debit_limit"];
		$link			      = $order["order_url"];

		if( (($price > $clientBalance) && $balance_type == 2) || (($clientBalance - $price < "-".$balance_limit) && $balance_type == 1) ):
			$conn->beginTransaction();
			$update_order = $conn->prepare("UPDATE orders SET order_detail=:detail, order_start=:start, order_finish=:finish, order_remains=:remains, order_status=:status, order_charge=:charge WHERE order_id=:id ");
    	$update_order = $update_order->execute(array("id"=>$order["order_id"],"start"=>0,"finish"=>0,"detail"=>"","remains"=>$order["order_quantity"],"status"=>"canceled","charge"=>0 ));
			$insert2		= 	$conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
            $insert2		= 	$insert2->execute(array("c_id"=>$order["client_id"],"action"=>"Kullanıcının yeterli bakiyesi olmadığından #".$order["order_id"]." idli sipariş iptal edildi.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
            if( $insert2 && $update_order )
            {
            	$conn->commit();
            }else
            {
            	$conn->rollBack();
            }
	    else:
        if( $order["want_username"] == 2 ):
          $private_type = "username";
          $countRow     = $conn->prepare("SELECT * FROM orders WHERE order_url=:url && ( order_status=:statu || order_status=:statu2 || order_status=:statu3 ) && dripfeed=:dripfeed && subscriptions_type=:subscriptions_type ");
          $countRow    -> execute(array("url"=>$link,"statu"=>"pending","statu2"=>"inprogress","statu3"=>"processing","dripfeed"=>1,"subscriptions_type"=>1 ));
          $countRow     = $countRow->rowCount();
        else:
          $private_type = "url";
          if( substr($link,0,7) == "http://" ): $linkSearch = substr($link,7); endif; if( substr($linkSearch,0,8) == "https://" ): $linkSearch = substr($linkSearch,8); endif; if( substr($linkSearch,0,4) == "www." ): $linkSearch = substr($link,4); endif;
          $countRow     = $conn->prepare("SELECT * FROM orders WHERE order_url LIKE :url && ( order_status=:statu || order_status=:statu2 || order_status=:statu3 ) && dripfeed=:dripfeed && subscriptions_type=:subscriptions_type ");
          $countRow    -> execute(array("url"=>'%'.$linkSearch.'%',"statu"=>"pending","statu2"=>"inprogress","statu3"=>"processing","dripfeed"=>1,"subscriptions_type"=>1 ));
          $countRow     = $countRow->rowCount();
        endif;
        if( $order["start_count"] == "none"  ): $start_count = "0"; else: $start_count = instagramCount(["type"=>$private_type,"url"=>$link,"search"=>$order["start_count"]]); endif;

        $conn->beginTransaction();
	    	if( $order["api_type"] == 1 ):
          ## Standart api başla ##
            if( $order["service_package"] == 1 || $order["service_package"] == 2 ):
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

  			$update_order	= 	$conn->prepare("UPDATE orders SET order_start=:start, order_error=:error, api_orderid=:orderid, order_detail=:detail, api_charge=:api_charge, api_currencycharge=:api_currencycharge, order_profit=:profit  WHERE order_id=:id ");
      	$update_order	=	$update_order->execute(array("start"=>$start_count,"error"=>$error,"orderid"=>$order_id,"detail"=>json_encode($get_order),"id"=>$order["order_id"],"profit"=>$api_charge*$currencycharge,"api_charge"=>$api_charge,"api_currencycharge"=>$currencycharge ));
      	$update_client	= 	$conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
        $update_client	= 	$update_client-> execute(array("balance"=>$clientBalance-$price,"spent"=>$clientSpent+$price,"id"=>$order["client_id"]));
        $client 		=	$conn->prepare("SELECT * FROM clients WHERE client_id=:id");
        $client 		->	execute(array("id"=>$order["client_id"]));
        $client 		=	$client->fetch(PDO::FETCH_ASSOC);
        $insert2		= 	$conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
        $insert2		= 	$insert2->execute(array("c_id"=>$order["client_id"],"action"=>"API aracılığıyla ".$price." TL tutarında yeni sipariş geçildi #".$order["order_id"]." Eski Bakiye: ".$clientBalance." / Yeni Bakiye:".$client["balance"],"ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));

      	if( $update_order && $update_client )
        {
        	$conn->commit();
        }else
        {
        	$conn->rollBack();
        }

    	endif;

		echo "<br>";
	}
