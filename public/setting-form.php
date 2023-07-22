<section class="content-header">
    <h1>Store Settings</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<section class="content">

    <div class="row">
        <div class="col-md-12">
            <!-- general form elements -->
            <?php
            if ($permissions['settings']['read'] == 1) {

                if ($permissions['settings']['update'] == 0) { ?>
                    <div class="alert alert-danger">You have no permission to update settings</div>
                <?php } ?>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Update System Settings</h3>
                    </div>
                    <!-- /.box-header -->
                    <?php
                    $db->sql("SET NAMES 'utf8'");
                    $sql = "SELECT * FROM settings WHERE  variable='system_timezone'";
                    $db->sql($sql);

                    $res_time = $db->getResult();
                    if (!empty($res_time)) {
                        foreach ($res_time as $row) {
                            $id = $row['id'];
                            $data = json_decode($row['value'], true);
                        }
                    }
                    $sql = "select value from `settings` where variable='Logo' OR variable='logo'";
                    $db->sql($sql);
                    $res_logo = $db->getResult();
                    $sql = "SELECT * FROM settings WHERE variable='currency'";
                    $db->sql($sql);
                    $res_currency = $db->getResult();
                    ?>
                    <!-- form start -->
                    <form id="system_configurations_form" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="system_configurations" name="system_configurations" required="" value="1" aria-required="true">
                        <input type="hidden" id="system_timezone_gmt" name="system_timezone_gmt" value="<?php if (!empty($data['system_timezone_gmt'])) {
                                                                                                            echo $data['system_timezone_gmt'];
                                                                                                        } ?>" aria-required="true">
                        <input type="hidden" id="system_configurations_id" name="system_configurations_id" value="<?php if (!empty($id)) {
                                                                                                                        echo $id;
                                                                                                                    } ?>" aria-required="true">
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label for="app_name">App Name:</label>
                                    <input type="text" class="form-control" name="app_name" value="<?= (isset($data['app_name'])) ? $data['app_name'] : ''; ?>" placeholder="Name of the App - used in whole system" />
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="">Support Number:</label>
                                    <input type="text" class="form-control" name="support_number" value="<?= (isset($data['support_number'])) ? $data['support_number'] : "" ?>" placeholder="Customer support mobile number - used in whole system +91 9876543210" />
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="">Support Email:</label>
                                    <input type="text" class="form-control" name="support_email" value="<?= (isset($data['support_email'])) ? $data['support_email'] : "" ?>" placeholder="Customer support email - used in whole system" />
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="">Current Version Of App:</label>
                                    <input type="text" class="form-control" name="current_version" value="<?= isset($data['current_version']) ? $data['current_version'] : '' ?>" placeholder='Current Version' />
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="">Minimum Version Required: </label>
                                    <input type="text" class="form-control" name="minimum_version_required" value="<?= isset($data['minimum_version_required']) ? $data['minimum_version_required'] : '' ?>" placeholder='Minimum Required Version' />
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="">Version System Status</label><br>
                                    <input type="checkbox" id="version-system-button" class="js-switch" <?php if (!empty($data['is-version-system-on']) && $data['is-version-system-on'] == '1') {
                                                                                                            echo 'checked';
                                                                                                        } ?>>
                                    <input type="hidden" id="is-version-system-on" name="is-version-system-on" value="<?= (!empty($data['is-version-system-on'])) ? $data['is-version-system-on'] : 0; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label for="app_name">Logo:</label>
                                    <img src="<?= DOMAIN_URL . 'dist/img/' . $res_logo[0]['value'] ?>" title='<?= $data['app_name'] ?> - Logo' alt='<?= (isset($data['app_name'])) ? $data['app_name'] : ""; ?> - Logo' style="max-width:100%" />
                                    <input type='file' name='logo' id='logo' accept="image/*" />
                                </div>
                                <h4>Store Address Settings</h4>
                                <hr>
                                <div class="form-group col-md-4">
                                    <label for="">Address: </label>
                                    <textarea class="form-control" name="store_address" rows="5" /><?= (isset($data['store_address'])) ? $data['store_address'] : "" ?></textarea>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="">Latitude: </label>
                                    <input type="text" class="form-control" name="map_latitude" value="<?= isset($data['map_latitude']) ? $data['map_latitude'] : '' ?>" placeholder='Minimum Required Version' />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="">Longitude: </label>
                                    <input type="text" class="form-control" name="map_longitude" value="<?= isset($data['map_longitude']) ? $data['map_longitude'] : '' ?>" placeholder='Minimum Required Version' />
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="currency">Store Currency (Symbol or Code - $ or USD):</label>
                                    <input type="text" class="form-control" name="currency" value="<?= !empty($res_currency) ? $res_currency[0]['value'] : ''; ?>" placeholder="Either Symbol or Code - For Example $ or USD" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="system_timezone" for="system_timezone">System Timezone</label>
                                    <select id="system_timezone" name="system_timezone" required class="form-control col-md-12 select2">
                                        <!-- <option value="">Select a time zone</option> -->
                                        ​
                                        <?php
                                        foreach (timezone_list() as $t) { ?>
                                            <option value="<?= $t["zone"] ?>" data-gmt="<?= $t['diff_from_GMT']; ?>" <?= (isset($data['system_timezone']) && $data['system_timezone'] == $t["zone"]) ? 'selected' : ''; ?>> <?= $t['zone'] . ' - ' . $t['diff_from_GMT'] . ' - ' . $t['time']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Maximum Items Allowed In Cart <small>( Maximum items user can add to cart at once )</small></label>
                                    <input type="number" class="form-control" name="max_cart_items_count" value="<?= $data['max_cart_items_count'] ?>" placeholder='Maximum Items Allowed In Cart' min='1' required />
                                </div>
                            </div>

                            <hr>
                            <h4><b>Delivery Boy Settings</b></h4>
                            <hr>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="">Order Delivery OTP System <br><small>( Enable/Disable )</small></label><br>
                                    <input type="checkbox" id="gen_otp" class="js-switch" <?= (!empty($data['generate-otp']) && $data['generate-otp'] == '1') ? "checked" : ""; ?>>
                                    <input type="hidden" id="generate-otp" name="generate-otp" value="<?= (!empty($data['generate-otp'])) ? $data['generate-otp'] : 0; ?>">
                                </div>
                                <div class="form-group col-md-3" id="charge">
                                    <label for="">Area wise delivery charge <br><small>( Enable/Disable )</small></label><br>
                                    <input type="checkbox" id="area_wise_delivery_charge_btn" class="js-switch" <?= (!empty($data['area-wise-delivery-charge']) && $data['area-wise-delivery-charge'] == '1') ? "checked" : "" ?>>
                                    <input type="hidden" id="area-wise-delivery-charge" name="area-wise-delivery-charge" value="<?= (!empty($data['area-wise-delivery-charge'])) ? $data['area-wise-delivery-charge'] : 0; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <?php $dnone = $data['area-wise-delivery-charge'] == 1 ? 'd-none' : '' ?>
                                <div class="form-group col-md-5 min_amount <?= $dnone; ?>">
                                    <label for="delivery_charge">Minimum Amount for Free Delivery (<?= $settings['currency'] ?>) <small>( Below this user will be charged based on Delivery Charge)</small></label>
                                    <input type="number" class="form-control" name="min_amount" value="<?= $data['min_amount'] ?>" placeholder='Minimum Order Amount for Free Delivery' min='0' required />
                                </div>

                                <div class="form-group col-md-4 min_order_amount <?= $dnone; ?>">
                                    <label for="">Minimum Order Amount <small>( Below this user will not allowed to place order )</small></label>
                                    <input type="number" class="form-control" name="min_order_amount" value="<?= $data['min_order_amount'] ?>" placeholder='Minimum total amount to place order' min='1' required />
                                </div>
                                <div class="form-group col-md-2 delivery_charge <?= $dnone; ?>">
                                    <label for="delivery_charge">Delivery Charge Amount (<?= $settings['currency'] ?>)</label>
                                    <input type="number" class="form-control" name="delivery_charge" value="<?= $data['delivery_charge'] ?>" placeholder='Delivery Charge on Shopping' min='0' />
                                </div>
                            </div>

                            <hr>
                            <h4><b>Refer & Earn System</b></h4>
                            <hr>
                            <div class="row">
                                <div class="form-group col-md-1">
                                    <label for="refer-earn-system">Status</label><br>
                                    <input type="checkbox" id="refer-earn-system-button" class="js-switch" <?= (!empty($data['is-refer-earn-on']) && $data['is-refer-earn-on'] == '1') ? 'checked' : ''; ?>>
                                    <input type="hidden" id="is-refer-earn-on" name="is-refer-earn-on" value="<?= (!empty($data['is-refer-earn-on'])) ? $data['is-refer-earn-on'] : 0; ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="">Minimum Refer & Earn Order Amount (<?= $settings['currency'] ?>)</label>
                                    <input type="number" class="form-control" name="min-refer-earn-order-amount" value="<?= $data['min-refer-earn-order-amount'] ?>" placeholder='Minimum Order Amount' />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="">Refer & Earn Bonus (<?= $settings['currency'] ?> OR %)</label>
                                    <input type="number" class="form-control" name="refer-earn-bonus" value="<?= $data['refer-earn-bonus'] ?>" placeholder='Bonus' />
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="">Refer & Earn Method</label>
                                    <select name="refer-earn-method" class="form-control">
                                        <option value="">Select</option>
                                        <option value="percentage" <?= (isset($data['refer-earn-method']) && $data['refer-earn-method'] == 'percentage') ? "selected" : "" ?>>Percentage</option>
                                        <option value="rupees" <?= (isset($data['refer-earn-method']) && $data['refer-earn-method'] == 'rupees') ? "selected" : "" ?>>Rupees</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="">Maximum Refer & Earn Amount (<?= $settings['currency'] ?>)</label>
                                    <input type="number" class="form-control" name="max-refer-earn-amount" value="<?= $data['max-refer-earn-amount'] ?>" placeholder='Maximum Refer & Earn Amount' />
                                </div>
                            </div>

                            <hr>
                            <h4><b>Other Settings</b></h4>
                            <hr>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Minimum Withdrawal Amount</label>
                                    <input type="number" class="form-control" name="minimum-withdrawal-amount" value="<?= $data['minimum-withdrawal-amount'] ?>" placeholder='Minimum Withdrawal Amount' />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Max days to return item</label>
                                    <input type="number" class="form-control" name="max-product-return-days" value="<?= (isset($data['max-product-return-days'])) ? $data['max-product-return-days'] : ''; ?>" placeholder='Max days to return item' />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Low stock limit <small>(Product will be considered as low stock if stock goes below this limit)</small></label>
                                    <input type="number" class="form-control" name="low-stock-limit" value="<?= (isset($data['low-stock-limit'])) ? $data['low-stock-limit'] : ''; ?>" placeholder='Product low stock limit' />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">User Wallet Refill Limit </label>
                                    <input type="number" class="form-control" name="user-wallet-refill-limit" value="<?= (isset($data['user-wallet-refill-limit'])) ? $data['user-wallet-refill-limit'] : ''; ?>" placeholder='User Wallet Refill Limit' />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Tax Name ( This will be visible on your invoice )</label>
                                    <input type="text" class="form-control" name="tax_name" value="<?= (isset($data['tax_name'])) ? $data['tax_name'] : ''; ?>" placeholder='Tax Name' />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Tax Number</label>
                                    <input type="text" class="form-control" name="tax_number" value="<?= (isset($data['tax_number'])) ? $data['tax_number'] : ''; ?>" placeholder='Tax Number' />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Delivery Boy Bonus Method</label>
                                    <select name="delivery-boy-bonus-method" class="form-control">
                                        <option value="">Select</option>
                                        <option value="percentage" <?= (isset($data['delivery-boy-bonus-method']) && $data['delivery-boy-bonus-method'] == 'percentage') ? "selected" : "" ?>>Percentage</option>
                                        <option value="rupees" <?= (isset($data['delivery-boy-bonus-method']) && $data['delivery-boy-bonus-method'] == 'rupees') ? "selected" : "" ?>>Rupees</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Delivery Boy Bonus </label>
                                    <input type="number" class="form-control" name="delivery-boy-bonus-percentage" value="<?= $data['delivery-boy-bonus-percentage'] ?>" placeholder='Delivery Boy Bonus' />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <h4>Under Maintenance System </h4>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="">Under Maintenance System <br><small>( Enable/Disable )</small></label><br>
                                            <input type="checkbox" id="under_maintenance_system_btn" class="js-switch" <?= (!empty($data['under_maintenance_system']) && $data['under_maintenance_system'] == '1')  ? "checked" : ""; ?>>
                                            <input type="hidden" id="under_maintenance_system" name="under_maintenance_system" value="<?= (!empty($data['under_maintenance_system'])) ? $data['under_maintenance_system'] : 0; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <h4>Ratings Settings</h4>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="">Ratings System <small>( Enable/Disable )</small></label><br>
                                            <input type="checkbox" id="rating" class="js-switch" <?= (!empty($data['ratings']) && $data['ratings'] == '1') ? "checked" : ""; ?>>
                                            <input type="hidden" id="ratings" name="ratings" value="<?= (!empty($data['ratings'])) ? $data['ratings'] : 0; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <h4>Local / Store Pickup Settings</h4>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-md-6 " id="local_pickup">
                                            <label for="">Local / Store Pickup<small>( Enable/Disable )</small></label><br>
                                            <input type="checkbox" id="local_pickup_btn" class="js-switch" <?= (!empty($data['local-pickup']) && $data['local-pickup'] == '1') ? "checked" : "" ?>>
                                            <input type="hidden" id="local-pickup" name="local-pickup" value="<?= (!empty($data['local-pickup'])) ? $data['local-pickup'] : 0; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <h4>Support System</h4>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="">Support System <small>( Enable/Disable )</small></label><br>
                                            <input type="checkbox" id="support_system_btn" class="js-switch" <?= (!empty($data['support_system']) && $data['support_system'] == '1') ? "checked" : ""; ?>>
                                            <input type="hidden" id="support_system" name="support_system" value="<?= (!empty($data['support_system'])) ? $data['support_system'] : 0; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h4><b>Welcome Wallet Balance Settings</b></h4>
                            <hr>

                            <div class="row">
                                <div class="form-group col-md-3" id="welcome_wallet_balance">
                                    <label for="">welcome wallet balance<small>( Enable/Disable )</small></label><br>
                                    <input type="checkbox" id="welcome_wallet_balance_btn" class="js-switch" <?= (!empty($data['welcome-wallet-balance']) && $data['welcome-wallet-balance'] == '1') ? "checked" : "" ?>>
                                    <input type="hidden" id="welcome-wallet-balance" name="welcome-wallet-balance" value="<?= (!empty($data['welcome-wallet-balance'])) ? $data['welcome-wallet-balance'] : 0; ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="">Wallet Balance Amount (<?= $settings['currency'] ?>) </label>
                                    <input type="number" class="form-control" name="wallet_balance" value="<?= (isset($data['wallet_balance'])) ? $data['wallet_balance'] : ''; ?>" placeholder='Amount of Welcome Wallet Balance' />
                                </div>
                            </div>

                            <hr>
                            <h4><b>Mail Settings</b></h4>
                            <hr>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="from_mail">From eMail ID: <small>( This email ID will be used in Mail System )</small></label>
                                    <input type="email" class="form-control" name="from_mail" value="<?= $data['from_mail'] ?>" placeholder='From Email ID' />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="reply_to">Reply To eMail ID: <small>( This email ID will be used in Mail System )</small></label>
                                    <input type="email" class="form-control" name="reply_to" value="<?= $data['reply_to'] ?>" placeholder='From Email ID' />
                                </div>
                            </div>

                            <hr>
                            <h4><b>SMTP Mail Settings</b></h4>
                            <hr>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="from_mail">From eMail ID: <small>( This email ID will be used in SMTP Mail System )</small></label>
                                    <input type="email" class="form-control" name="smtp-from-mail" value="<?= (isset($data['smtp-from-mail'])) ? $data['smtp-from-mail'] : "" ?>" placeholder='From SMTP Email ID' />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="reply_to">Reply To eMail ID: <small>( This email ID will be used in SMTP Mail System )</small></label>
                                    <input type="email" class="form-control" name="smtp-reply-to" value="<?= (isset($data['smtp-reply-to'])) ? $data['smtp-reply-to'] : ""  ?>" placeholder='From SMTP Email ID' />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="reply_to">SMTP eMail Password: </label>
                                    <input type="text" class="form-control" name="smtp-email-password" value="<?= (isset($data['smtp-email-password'])) ? $data['smtp-email-password'] : "" ?>" placeholder='Enter your SMTP email password' />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="reply_to">SMTP Host: </label>
                                    <input type="text" class="form-control" name="smtp-host" value="<?= (isset($data['smtp-host'])) ? $data['smtp-host'] : "" ?>" placeholder='SMTP Host address' />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="reply_to">SMTP Port: <small>( <b>TLS: </b>587 <b>SSL: </b>465 )</small> </label>
                                    <input type="text" class="form-control" name="smtp-port" value="<?= (isset($data['smtp-port'])) ? $data['smtp-port'] : "" ?>" placeholder='SMTP Port' />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">SMTP eMail Content Type: </label>
                                    <select name="smtp-content-type" class="form-control">
                                        <option value="">Select</option>
                                        <option value="html" <?= (isset($data['smtp-content-type']) && $data['smtp-content-type'] == 'html') ? "selected" : "" ?>>HTML</option>
                                        <option value="text" <?= (isset($data['smtp-content-type']) && $data['smtp-content-type'] == 'text') ? "selected" : "" ?>>Text</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">SMTP Encryption: </label>
                                    <select name="smtp-encryption-type" class="form-control">
                                        <option value="">Select</option>
                                        <option value="tls" <?= (isset($data['smtp-encryption-type']) && $data['smtp-encryption-type'] == 'tls') ? "selected" : "" ?>>TLS</option>
                                        <option value="ssl" <?= (isset($data['smtp-encryption-type']) && $data['smtp-encryption-type'] == 'ssl') ? "selected" : "" ?>>SSL</option>
                                    </select>
                                </div>
                            </div>

                            <h4>Flash Sale Url </h4> <a class='btn btn-xs btn-primary' data-toggle='modal' data-target='#howItWorksModal' title='How it works'> Automatic delete flash sale when sales is over</a>
                            <hr>
                            <div class=" row">
                                <div class="form-group col-md-6">
                                    <label for="url">Cron Job URL <small>(Set this URL at your server cron job list for "every hour")</small></label>
                                    <input type="text" class="form-control" name="midtrans_notification_url" value="<?= DOMAIN_URL . "update-flash-sales.php" ?>" placeholder="Cron Job URL" disabled />
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div id="result"></div>
                        <div class="box-footer">
                            <input type="submit" id="btn_update" class="btn-primary btn" value="Update" name="btn_update" />
                        </div>
                    </form>
                <?php } else { ?>
                    <div class="alert alert-danger">You have no permission to view settings</div>
                <?php } ?>
                </div>
                <!-- /.box -->
        </div>
        <div class="modal fade" id='howItWorksModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">How flash sales is delete when sales is over ?</h4>
                        <hr>
                        <ol>
                            <li>
                                Cron job must be set (For every hours) on your server for delete flash sale to be work.
                            </li>
                            <li>
                                Cron job will run every one hour.
                            </li>
                            <li>
                                If delete flash sale doesn't works make sure cron job is set properly and it is working. If you don't know how to set cron job please take help of server support or do search for it.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="separator"> </div>
