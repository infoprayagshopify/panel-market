<?php include 'header.php'; ?>

<div class="content-body">
    <div class="pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-10 dbg-none">
                        <li class="breadcrumb-item"><a href="#"><?= constant("HOME") ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Online Payments</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row row-xs">

            <div class="col">
                <div class="card dwd-100">
                    <div class="card-body pd-20 table-responsive dof-inherit">

                        <div class="container-fluid pd-t-20 pd-b-20">
                            <ul class="nav nav-tabs pull-right dborder-0">
                                <li class="pull-right export-li">
                                    <button class="btn btn-primary dp-10" data-toggle="modal" data-target="#modalDiv" data-action="payment_new">New Payment</button>
                                </li>
                            </ul>
                            <table class="table" id="dt">
                                <thead>
                                    <tr>
                                        <th class="p-l">#</th>
                                        <th>User</th>
                                        <th>Balance</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Mode</th>
                                        <th>Note</th>
                                        <th>Creation Date</th>
                                        <th>Last Update</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <form id="changebulkForm" action="<?php echo site_url("admin/payments/online/multi-action") ?>" method="post">
                                    <tbody>
                                        <?php foreach($payments as $payment ): ?>
                                        <tr>
                                            <td class="p-l"><?php echo $payment["payment_id"] ?></td>
                                            <td><?php echo $payment["username"] ?></td>
                                            <td><?php echo $payment["client_balance"] ?></td>
                                            <td><?php echo $payment["payment_amount"] ?></td>
                                            <td><?php echo $payment["method_name"] ?></td>
                                            <td>
                                                <?php if( $payment["payment_status"] = 3 ){ ?>
                                                Completed
                                                <?php } ?>
                                            </td>
                                            <td><?php echo $payment["payment_mode"]; ?></td>
                                            <td><?php echo $payment["payment_note"] ?></td>
                                            <td nowrap=""><?php echo $payment["payment_create_date"] ?></td>
                                            <td nowrap=""><?php echo $payment["payment_update_date"] ?></td>
                                            <td class="service-block__action">
                                                <div class="dropdown pull-right">
                                                    <button type="button" class="btn btn-primary btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Action</button>
                                                    <ul class="dropdown-menu">
                                                        <?php if( $payment["payment_mode"] == "Otomatik" ): ?>
                                                        <li><a href="#" data-toggle="modal" data-target="#modalDiv" data-action="payment_detail" data-id="<?php echo $payment["payment_id"] ?>">Payment Detail</a></li>
                                                        <?php endif; ?>
                                                        <li><a href="#" data-toggle="modal" data-target="#modalDiv" data-action="payment_edit" data-id="<?php echo $payment["payment_id"] ?>">Edit Payment</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <input type="hidden" name="bulkStatus" id="bulkStatus" value="0">
                                </form>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="theme/assets/js/datatable/payments.js"></script>