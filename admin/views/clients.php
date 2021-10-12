<?php include 'header.php'; ?>

<div class="container-fluid">

 <ul class="nav nav-tabs">
   <li class="p-b"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="new_user">New user</button></li>
   <li class="p-b"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="export_user">Backup users</button></li>
   <li class="p-b"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="alert_user">Notify users</button></li>
     <li class="p-b"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="all_numbers">Username & mailing list</button></li>
   
   
   
   <li class="pull-right p-b">
     <form class="form-inline" action="" method="get" enctype="multipart/form-data">
       <div class="input-group">
         <input type="text" name="search" class="form-control" value="<?=$search_word?>" placeholder="Search">
         <span class="input-group-btn search-select-wrap">
             <select class="form-control search-select" name="search_type">
               <option value="username" <?php if( $search_where == "username" ): echo 'selected'; endif; ?> >Username</option>
               <option value="name" <?php if( $search_where == "name" ): echo 'selected'; endif; ?> >Name</option>
               <option value="email" <?php if( $search_where == "email" ): echo 'selected'; endif; ?> >Email</option>
               <option value="telephone" <?php if( $search_where == "telephone" ): echo 'selected'; endif; ?> >Phone No.</option>
             </select>
             <button type="submit" class="btn btn-default"><span class="fa fa-search" aria-hidden="true"></span></button>
           </span>
       </div>
     </form>
    </li>
 </ul>
  <ul></ul>
    <table class="table" style="border:1px solid #ddd">
      <thead>
      <tr>
        <th class="column-id">#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Username</th>
        <th>Status</th>
        <th>Balance</th>
        <th>Spending</th>
        <th nowrap="">Date of registration</th>
        <th>Pricing</th>
        <th>Actions</th>
      </tr>
      </thead>
        <tbody>

          <?php 
		  
		  $statucek = $conn->prepare("SELECT * FROM settings");
          $statucek->execute();
          $statucek = $statucek->fetch(PDO::FETCH_ASSOC);
		  
		  $bronz_statu = $statucek["bronz_statu"];
		  $silver_statu = $statucek["silver_statu"];
		  $gold_statu = $statucek["gold_statu"];
		  $bayi_statu = $statucek["bayi_statu"];
		  
		  foreach($clients as $client ): 
		  
		  
		  $statubul = $conn->prepare("SELECT SUM(payment_amount) as toplam FROM payments WHERE client_id=:client_id ");
          $statubul->execute(array("client_id"=>$client["client_id"]));
          $statubul = $statubul->fetch(PDO::FETCH_ASSOC);
		  
		  
		  
		  
		  if($statubul["toplam"]<=$bronz_statu):
			$statusu = "Bronze Status";
		  endif;
		  
		  if($statubul["toplam"]>$bronz_statu and $statubul["toplam"]<=$silver_statu):
			$statusu = "Silver Status";
		  endif;
		  
		  if($statubul["toplam"]>$silver_statu and $statubul["toplam"]<=$gold_statu):
			$statusu = "Gold Status";
		  endif;
		  
		  if($statubul["toplam"]>$gold_statu and $statubul["toplam"]<=$bayi_statu):
			$statusu = "Dealer Status";
		  endif;
		  
		  if($statubul["toplam"]>$bayi_statu):
			$statusu = "Dealer Status";
		  endif;
		  
		  
			
		  
		  
		  ?>
            <tr class="<?php if( $client["client_type"] == 1 ): echo "grey"; endif; ?>">
               <td><?php echo $client["client_id"] ?></td>
               <td><?php echo $client["name"] ?></td>
               <td><?php echo $client["email"] ?></td>
               <td><?php echo $client["username"] ?></td>
               <td><?php echo $statusu; ?></td>
               <td><?php echo $client["balance"] ?></td>
               <td><?php echo $client["spent"] ?></td>
               <td><?php echo $client["register_date"] ?></td>
               <td><button type="button" class="btn btn-default btn-xs <?php if( !countRow(["table"=>"clients_price","where"=>["client_id"=>$client["client_id"]] ]) ): echo "disabled"; endif; ?> " style="cursor:pointer"  data-toggle="modal" data-target="#modalDiv" data-id="<?php echo $client["client_id"] ?>" data-action="price_user">Special Pricing (<?php echo countRow(["table"=>"clients_price","where"=>["client_id"=>$client["client_id"]] ]) ?>) </button></td>
               <td class="td-caret">
                 <div class="dropdown pull-right">
                   <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Options <span class="caret"></span></button>
                   <ul class="dropdown-menu">
                     <li><a style="cursor:pointer;"  data-toggle="modal" data-target="#modalDiv" data-action="edit_user" data-id="<?=$client["client_id"]?>">Edit User</a></li>
                     <li><a style="cursor:pointer;"  data-toggle="modal" data-target="#modalDiv" data-action="pass_user" data-id="<?=$client["client_id"]?>">Edit Password</a></li>
                     <li><a style="cursor:pointer;"  data-toggle="modal" data-target="#modalDiv" data-action="secret_user" data-id="<?=$client["client_id"]?>">Edit Categories</a></li>
                     <li><a href="<?php echo site_url("admin/clients/change_apikey/".$client["client_id"]) ?>">Change API Key</a></li>
                     <?php if( $client["client_type"] == 1 ): $type = "active"; else: $type = "deactive"; endif; ?>
                     <li><a href="<?php echo site_url("admin/clients/".$type."/".$client["client_id"]) ?>">Disable Account <?php if( $client["client_type"] == 1 ): echo "aktifleştir"; else: echo "pasifleştir"; endif; ?></a></li>
                     <li><a href="<?php echo site_url("admin/clients/del_price/".$client["client_id"]) ?>">Delete Discounts</a></li>
                   </ul>
                 </div>
               </td>
             </tr>
          <?php endforeach; ?>

        </tbody>
    </table>

    <?php if( $paginationArr["count"] > 1 ): ?>
      <nav>
        <ul class="pagination">
          <?php if( $paginationArr["current"] != 1 ): ?>
            <li class="page-item"><a class="page-link" href="<?php echo site_url("admin/clients/1".$search_link) ?>">&laquo;</a></li>
            <li class="page-item"><a class="page-link" href="<?php echo site_url("admin/clients/".$paginationArr["previous"].$search_link) ?>">&lsaquo;</a></li>
          <?php
              endif;
              for ($page=1; $page<=$pageCount; $page++):
                if( $page >= ($paginationArr['current']-9) and $page <= ($paginationArr['current']+9) ):
          ?>
            <li class="page-item <?php if( $page == $paginationArr["current"] ): echo "active"; endif; ?> ">
              <a class="page-link" href="<?php echo site_url("admin/clients/".$page.$search_link) ?>"><?php echo $page ?></a>
            </li>
          <?php endif; endfor;
                if( $paginationArr["current"] != $paginationArr["count"] ):
          ?>
            <li class="page-item"><a class="page-link" href="<?php echo site_url("admin/clients/".$paginationArr["next"].$search_link) ?>">&rsaquo;</a></li>
            <li class="page-item"><a class="page-link" href="<?php echo site_url("admin/clients/".$paginationArr["count"].$search_link) ?>">&raquo;</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>


</div>

<?php include 'footer.php'; ?>
