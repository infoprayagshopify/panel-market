<?php include 'header.php'; ?>

<div class="content-body">
    <div class="pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-10 dbg-none">
                        <li class="breadcrumb-item"><a href="#"><?= constant("HOME") ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Child Panels</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row row-xs">

            <div class="col">
                <div class="card dwd-100">
                    <div class="card-body pd-20 table-responsive dof-inherit">

                        <div class="container-fluid pd-t-20 pd-b-20">
                            <table class="table" id="dt">
                                <thead>
                                    <tr>
                                        <th class="p-l">#</th>
                                        <th>User</th>
                                        <th>Domain</th>
                                        <th>Username</th>
                                        <th>Password</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <form id="changebulkForm" method="post">
                                    <tbody>
                                        <?php foreach($payments as $payment ): ?>
                                        <tr>
                                            <td class="p-l"><?php echo $payment["id"] ?></td>
                                            <td><?php echo $payment["username"] ?></td>
                                            <td><?php echo $payment["domain"] ?></td>
                                            <td><?php echo $payment["child_username"] ?></td>
                                            <td><?php echo $payment["child_password"] ?></td>
                                            <td><?php echo $payment["date_created"]; ?></td>
                                            <td><?php echo $payment["status"]; ?></td>
                                            <td><?php if($payment["status"] != "disabled"){ ?>
                                                <form method="post"><input name="panel_id" type="hidden" value="<?php echo $payment["id"] ?>"/><input type="submit" name="disable" class="btn btn-primary" value="Disable"></form>
                                                <?php }else{ ?>
                                                <form method="post"><input name="panel_id" type="hidden" value="<?php echo $payment["id"] ?>"/><input type="submit" name="enable" class="btn btn-primary" value="Enable"></form>
                                                <?php }?></td>
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