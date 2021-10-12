<div class="col-md-8">
  <div class="settings-header__table">
    <button type="button"  class="btn btn-default m-b" data-toggle="modal" data-target="#modalDiv" data-action="new_paymentbonus" >Add New Bonus</button>
  </div>
   <table class="table">
      <thead>
         <tr>
           <th>
             Method Name
           </th>
           <th>
             From
           </th>
           <th>
             Bonus
           </th>
            <th></th>
         </tr>
      </thead>
      <tbody class="methods-sortable">
         <?php foreach($bonusList as $bonus): ?>
           <tr>
            <td>
              <?php echo $bonus["method_name"]; ?>
            </td>
            <td>
              <i class="fa fa-try"></i> <?php echo $bonus["bonus_from"]; ?>
            </td>
            <td>
              %<?php echo $bonus["bonus_amount"]; ?>
            </td>
            <td class="p-r">
               <button type="button" class="btn btn-default btn-xs pull-right edit-payment-method" data-toggle="modal" data-target="#modalDiv" data-action="edit_paymentbonus" data-id="<?php echo $bonus["bonus_id"]; ?>">Edit</button>
            </td>
         </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
</div>
