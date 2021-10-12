<?php include 'header.php'; ?>


<div class="container">
  <div class="row">
    <?php if( ( route(2) == "themes" && !route(3) ) || route(2) != "themes"  ):  ?>
          <div class="col-md-2 col-md-offset-1">
            <ul class="nav nav-pills nav-stacked p-b">
              <?php foreach($menuList as $menuName => $menuLink ): ?>
                <li class="settings_menus <?php if( $route["2"] == $menuLink ): echo "active"; endif; ?>"><a href="<?=site_url("admin/settings/".$menuLink)?>"><?=$menuName?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
    <?php  endif;
          if( $access ):
            include admin_view('settings/'.route(2));
          else:
            include admin_view('settings/access');
          endif;
    ?>


  </div>
</div>


<?php include 'footer.php'; ?>
