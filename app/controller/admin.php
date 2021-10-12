<?php
  if( $user["access"]["admin_access"]  && $_SESSION["msmbilisim_adminlogin"] && $user["client_type"] == 2 ):
    if( !route(1) ){
      $route[1] = "index";
    }

    if( !file_exists( admin_controller(route(1)) ) ){
      $route[1] = "index";
    }

    require admin_controller( route(1) );
  else:
      $route[1] = "login";
      require admin_controller( route(1) );
  endif;
