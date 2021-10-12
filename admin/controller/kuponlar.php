<?php

  if( $user["access"]["kuponlar"] != 1  ):
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

  if( !route(2) ):
    $page   = 1;
  elseif( is_numeric(route(2)) ):
    $page   = route(2);
  elseif( !is_numeric(route(2)) ):
    $action = route(2);
  endif;

  if( empty($action) ):
      
    $kuponlar        = $conn->prepare("SELECT * FROM kuponlar ");
    $kuponlar        -> execute(array());
    $kuponlar        = $kuponlar->fetchAll(PDO::FETCH_ASSOC);
    $kupon_kullananlar        = $conn->prepare("SELECT * FROM kupon_kullananlar ");
    $kupon_kullananlar        -> execute(array());
    $kupon_kullananlar        = $kupon_kullananlar->fetchAll(PDO::FETCH_ASSOC);
    require admin_view('kuponlar');
	
	
	
	
	elseif( $action == "delete" ):
	
	if( $_POST ):
		 
		 foreach ($_POST as $key => $value) {
			$$key = $value;
		  }
		  
		  
		  
		  
		   $delete = $conn->prepare("DELETE FROM kuponlar WHERE id=:kupon_id");
          $delete->execute(array("kupon_id"=>$kupon_id));
            if( $delete ):
			
              header("Location:".site_url("admin/kuponlar"));
            else:
			
              header("Location:".site_url("admin/kuponlar"));
            endif;
			
			
	  
	endif;
	
	
  elseif( $action == "new" ):
    if( $_POST ):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
	  
	  
	  
	    $stmt = $conn->prepare("SELECT count(*) FROM kuponlar WHERE kuponadi = ?");
		$stmt->execute([$kuponadi]);
		$count = $stmt->fetchColumn();


      if($count>0):
        $error    = 1;
        $errorText= "Bu kupon adı mevcut";
        $icon     = "error";
      else:
          $conn->beginTransaction();
          $insert = $conn->prepare("INSERT INTO kuponlar SET kuponadi=:kuponadi, adet=:adet, tutar=:tutar");
          $insert = $insert->execute(array("kuponadi"=>$kuponadi,"adet"=>$adet,"tutar"=>$tutar));
          
          if( $insert ):
            $conn->commit();
            $referrer = site_url("admin/kuponlar");
            $error    = 1;
            $errorText= "Success";
            $icon     = "success";
          else:
            $conn->rollBack();
            $error    = 1;
            $errorText= "Failed";
            $icon     = "error";
          endif;
      endif;
      echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
    
    endif;
  endif;
