<?php

require '../vendor/autoload.php';
require '../app/init.php';
$smmapi   = new SMMApi();

$orders = $conn->prepare("SELECT *,services.service_id as service_id,service_api.id as api_id FROM orders INNER JOIN clients ON clients.client_id=orders.client_id INNER JOIN service_api ON service_api.id=orders.order_api LEFT JOIN services ON services.service_id=orders.service_id LEFT JOIN categories ON categories.category_id=services.category_id WHERE orders.dripfeed=:dripfeed && orders.dripfeed_status=:status ");
$orders->execute(array("dripfeed"=>2,"status"=>"active"));
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);

  foreach( $orders as $order ):
    $orderid  = $order["order_id"];
    //print_r($order);
     if( $order["service_type"] == 1 || $order["category_type"] == 1 ):
        ## servis ya da kategori pasif
     elseif( $order["service_secret"] == 1 && !getRow(["table"=>"clients_service","where"=>["client_id"=>$order["client_id"],"service_id"=>$order["service_id"]] ])  ):
         ## servis gizli
     elseif( $order["category_secret"] == 1 && !getRow(["table"=>"clients_category","where"=>["client_id"=>$order["client_id"],"category_id"=>$order["category_id"]] ])  ):
         ## kategori gizli
     elseif( $order["dripfeed_runs"] == $order["dripfeed_delivery"] ):
        ## gönderilen miktarı ile gönderim miktarı eşit tamamlandı olsun
        $update   = $conn->prepare("UPDATE orders SET dripfeed_status=:dripfeed_status WHERE order_id=:id ");
        $update  -> execute(array("id"=>$orderid,"dripfeed_status"=>"completed" ));
      else:
        ## -- ##
          $create_date  = strtotime($order["order_create"]);
          $last_check   = strtotime($order["last_check"]);
          $now          = date("Y-m-d H:i:s"); $now=strtotime($now);


          $order = $conn->prepare("SELECT *,services.service_id as service_id,service_api.id as api_id  FROM orders INNER JOIN clients ON clients.client_id=orders.client_id INNER JOIN service_api ON service_api.id=orders.order_api LEFT JOIN services ON services.service_id=orders.service_id LEFT JOIN categories ON categories.category_id=services.category_id WHERE orders.order_id=:order_id ");
          $order->execute(array("order_id"=>$orderid));
          $order = $order->fetch(PDO::FETCH_ASSOC);
          $link       = $order["order_url"];
          $quantity   = $order["order_quantity"];
          $now        = date("Y-m-d H:i:s"); $now=strtotime($now);

            if( round(($now - $last_check)/60)  < $order["dripfeed_interval"]  ):
              ## sipariş verilme tarihi, media paylaşım tarihinden önce
            elseif( $order["dripfeed_delivery"] >= $order["dripfeed_runs"] ):
              ## geçikme süresi dolmadı
            else:
              ## __ ##
                  ## sipariş ver başla ##
                    $conn->beginTransaction();
                    if( $order["api_type"] == 1 ):
                      ## Standart api başla ##
                        $getOrder    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'add','service'=>$order["api_service"],'link'=>$link,'quantity'=>$quantity),$order["api_url"]);
                        if( @!$getOrder->order ):
                          $error    = json_encode($getOrder);
                          $order_id = "";
                        else:
                          $error    = "-";
                          $order_id = @$getOrder->order;
                        endif;
                        $balance    = $smmapi->action(array('key' =>$order["api_key"],'action' =>'balance'),$order["api_url"]);
                        $orderstatus= $smmapi->action(array('key' =>$order["api_key"],'action' =>'status','order'=>$order_id),$order["api_url"]);

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
                        $getOrder    = $smmapi->standartAPI(array('api_token' =>$order["api_key"],'action' =>'add','package'=>$order["api_service"],'link'=>$link,'quantity'=>$quantity),$order["api_url"]);
                        if( @!$getOrder->order ):
                          $error    = json_encode($getOrder);
                          $order_id = "";
                        else:
                          $error    = "-";
                          $order_id = @$getOrder->order;
                        endif;
                      $orderstatus= $smmapi->action(array('api_token' =>$order["api_key"],'status' =>'balance','order'=>$order_id),$order["api_url"]);
                      $balance    = $smmapi->action(array('api_token' =>$order["api_key"],'action' =>'balance'),$order["api_url"]);
                      $api_charge = $orderstatus->charge;
                      $currency   = $balance->currency;
                      if( $currency == "TRY" ):
                        $currencycharge = 1;
                      elseif( $currency == "USD" ):
                        $currencycharge = $settings["dolar_charge"];
                      elseif( $currency == "EUR" ):
                        $currencycharge = $settings["euro_charge"];
                      endif;
                    else:
                    endif;
                    $extras = "";
                    $insert = $conn->prepare("INSERT INTO orders SET order_error=:error, order_detail=:detail, client_id=:c_id,
                      api_orderid=:order_id, service_id=:s_id, order_quantity=:quantity, order_charge=:price, order_url=:url,
                      order_create=:create, order_extras=:extra, last_check=:last_check, order_api=:api, api_serviceid=:api_serviceid,
                      dripfeed_id=:dripfeed_id, api_charge=:api_charge, api_currencycharge=:api_currencycharge, order_profit=:profit
                      ");
                    $insert = $insert-> execute(array("c_id"=>$order["client_id"],"detail"=>json_encode($getOrder),"error"=>$error,"s_id"=>$order["service_id"],
                      "quantity"=>$quantity,"price"=>$order["dripfeed_totalcharges"]/$order["dripfeed_runs"],"url"=>$link,
                      "create"=>date("Y.m.d H:i:s"),"extra"=>$extras,"order_id"=>$order_id,"last_check"=>date("Y.m.d H:i:s"),"api"=>$order["api_id"],
                      "api_serviceid"=>$order["api_service"],
                      "dripfeed_id"=>$order["order_id"],"profit"=>$api_charge*$currencycharge,"api_charge"=>$api_charge,"api_currencycharge"=>$currencycharge
                    ));
                      if( $insert ): $last_id = $conn->lastInsertId(); endif;
                    $update2= $conn->prepare("UPDATE orders SET dripfeed_delivery=:delivery WHERE order_id=:id ");
                    $update2= $update2->execute(array("delivery"=>$order["dripfeed_delivery"] + 1,"id"=>$orderid));
                    $update3  = $conn->prepare("UPDATE orders SET last_check=:check WHERE order_id=:id ");
                    $update3  = $update3-> execute(array("id"=>$orderid,"check"=>date("Y-m-d H:i:s") ));
                    if( $insert && $update2 ):
                      $conn->commit();

                    else:
                      $conn->rollBack();
                      echo "update: ".$update." insert: ".$insert." update2: ".$update2."\n";
                    endif;

                  ## sipariş ver bitti ##

              ## __ ##
            endif;

        ## -- ##
     endif;
  endforeach;
