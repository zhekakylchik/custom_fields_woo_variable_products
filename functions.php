/**
 * Creating a text field
 *
 * @param $loop
 * @param $variation_data
 * @param $variation
 */
add_action( 'woocommerce_product_after_variable_attributes', 'art_term_production_fields', 10, 3 );
function art_term_production_fields( $loop, $variation_data, $variation ) {
   woocommerce_wp_text_input( array(
      'id'                => '_term_prod_var[' . $variation->ID . ']', // id field
      'label'             => 'Manufacturing time', // Text above the field
      'description'       => 'Indicate the production time, just numbers in days',// Field Description
      'desc_tip'          => 'true', // Popup hint
      'placeholder'       => 'Manufacturing time, days', // Text inside the field
      'type'              => 'number', // Field type
      'custom_attributes' => array( // Arbitrary attributes
                                    'step' => 'any', // Value step
                                    'min'  => '0', // Min value
      ),
      'value'             => get_post_meta( $variation->ID, '_term_prod_var', true ),
   ) );
}

/**
 * Saving the field value
 *
 * @param $post_id
 */
add_action( 'woocommerce_save_product_variation', 'art_save_variation_settings_fields', 10, 2 );
function art_save_variation_settings_fields( $post_id ) {
   $woocommerce__term_prod_var = $_POST['_term_prod_var'][ $post_id ];
   if ( isset( $woocommerce__term_prod_var ) && ! empty( $woocommerce__term_prod_var ) ) {
      update_post_meta( $post_id, '_term_prod_var', esc_attr( $woocommerce__term_prod_var ) );
   }
}

/**
 * Adding the value of the field to the data array
 *
 * @param $variations
 *
 * @return mixed
 */
add_filter( 'woocommerce_available_variation', 'art_load_variation_settings_fields' );
function art_load_variation_settings_fields( $variations ) {
   $variations_time = get_post_meta( $variations['variation_id'], '_term_prod_var', true );
   if ( isset( $variations_time ) && ! empty( $variations_time ) ) {
      $variations['_term_prod_var'] = '<div class="term-production">';
      $variations['_term_prod_var'] .= '<span>Manufacturing time </span>';
      $variations['_term_prod_var'] .= get_post_meta( $variations['variation_id'], '_term_prod_var', true ) . ' дн.';
      $variations['_term_prod_var'] .= '</div>';
   }
   
   return $variations;
}

/**
 *  Adding a dropdown list item in the admin panel
 */
add_action( 'woocommerce_variable_product_bulk_edit_actions', 'art_actions_variation_settings_fields', 10, 1 );
function art_actions_variation_settings_fields() {
   ?>
   <option data-global="true" value="variation_prod_time">Manufacturing time</option>
   <?php
}

/**
 * Adding ajax to bulk change the field
 */
add_action( 'admin_footer', 'art_script_add_all_variation_select' );
function art_script_add_all_variation_select() {
   ?>
   <script>
        jQuery(function ($) {
            jQuery('.wc-metaboxes-wrapper').on('click', 'a.bulk_edit', function (event) {
                var do_variation_term = jQuery('select.variation_actions').val();
                var data = {},
                    value;
                if ('variation_prod_time' === do_variation_term) {

                    value = window.prompt(woocommerce_admin_meta_boxes_variations.i18n_enter_a_value);

                    if (null !== value) {
                        data.value = value;
                    } else {
                        return;
                    }
                    jQuery.ajax({
                        url: woocommerce_admin_meta_boxes_variations.ajax_url,
                        data: {
                            action: 'woocommerce_bulk_edit_variations',
                            security: woocommerce_admin_meta_boxes_variations.bulk_edit_variations_nonce,
                            product_id: woocommerce_admin_meta_boxes_variations.post_id,
                            product_type: jQuery('#product-type').val(),
                            bulk_action: do_variation_term,
                            data: data
                        },
                        type: 'POST',
                        success: function (data) {
                            jQuery('.variations-pagenav .page-selector').val(1).first().change();
                            //console.log(data.product_id);
                        }
                    });

                    jQuery('#woocommerce-product-data').unblock();
                }

            });
        });
   </script>
   <?php
}

/**
 * Saving a value for bulk change
 *
 * @param $bulk_action
 * @param $data
 * @param $product_id
 * @param $variations
 */
add_action( 'woocommerce_bulk_edit_variations_default', 'action_woocommerce_bulk_edit_variations_default', 10, 4 );
function action_woocommerce_bulk_edit_variations_default( $bulk_action, $data, $product_id, $variations ) {
   if ( 'variation_prod_time' === $bulk_action ) {
      foreach ( $variations as $variation ) {
         update_post_meta( $variation, '_term_prod_var', $data['value'] );
      }
   }
   exit;
}
