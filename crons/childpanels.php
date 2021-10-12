<?php

require '../vendor/autoload.php';
require '../app/init.php';

$now = new DateTime(NOW);
$renewal_date = $now->format('Y-m-d');

$childorders = $conn->prepare("SELECT * FROM childpanels INNER JOIN clients ON clients.client_id=childpanels.client_id WHERE childpanels.renewal_date <='$renewal_date' AND childpanels.status !='disabled' ORDER BY childpanels.id DESC");
$childorders->execute();
$childorders = $childorders->fetchAll(PDO::FETCH_ASSOC);

  foreach( $childorders as $order ):

      if($order['balance']<$order['charge']){
          $conn->beginTransaction();
          $update = $conn->prepare("UPDATE childpanels SET status=:status WHERE id=:id");
          $update = $update->execute(array("id"=>$order['id'],"status"=>"terminated"));
          $conn->commit();
      }else{
          $date = new DateTime(NOW);
          $date->modify('+1 month');
          $renewal_date = $date->format('Y-m-d');
          
          $price = $order['charge'];
          
          $conn->beginTransaction();
          $insert = $conn->prepare("UPDATE childpanels SET renewal_date=:renewal_date, status=:status WHERE id=:id");
          $insert = $insert->execute(array("renewal_date"=>$renewal_date,"status"=>"active","id"=>$order['id']));

          $update = $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
          $update = $update->execute(array("balance"=>$order["balance"]-$price,"spent"=>$order["spent"]+$price,"id"=>$order["client_id"]));
          $insert2= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
          $insert2= $insert2->execute(array("c_id"=>$order["client_id"],"action"=>"Child Panel Renewed with id : ".$order['id'].".","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
            if ( $insert && $update && $insert2 ):
              $conn->commit();
            else:
              $conn->rollBack();
            endif;
          
      }
  endforeach;
  
  echo "Successfull";
  
  $dosya = fopen('../count/son.txt', 'w');
fwrite($dosya, date("Y-m-d H:i:s"));
fclose($dosya);