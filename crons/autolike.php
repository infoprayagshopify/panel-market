<?php

require '../vendor/autoload.php';
require '../app/init.php';
$smmapi   = new SMMApi();

$orders = $conn->prepare("SELECT *,services.service_id as service_id,service_api.id as api_id FROM orders
  INNER JOIN clients ON clients.client_id=orders.client_id
  LEFT JOIN services ON services.service_id=orders.service_id
  INNER JOIN service_api ON service_api.id=services.service_api
  LEFT JOIN categories ON categories.category_id=services.category_id
  WHERE orders.subscriptions_type=:subscriptions_type && ( orders.subscriptions_status=:status || orders.subscriptions_status=:status2 ) ");
$orders->execute(array("subscriptions_type"=>2,"status"=>"active","status2"=>"limit"));
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);

  foreach( $orders as $order ):
    $orderid  = $order["order_id"];
    $update   = $conn->prepare("UPDATE orders SET last_check=:check WHERE order_id=:id ");
    $update  -> execute(array("id"=>$orderid,"check"=>date("Y-m-d H:i:s") ));
    //print_r($order);
     if( $order["service_type"] == 1 || $order["category_type"] == 1 ):
        ## servis ya da kategori pasif
     elseif( $order["service_secret"] == 1 && !getRow(["table"=>"clients_service","where"=>["client_id"=>$order["client_id"],"service_id"=>$order["service_id"]] ])  ):
         ## servis gizli
     elseif( $order["category_secret"] == 1 && !getRow(["table"=>"clients_category","where"=>["client_id"=>$order["client_id"],"category_id"=>$order["category_id"]] ])  ):
         ## kategori gizli
     elseif( $order["subscriptions_delivery"] >= $order["subscriptions_posts"] ):
        ## gönderilen miktarı ile gönderim miktarı eşit tamamlandı olsun
        $update   = $conn->prepare("UPDATE orders SET subscriptions_status=:subscriptions_status WHERE order_id=:id ");
        $update  -> execute(array("id"=>$orderid,"subscriptions_status"=>"completed" ));
     elseif( date("Y-m-d") >= $order["subscriptions_expiry"] && $order["subscriptions_expiry"] != "1970-01-01" ):
        ## bitiş tarihi gelmiş, süresi dolmuş olsun
      echo "1";
        $update   = $conn->prepare("UPDATE orders SET subscriptions_status=:subscriptions_status WHERE order_id=:id ");
        $update  -> execute(array("id"=>$orderid,"subscriptions_status"=>"expired" ));
      else:
        ## -- ##
          $create_date  = strtotime($order["order_create"]);
          $last_check   = strtotime($order["last_check"]);
          $ordername     = $order["subscriptions_username"];