<?php

function timezone_list()
{
    $zones_array = array();
    $timestamp = time();
    foreach (timezone_identifiers_list() as $key => $zone) {
        date_default_timezone_set($zone);
        $zones_array[$key]['zone'] = $zone;
        $zones_array[$key]['offset'] = (int) ((int) date('O', $timestamp)) / 100;
        $zones_array[$key]['diff_from_GMT'] = 'GMT ' . date('P', $timestamp);
        $zones_array[$key]['time'] = date('h:i:s A');
    }
    return $zones_array;
}


// ___________________old______________

function getTimezoneOptions()
{
    $list = DateTimeZone::listAbbreviations();
    $idents = DateTimeZone::listIdentifiers();

    $data = $offset = $added = array();
    foreach ($list as $abbr => $info) {
        foreach ($info as $zone) {
            if (
                !empty($zone['timezone_id'])
                and
                !in_array($zone['timezone_id'], $added)
                and
                in_array($zone['timezone_id'], $idents)
            ) {
                $z = new DateTimeZone($zone['timezone_id']);
                $c = new DateTime('', $z);
                $zone['time'] = $c->format('H:i a');
                $offset[] = $zone['offset'] = $z->getOffset($c);
                $data[] = $zone;
                $added[] = $zone['timezone_id'];
            }
        }
    }

    array_multisort($offset, SORT_ASC, $data);
    $i = 0;
    $temp = array();
    foreach ($data as $key => $row) {
        $temp[0] = $row['time'];
        $temp[1] = formatOffset($row['offset']);
        $temp[2] = $row['timezone_id'];
        $options[$i++] = $temp;
    }
    return $options;
}
function formatOffset($offset)
{
    $hours = $offset / 3600;
    $remainder = $offset % 3600;
    $sign = $hours > 0 ? '+' : '-';
    $hour = (int) abs($hours);
    $minutes = (int) abs($remainder / 60);

    if ($hour == 0 and $minutes == 0) {
        $sign = ' ';
    }
    return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
}
?>
<script src="plugins/jQuery/jquery.validate.min.js"></script>
<script>
    $(".select2").select2();
    $('#system_timezone').on('change', function(e) {
        gmt = $(this).find(':selected').data('gmt');
        $('#system_timezone_gmt').val(gmt);
    });

    $('#system_configurations_form').validate({
        rules: {
            currency: "required",
        }
    });

    $('#system_configurations_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#system_configurations_form").validate().form()) {
            $.ajax({
                type: 'POST',
                url: 'public/db-operation.php',
                data: formData,
                beforeSend: function() {
                    $('#btn_update').html('Please wait..');
                },
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#result').html(result);
                    $('#result').show().delay(5000).fadeOut();
                    $('#btn_update').html('Save Settings');
                }
            });
        }
    });
