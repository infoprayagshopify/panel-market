<?php

$title .= $languageArray["terms.title"];

if( $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}
