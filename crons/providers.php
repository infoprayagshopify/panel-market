<?php

require '../vendor/autoload.php';
require '../app/init.php';
$smmapi   = new SMMApi();

$services = $conn->prepare("SELECT * FROM services INNER JOIN service_api ON service_api.id=services.service_api WHERE services.service_api!=:apitype ");
$services->execute(array("apitype"=>0));
$services = $services->fetchAll(PDO::FETCH_ASSOC);
$there_change=0;

  foreach( $services as $service ):
    $there[$service["service_id"]] = 0;
    $apiServices  = $smmapi->action(array('key'=>$service["api_key"],'action'=>'services'),$service["api_url"]);
    $balance      = $smmapi->action(array('key' =>$service["api_key"],'action' =>'balance'),$service["api_url"]);
    $apiServices  = json_decode(json_encode($apiServices),true);
      foreach ($apiServices as $apiService):
        if( $service["api_service"] == $apiService["service"] ):
          $there[$service["service_id"]] = 1;
          $detail["min"]=$apiService["min"];$detail["max"]=$apiService["max"];$detail["rate"]=$apiService["rate"];$detail["currency"]=$balance->currency;$detail=json_encode($detail);
          $extras = json_decode($service["api_detail"],true);
            if( $apiService["rate"] != $extras["rate"] ):
              $extra  = ["old"=>$extras["rate"],"new"=>$apiService["rate"] ];
              $insert = $conn->prepare("INSERT INTO serviceapi_alert SET service_id=:service, serviceapi_alert=:alert, servicealert_date=:date, servicealert_extra=:extra ");
              $insert->execute(array("service"=>$service["service_id"],"alert"=>"#".$service["service_id"]." numaralı servis fiyatı değiştirilmiş.","date"=>date("Y-m-d H:i:s"),"extra"=>json_encode($extra) ));
              if( $insert ): $there_change = $there_change+1; endif;
            endif;
            if( $apiService["min"] != $extras["min"] ):
              $extra  = ["old"=>$extras["min"],"new"=>$apiService["min"] ];
              $insert = $conn->prepare("INSERT INTO serviceapi_alert SET service_id=:service, serviceapi_alert=:alert, servicealert_date=:date, servicealert_extra=:extra ");
              $insert->execute(array("service"=>$service["service_id"],"alert"=>"#".$service["service_id"]." numaralı servis minimum miktarı değiştirilmiş.","date"=>date("Y-m-d H:i:s"),"extra"=>json_encode($extra) ));
              if( $insert ): $there_change = $there_change+1; endif;
            endif;
            if( $apiService["max"] != $extras["max"] ):
              $extra  = ["old"=>$extras["max"],"new"=>$apiService["max"] ];
              $insert = $conn->prepare("INSERT INTO serviceapi_alert SET service_id=:service, serviceapi_alert=:alert, servicealert_date=:date, servicealert_extra=:extra ");
              $insert->execute(array("service"=>$service["service_id"],"alert"=>"#".$service["service_id"]." numaralı servis maksimum miktarı değiştirilmiş.","date"=>date("Y-m-d H:i:s"),"extra"=>json_encode($extra) ));
              if( $insert ): $there_change = $there_change+1; endif;
            endif;
              if( $service["api_servicetype"] == 1 && $there[$service["service_id"]] ):
                $extra  = ["old"=>"Sağlayıcıda Pasif","new"=>"Sağlayıcıda Aktif" ];
                $update = $conn->prepare("UPDATE services SET api_detail=:detail, api_servicetype=:type WHERE service_id=:service ");
                $update->execute(array("service"=>$service["service_id"],"detail"=>$detail,"type"=>2 ));
                $insert = $conn->prepare("INSERT INTO serviceapi_alert SET service_id=:service, serviceapi_alert=:alert, servicealert_date=:date, servicealert_extra=:extra ");
                $insert->execute(array("service"=>$service["service_id"],"alert"=>"#".$service["service_id"]." numaralı servis sağlayıcı tarafından yeniden aktif edilmiş.","date"=>date("Y-m-d H:i:s"),"extra"=>json_encode($extra) ));
                if( $insert ): $there_change = $there_change+1; endif;
              else:
                $update = $conn->prepare("UPDATE services SET api_detail=:detail, api_servicetype=:type WHERE service_id=:service ");
                $update->execute(array("service"=>$service["service_id"],"detail"=>$detail,"type"=>2 ));
              endif;
            $detail = [];
        endif;
      endforeach;
  endforeach;

  foreach ($there as $service => $type):
    $serviceDetail = $conn->prepare("SELECT * FROM services WHERE service_id=:id ");
    $serviceDetail->execute(array("id"=>$service));
    $serviceDetail = $serviceDetail->fetch(PDO::FETCH_ASSOC);
    if( $type == 0 && $serviceDetail["api_servicetype"] == 2 ):
      $extra  = ["old"=>"Sağlayıcıda Aktif","new"=>"Sağlayıcıda Pasif" ];
      $update = $conn->prepare("UPDATE services SET  api_servicetype=:type WHERE service_id=:service ");
      $update->execute(array("service"=>$service,"type"=>1 ));
      $insert = $conn->prepare("INSERT INTO serviceapi_alert SET service_id=:service, serviceapi_alert=:alert, servicealert_date=:date, servicealert_extra=:extra ");
      $insert->execute(array("service"=>$service,"alert"=>"#".$service." numaralı servis sağlayıcı tarafından kaldırılmış.","date"=>date("Y-m-d H:i:s"),"extra"=>json_encode($extra) ));
      if( $update ): $there_change = $there_change+1; endif;
    endif;
  endforeach;

  if( $settings["alert_serviceapialert"] == 2 && $there_change ):
    if( $settings["alert_type"] == 3 ):   $sendmail = 1; $sendsms  = 1; elseif( $settings["alert_type"] == 2 ): $sendmail = 1; $sendsms=0; elseif( $settings["alert_type"] == 1 ): $sendmail=0; $sendsms  = 1; endif;
    if( $sendsms ):
      SMSUser($settings["admin_telephone"],"Servis sağlayıcı tarafından bilgisi değişen servisleriniz mevcut.");
    endif;
    if( $sendmail ):
      sendMail(["subject"=>"Yeni sipariş mevcut.","body"=>"Servis sağlayıcı tarafından bilgisi değişen servisleriniz mevcut.","mail"=>$settings["admin_mail"]]);
    endif;
  endif;
