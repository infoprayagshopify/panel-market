<?php

  if( $user["access"]["child-panels"] != 1  ):
    header("Location:".site_url("admin"));
    exit();
  endif;


  if( route(2)  ==  "counter" ):
    $count          = $conn->prepare("SELECT * FROM childpanels WHERE 1");
    $count        ->execute();
    $count          = $count->rowCount();
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

    if ($_GET["search_type"] == "username" && $_GET["search"]):
        $search_where = $_GET["search_type"];
        $search_word = $_GET["search"];
        $clients = $conn->prepare("SELECT client_id FROM clients WHERE username LIKE '%" . $search_word . "%' ");
        $clients->execute(array());
        $clients = $clients->fetchAll(PDO::FETCH_ASSOC);
        $id = "(";
        foreach ($clients as $client) {
            $id.= $client["client_id"] . ",";
        }
        if (substr($id, -1) == ","):
            $id = substr($id, 0, -1);
        endif;
        $id.= ")";
        $search = " childpanels.client_id IN " . $id;
        $count = $conn->prepare("SELECT * FROM childpanels INNER JOIN clients ON clients.client_id = childpanels.client_id WHERE {$search} ");
        $count->execute(array());
        $count = $count->rowCount();
        $search = "WHERE {$search}";
        $search_link = "?search=" . $search_word . "&search_type=" . $search_where;
    else:
         
        $count = $conn->prepare("SELECT * FROM childpanels WHERE 1");
        $count->execute();
        $count = $count->rowCount();
        $search = "WHERE 1";
    endif;
    // $to = 50;
    // $pageCount = ceil($count / $to);
    // if ($page > $pageCount):
    //     $page = 1;
    // endif;
    // $where = ($page * $to) - $to;
    $paginationArr = ["count" => $pageCount, "current" => $page, "next" => $page + 1, "previous" => $page - 1];
    $payments = $conn->prepare("SELECT * FROM childpanels INNER JOIN clients ON clients.client_id = childpanels.client_id WHERE 1");
    $payments->execute(array());
    $payments = $payments->fetchAll(PDO::FETCH_ASSOC);


  require admin_view('child-panels');

    if( $_POST ):
        
        if($_POST["disable"]){
        
        $conn->beginTransaction();
        $insert = $conn->prepare("UPDATE childpanels SET status=:status WHERE id=:id");
        $insert = $insert->execute(array("status"=>"disabled","id"=>$_POST["panel_id"]));
        
        if ( $insert ):
              $conn->commit();
              $error      = 1;
                $errorText  = "Success";
                $icon       = "success";
              header("Location:".site_url("admin/child-panels"));
            else:
              $conn->rollBack();
            endif;
        }else{
            $conn->beginTransaction();
            $insert = $conn->prepare("UPDATE childpanels SET status=:status WHERE id=:id");
            $insert = $insert->execute(array("status"=>"active","id"=>$_POST["panel_id"]));
            
            if ( $insert ):
                  $conn->commit();
                  $error      = 1;
                    $errorText  = "Success";
                    $icon       = "success";
                  header("Location:".site_url("admin/child-panels"));
                else:
                  $conn->rollBack();
                endif;
        }
    
    endif;