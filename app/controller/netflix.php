<?php

$title .= "Netflix";

if( $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}