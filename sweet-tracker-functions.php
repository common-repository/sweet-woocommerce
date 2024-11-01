<?php
namespace SweetAnalytics;

function sweetTracker()
{
    
    $setup = new SweetAnalyticsSetup();

    $trackerEnabled = get_option('SWEET_TRACKER');
    $basketTracking = get_option('SWEET_BASKET_TRACKER');
    $trackerID = $setup->getTrackerID();

    if($trackerEnabled && (!isset($trackerID) || strlen($trackerID) === 0)) {
        return Logger::add('Tracker', 'Tracker is enabled, but no Tracker ID has been configured.', 0);
    }

    if ($trackerEnabled) {
        $script = '<script>';
        $script .= 'window.sweet = window.sweet || {}, window.sweet.q = sweet.q || [], window.sweet.init = function(e) {
                  this.cid = e;
              }, window.sweet.track = function() {
                  this.q.push(arguments)
              };
              var a = document.createElement("script"),
                  m = document.getElementsByTagName("script")[0];
              a.async = 1, a.src = "https://track.sweetanalytics.com/sweet.min.js", m.parentNode.insertBefore(a, m);

              sweet.init("' . $trackerID . '");
              sweet.track("pageview", 1);';

        if (is_singular('product') and $basketTracking) {
            global $post;
            $_product = wc_get_product($post->ID);
            $sku = $post->ID;
            $skuMeta = $_product->get_sku();

            if (!empty($skuMeta)) {
                $sku = $skuMeta;
            }

            $name = get_the_title($post->ID);
            $price = $_product->get_price();
            $term_list = wp_get_post_terms($post->ID, 'product_cat');
            $script .= '
                        document.body.addEventListener("added_to_cart", function(e) {
                          sweet.track("addToBasket", {
                            item_id: "' . $sku . '",
                            item_name: "' . $name . '",
                            category: "' . $term_list[0]->name . '",
                            price: "' . $price . '",
                            qty: 1
                          });
                        })';
        } elseif (is_page('cart') and $basketTracking) {
            $script .= '
            document.body.addEventListener("removed_from_cart", function(e) {
                        var product_id = $("this").data("product_id");
                        var sku = $("this").data("product_sku");
                        if( sku ) {
                          product_id = sku;
                        }
                        sweet.track("removeFromBasket", {
                          id: product_id
                        });
                      })';
        }
        $script .= '</script>';
        echo $script;
    }
}

function sweetTrackerEnhancedEcommerce($order_id)
{
    global $wp;

    $trackerEnabled = get_option('SWEET_TRACKER');
    // Finish if tracker is not enabled
    if (!$trackerEnabled) {
        return;
    }
    
    $setup = new SweetAnalyticsSetup();

    // ONLY RUN ON THANK YOU PAGE
    if (!is_wc_endpoint_url('order-received')) {
        return;
    }

    // GET ORDER ID FROM URL
    $order_id = absint($wp->query_vars['order-received']);
    $order = wc_get_order($order_id);

    // Check if transaction already processed
    $key = 'sweetTracker-' . $order->get_order_number();
    $trackerProcessed = get_post_meta($order_id, $key, true);
    if (empty($trackerProcessed)) {
        update_post_meta($order_id, $key, 1);
    } else {
        Logger::add('Tracker', 'Transaction ' . $order_id .' has already been processed.', 0);
        return;
    }

    if ($order->has_status('failed')) {
        // DISABLE ANY FUNCTION HOOKED TO "woocommerce_thankyou"
        remove_all_actions('woocommerce_thankyou');
    } else {

        Logger::add('Tracker', 'Adding transaction: ' . $order_id, 0);
        Logger::add('Tracker', 'Total price: ' . $order->get_total(), 0);

    ?>
      <script>
        sweet.track("addTransaction", {
                transaction_id: "<?php echo $order_id ?>",
                affiliation: "",
                total: <?php echo $order->get_total(); ?>,
                tax: <?php echo $order->get_total_tax(); ?>,
                shipping: <?php echo $order->get_shipping_total(); ?>,
                address1: "<?php echo $order->get_billing_address_1(); ?>",
                address2: "<?php echo $order->get_billing_address_2(); ?>",
                zip: "<?php echo $order->get_billing_postcode(); ?>",
                city: "<?php echo $order->get_billing_city(); ?>",
                state: "<?php echo $order->get_billing_state(); ?>",
                country: "<?php echo $order->get_shipping_country(); ?>"
            });
            </script>
            <?php

        $lineItems = $order->get_items();

        foreach ($lineItems as $item) {
            $product = $item->get_product();
            $categories = $product->get_category_ids();
            $primaryCat = sizeof($categories) > 0 ? $categories[0] : "";
            $term = get_term_by('id', $primaryCat, 'product_cat');
            $categoryName = is_null($term) || $term === false ? "" : $term->name;

            Logger::add('Tracker', 'Adding item: ' . $product->get_name() . $product->get_sku(), 0);
            Logger::add('Tracker', 'Price: ' . $item->get_total(), 0);
            Logger::add('Tracker', 'Category: ' . $categoryName, 0);

            ?>

            <script>

            sweet.track("addItem", {
              transaction_id: "<?php echo $order_id; ?>",
              id: "<?php echo $product->get_sku(); ?>",
              name: "<?php echo $product->get_name(); ?>",
              category: "<?php echo $categoryName; ?>",
              price: <?php echo $item->get_total(); ?>,
              qty: <?php echo $item->get_quantity(); ?>,
              url: "<?php echo get_permalink($product->get_id()); ?>",
              image: "<?php echo wp_get_attachment_url($product->get_image_id()); ?>"
            });

            </script>

        <?php 
            }

            Logger::add('Tracker', 'Sending transaction! ' . $order_id, 0);
        ?>
              <script>
            sweet.track("transaction");
            </script>
      <?php
}
}

add_action('wp_head', 'SweetAnalytics\sweetTracker');
add_action('woocommerce_thankyou', 'SweetAnalytics\sweetTrackerEnhancedEcommerce');

?>