</script>
<script>
    var changeCheckbox = document.querySelector('#version-system-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#is-version-system-on').val(1);
        } else {
            $('#is-version-system-on').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#refer-earn-system-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#is-refer-earn-on').val(1);
        } else {
            $('#is-refer-earn-on').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#gen_otp');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#generate-otp').val(1);
        } else {
            $('#generate-otp').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#rating');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#ratings').val(1);
        } else {
            $('#ratings').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#under_maintenance_system_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#under_maintenance_system').val(1);
        } else {
            $('#under_maintenance_system').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#area_wise_delivery_charge_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $(".min_amount").hide();
            $(".min_order_amount").hide();
            $(".delivery_charge").hide();
            $('#area-wise-delivery-charge').val(1);
        } else {
            $(".min_amount").show();
            $(".min_order_amount").show();
            $(".delivery_charge").show();
            $('#area-wise-delivery-charge').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#local_pickup_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#local-pickup').val(1);
        } else {
            $('#local-pickup').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#welcome_wallet_balance_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#welcome-wallet-balance').val(1);
        } else {
            $('#welcome-wallet-balance').val(0);
        }
    };

    var changeCheckbox = document.querySelector('#support_system_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#support_system').val(1);
        } else {
            $('#support_system').val(0);
        }
    };
</script>