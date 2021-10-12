<?php include 'header.php'; ?>


<div class="container-fluid">
  <div class="row">    
   <div class=" col-md-12">
   <ul class="nav nav-tabs">
      <li class="p-b"><button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalDiv" data-action="yeni_kupon">Create Coupon</button></li>
     
   </ul>
   
   <table class="table report-table" style="border:1px solid #ddd">
      <thead>
         <tr>
           
           
            <th width="33%">Coupon Code</th>
            <th width="15%">Peice</th>
            <th width="33%">Amount</th>
            <th></th>
         </tr>
      </thead>
      <form id="changebulkForm" action="<?php echo site_url("admin/kuponlar/delete") ?>" method="post" onsubmit="return confirm('Do you want to delete it?');">
        <tbody>
          <?php foreach($kuponlar as $kupon ): ?>
              <tr>
                
                 <td><?php echo $kupon["kuponadi"] ?></td>
                 <td><?php echo $kupon["adet"] ?></td>
                 <td><?php echo $kupon["tutar"] ?></td>
                 <td><input type="hidden" name="kupon_id" value="<?php echo $kupon["id"] ?>"><button type="submit" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret">Delete</button></td>
                 
              </tr>
            <?php endforeach; ?>
        </tbody>
        <input type="hidden" name="bulkStatus" id="bulkStatus" value="0">
      </form>
   </table>
   <hr>
   <h4>Coupons Used</h4>
 
   
   <table class="table report-table" style="border:1px solid #ddd">
      <thead>
         <tr>
           
           
            <th width="33%">Member Number</th>
            <th width="15%">Coupon Code</th>
            <th width="33%">Amount</th>
            <th></th>
         </tr>
      </thead>
      <form id="changebulkForm" action="<?php echo site_url("admin/kuponlar/delete") ?>" method="post" onsubmit="return confirm('Silmek istiyor musunuz ?');">
        <tbody>
          <?php foreach($kupon_kullananlar as $kupons ): ?>
              <tr>
                
                 <td><?php echo $kupons["uye_id"] ?></td>
                 <td><?php echo $kupons["kuponadi"] ?></td>
                 <td><?php echo $kupons["tutar"] ?></td>
            
                 
              </tr>
            <?php endforeach; ?>
        </tbody>
        <input type="hidden" name="bulkStatus" id="bulkStatus" value="0">
      </form>
   </table>
</div>
</div>
</div>

<?php include 'footer.php'; ?>