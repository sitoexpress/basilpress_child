<div class="row">
  <div class="col col-9">
    <div class="row">
      <div class="col col-6">
        <div class="data-item">
          <label><?php _e("Full Name", 'basilpress'); ?></label>
          <p>
            <?php 
            $f_name = ($user->get_first_name()) ? $user->get_first_name() : $user->get_billing_first_name();
            $l_name = ($user->get_last_name()) ? $user->get_last_name() : $user->get_billing_last_name();
            echo $f_name." ".$l_name; ?>
          </p>
        </div>
      </div>
      <div class="col col-6">
        <div class="data-item">
          <label><?php _e("Email", 'basilpress'); ?></label>
          <p>
            <?php echo $user->get_email(); ?>
          </p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col col-6">
        <div class="data-item">
          <label><?php _e("Billing Address", 'basilpress'); ?></label>
          <p>
            <?php echo $user->get_billing_address(); ?><br />
            <?php echo $user->get_billing_postcode()." ".$user->get_billing_state(); ?><br />
            <?php echo $user->get_billing_state()." - ".$user->get_billing_country(); ?>
          </p>
        </div>
        <div class="data-item">
          <label><?php _e("Billing Phone", 'basilpress'); ?></label>
          <p>
            <?php echo $user->get_billing_phone(); ?>
          </p>
        </div>
      </div>
      <div class="col col-6">
        <div class="data-item">
          <label><?php _e("Shipping Address", 'basilpress'); ?></label>
          <p>
            <?php if($user->get_shipping_address()) {
                echo $user->get_shipping_address()."<br />";
                echo $user->get_shipping_postcode()." - ".$user->get_shipping_city()."<br />";
                echo $user->get_shipping_state()." - ".$user->get_shipping_country();
              } else {
                echo $user->get_billing_address()."<br />";
                echo $user->get_billing_postcode()." - ".$user->get_billing_city()."<br />";
                echo $user->get_billing_state()." - ".$user->get_billing_country();
              } ?>
          </p>
        </div>
        <div class="data-item">
          <label><?php _e("Shipping Phone", 'basilpress'); ?></label>
          <p><?php
            if($user->get_shipping_phone()) {
              echo $user->get_shipping_phone();
            } else {
              echo $user->get_billing_phone();
            } ?>
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="col col-3">
    <div class="row">
      <div class="col col-12">
        <div class="data-item">
          <label><?php _e("Invoice or receipt?", 'basilpress'); ?></label>
          <p>
            <?php
            $meta_inv_rec = get_user_meta(get_current_user_id(), 'billing_fatt', true);
            $inv_rec = get_receipt_invoice_label($meta_inv_rec);
            echo $inv_rec; ?>
          </p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col col-12">
        <div class="data-item">
          <?php
          if($meta_inv_rec == 'Si') {
            $invoice_data[] = get_user_meta(get_current_user_id(), 'billing_company', true);
            $invoice_data[] = get_user_meta(get_current_user_id(), 'billing_vat', true);
            $invoice_data[] = get_user_meta(get_current_user_id(), 'billing_pec', true);
            $invoice_data[] = get_user_meta(get_current_user_id(), 'billing_nin', true);
            $invoice_data = array_unique($invoice_data);
            ?>
            <label><?php _e("Invoice data", 'basilpress'); ?></label>
            <p>
              <?php
                // $cf_vat_value = ($cf_vat_value) ? $cf_vat_value : __("Not set", 'basilpress');
                echo implode('<br/>',$invoice_data);
             ?>
            </p>
          <?php
          } else {
            $cf_vat_value = get_user_meta(get_current_user_id(), 'billing_cf', true);
            ?>
            <label><?php _e("Fiscal Code", 'basilpress'); ?></label>
            <p>
              <?php
                $cf_vat_value = ($cf_vat_value) ? $cf_vat_value : __("Not set", 'basilpress');
                echo $cf_vat_value;
             ?>
            </p>
          <?php } ?>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col col-12">
        <div class="action-item">
          <?php if(is_checkout()) { ?>
            <a id="edit-address" class="button"><?php _e("Edit", 'basilpress'); ?></a>
          <?php } else { ?>
            <a href="<?php echo get_the_permalink().'edit-address/'; ?>" class="button"><?php _e('Edit', 'basilpress'); ?></a>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>