$profile      = site_url("app/hidden/bridge.php?username=".$ordername);
$html         = file_get_contents($profile);
          $arr          = explode('window._sharedData = ',$html);
          $arr          = explode(';</script>',$arr[1]);
          $obj          = json_decode($arr[0] , true);

          $order       =  $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"];
          $is_private=  $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["is_private"];
          $photoCount=  $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["edge_owner_to_timeline_media"]["count"];
          $photos    =  $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["edge_owner_to_timeline_media"]["edges"];

          if( $is_private ):
            ## profil gizli
          else:
            for( $i=0; $i<=11; $i++ ):
              $order = $conn->prepare("SELECT *,services.service_id as service_id,service_api.id as api_id  FROM orders INNER JOIN clients ON clients.client_id=orders.client_id LEFT JOIN services ON services.service_id=orders.service_id INNER JOIN service_api ON service_api.id=orders.order_api LEFT JOIN categories ON categories.category_id=services.category_id WHERE orders.order_id=:order_id ");
              $order->execute(array("order_id"=>$orderid));
              $order = $order->fetch(PDO::FETCH_ASSOC);
              $share_date = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["edge_owner_to_timeline_media"]["edges"][$i]["node"]["taken_at_timestamp"];
              $is_video   = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["edge_owner_to_timeline_media"]["edges"][$i]["node"]["is_video"];
              $media_id   = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["edge_owner_to_timeline_media"]["edges"][$i]["node"]["shortcode"];
              $link       = "https://www.instagram.com/p/".$media_id;
              $delay      = $create_date - $share_date;
              $quantity   = rand($order["subscriptions_min"],$order["subscriptions_max"]);
              $price      = (client_price($order["service_id"],$order["client_id"])/1000)*$quantity;
              $now        = date("Y-m-d H:i:s"); $now=strtotime($now);
                if( $link == "https://www.instagram.com/p/" ):
                      ## sipariş verilemez
                elseif( getRow(["table"=>"orders","where"=>["subscriptions_id"=>$order["order_id"],"order_url"=>$link ] ]) ):
                  ## bu abonelik ile bu mediaya gönderim sağlanmış
                elseif( $create_date > $share_date  ):
                  ## sipariş verilme tarihi, media paylaşım tarihinden önce
                elseif( $now - $share_date < $order["subscriptions_delay"] ):
                  ## geçikme süresi dolmadı
                else:
                  ## __ ##
                    if( $order["service_package"] == 11 ):
                      $send_order       = TRUE;
                    elseif( $order["service_package"] == 12 && $is_video ):
                      $send_order       = TRUE;
                    elseif( $order["service_package"] == 14 ):
                      $send_order       = TRUE;
                      $price            = $price/$order["subscriptions_posts"];
                      $order["balance"] = $order["balance"] + $price;
                      $order["spent"]   = $order["spent"] - $price;
                    elseif( $order["service_package"] == 15 && $is_video ):
                      $send_order       = TRUE;
                      $price            = $price/$order["subscriptions_posts"];
                      $order["balance"] = $order["balance"] + $price;
                      $order["spent"]   = $order["spent"] - $price;
                    else:
                      $send_order = FALSE;
                    endif;
                    if( $send_order == FALSE ):
                      ## sipariş verilemez
                    elseif( $order["subscriptions_delivery"] >= $order["subscriptions_posts"] ):
                      ## gönderilen miktarı ile gönderim miktarı eşit tamamlandı olsun
                      $update   = $conn->prepare("UPDATE orders SET subscriptions_status=:subscriptions_status WHERE order_id=:id ");
                      $update  -> execute(array("id"=>$orderid,"subscriptions_status"=>"completed" ));
                    elseif( ( $price > $order["balance"] ) && $order["balance_type"] == 2 ):
                      ## sipariş verilemez
                    elseif( ( $order["balance"] - $price < "-".$order["debit_limit"] ) && $order["balance_type"] == 1 ):
                      ## sipariş verilemez
                    elseif( $price == 0 ):
                      ## sipariş verilemez
                    else:
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
                          subscriptions_id=:subscriptions_id, api_charge=:api_charge, api_currencycharge=:api_currencycharge, order_profit=:profit
                          ");
                        $insert = $insert-> execute(array("c_id"=>$order["client_id"],"detail"=>json_encode($getOrder),"error"=>$error,"s_id"=>$order["service_id"],
                          "quantity"=>$quantity,"price"=>$price,"url"=>$link,
                          "create"=>date("Y.m.d H:i:s"),"extra"=>$extras,"order_id"=>$order_id,"last_check"=>date("Y.m.d H:i:s"),"api"=>$order["api_id"],
                          "api_serviceid"=>$order["api_service"],
                          "subscriptions_id"=>$order["order_id"],"profit"=>$api_charge*$currencycharge,"api_charge"=>$api_charge,"api_currencycharge"=>$currencycharge
                        ));
                          if( $insert ): $last_id = $conn->lastInsertId(); endif;
                        $update = $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
                        $update = $update-> execute(array("balance"=>$order["balance"]-$price,"spent"=>$order["spent"]+$price,"id"=>$order["client_id"]));
                        $update2= $conn->prepare("UPDATE orders SET subscriptions_delivery=:delivery WHERE order_id=:id ");
                        $update2= $update2->execute(array("delivery"=>$order["subscriptions_delivery"] + 1,"id"=>$orderid));

                        if( $update && $insert && $update2 ):
                          $conn->commit();
                        else:
                          $conn->rollBack();
                          echo "update: ".$update." insert: ".$insert." update2: ".$update2."\n";
                        endif;

                      ## sipariş ver bitti ##
                    endif;

                  ## __ ##
                endif;
            endfor;
          endif;

        ## -- ##
     endif;
  endforeach;
