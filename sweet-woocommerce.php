<?php
/*
 * Plugin Name: Sweet Analytics WooCommerce
 * Description: WooCommerce tracking integration with Sweet Analytics
 * Version: 0.0.10
 * Author: Sweet Analytics
 * Author URI: https://sweetanalytics.com
 * WC requires at least: 2.2
 * WC tested up to: 6.5
 */
namespace SweetAnalytics;


/**
 * Check if WooCommerce is active
 **/
require_once 'class/setup.php';

add_action('before_woocommerce_init', function() {
	if (class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class )) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true );
	}
});

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('woocommerce_loaded', 'SweetAnalytics\initSweetPlugin');
}

function initSweetPlugin()
{
    // Admin menu settings
    function sweet_woocommerce()
    {
			$image = plugin_dir_url( __FILE__ ) . 'assets/img/logo.svg';
			// $icon = base64_decode($image);
			// var_dump($icon);

			add_submenu_page(
				'plugins.php',
				'Configuration | Sweet Analytics',
				'Sweet Analytics',
				'manage_options',
				'sweetConfig',
				'SweetAnalytics\sweetConfig'
				);

        // add_menu_page('Configuración', 'Sweet Analytics', 'manage_options',
				// 		'sweetConfig', 'SweetAnalytics\sweetConfig', null, 75);
						
				add_action( 'admin_enqueue_scripts', function() {
					wp_enqueue_style( 'main styles', plugin_dir_url( __FILE__ ) . 'assets/styles/main.css', array(), '1.0' );
					wp_enqueue_script( 'main script', plugin_dir_url( __FILE__ ) . 'assets/scripts/main.js', array(), '1.0' );
				});
					
    }
		
    add_action("admin_menu", "SweetAnalytics\sweet_woocommerce", 1);


    function sweetConfig()
    {
        $setup = new SweetAnalyticsSetup();

        if (isset($setup->errorMessage)) {
            set_transient('fx-admin-notice-sweet', true, 5);
        }
        $defs = new SweetAnalyticsDefs();

        if (isset($_POST['trackerSave'])) {
            $sweetTracker = false;
            if (isset($_POST['tracker'])) {
                $sweetTracker = true;
            }
            update_option('SWEET_TRACKER', $sweetTracker);

            $basketTracking = false;
            if (isset($_POST['basketTracking'])) {
                $basketTracking = true;
            }
            update_option('SWEET_BASKET_TRACKER', $basketTracking);

            $sweetTrackerId = '';
            if (isset($_POST['trackerId'])) {
                $sweetTrackerId = sanitize_text_field($_POST['trackerId']);
            }
            update_option('SWEET_TRACKER_ID', $sweetTrackerId);

						?>
							<div class="updated notice is-dismissible">
				        <p><?php echo $defs->getDef('Configuration has been saved!'); ?></p>
					    </div>
						<?php

        }

        $storeData = $setup->getShopInfo();

        if (isset($_POST['contactSuport'])) {
            $to = 'miguel.martinez@sweetanalytics.com';
            $subject = $defs->getDef('Support ticket');
            $message = "<p><strong>{$defs->getDef('Site name')}: </strong>{$storeData['store_name']}</p>
			<p><strong>{$defs->getDef('Domain')}: </strong>{$storeData['domain']}</p>
			<p><strong>{$defs->getDef('Platform')}: </strong>{$storeData['platform_name']}, version {$storeData['platform_version']}</p>
			<p><strong>{$defs->getDef('Extension')}: </strong>{$storeData['extension_name']}, version {$storeData['extension_version']}</p>
			<p><strong>{$defs->getDef('Currency')}: </strong>{$storeData['currency']}</p>";
            $message .= "<p><strong>{$defs->getDef('Message')}:</strong><p> <p>" . sanitize_textarea_field($_POST['message']) . "</p>";


						$file_dir = SWEET_ANALYTICS_PLUGIN_PATH . '../core/log/log.csv';

						$logs = Logger::get_logs();
						$fp = fopen($file_dir, 'w');

						foreach ($logs as $log)
						{
							$content = array($log->post_date, $log->post_title, $log->post_content);
							fputcsv($fp, $content);
						}
						
						fclose($fp);

						$attachments = array();
            if (isset($_POST['includeLog'])) {
                $attachments = $file_dir;
            }
            $headers = array('Content-Type: text/html; charset=UTF-8');
						$isProcessed = wp_mail($to, $subject, $message, $headers, $attachments);
						$emailProcessed = $isProcessed ? "Yes" : "No";

						Logger::add('Support', 'Sending support email');
						Logger::add('Support', 'Processed: ' . $emailProcessed, 0);

						file_put_contents($file_dir, "");
?>

					<div class="updated notice is-dismissible">
			        <p><?php echo $defs->getDef('Thank you for contacting support! We will be in touch as soon as we can.'); ?></p>
			    </div>

<?php } ?>
	  <div class="wrap">
			<div style="min-width:45%;">
				<h2>Sweet Analytics</h2>

	      <?php if (!empty($config->error)): ?>
	        <div class="error">
	          <h2><?php echo $defs->getDef('An error occurred while activating the plugin'); ?></h2>
	          <div>
	            <p><?php echo $config->message; ?></p>
	      		</div>
	        </div>
	      <?php else: ?>
				<?php
$tracker = get_option('SWEET_TRACKER');
        $basketTracker = get_option('SWEET_BASKET_TRACKER');
        $trackerId = get_option('SWEET_TRACKER_ID');

        $checkTracker = $tracker ? 'checked' : '';
        $checkBasketTracker = $basketTracker ? 'checked' : '';
        $centerClass = '';

        ?>
					<div class="sweet-plugin-wrapper">
							<div class="sweet-general-info">
								<div class="sweet-sweet-general-info display-flex align-items-center">
									<div class="logo">
										<img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/logo.svg'; ?>" alt="Sweet Analytics">
									</div>

									<div class="sweet-general-info-content <?php echo $centerClass; ?>">

										<div class="sync-status display-flex">
											<div class="sync-item categories text-center">
												<img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/categories.svg'; ?>" alt="">
												<h4 class=""><?php echo 'Ecommerce' ?></h4>
											</div>
											<div class="sync-item products text-center">
												<img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/products.svg'; ?>" alt="">
												<h4 class=""><?php echo 'User Behavior' ?></h4>
											</div>
											<div class="sync-item orders text-center">
												<img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/orders.svg'; ?>" alt="">
												<h4 class=""><?php echo 'Sales' ?></h4>
											</div>
										</div>

									</div>

								</div>
							</div>

							<div class="wrapper-sweet display-flex">

								<div class="sweet-menu">
									<ul>
										<li><a href="javascript:void(0)" data-tab="#dashboard" class="sweet-menu-btn active"><?php echo $defs->getDef('Dashboard'); ?></a></li>
										<li><a href="javascript:void(0)" data-tab="#settings" class="sweet-menu-btn"><?php echo $defs->getDef('Settings'); ?></a></li>
										<li><a href="javascript:void(0)" data-tab="#support" class="sweet-menu-btn"><?php echo $defs->getDef('Support'); ?></a></li>
										<li><a href="javascript:void(0)" data-tab="#about" class="sweet-menu-btn"><?php echo $defs->getDef('About us'); ?></a></li>
									</ul>
								</div>

				        <div class="sweet-main-area flex-grow-1">

									<div id="dashboard" class="dashboard active sweet-tab-item">
					          <div class="info">
					            <p><strong><?php echo $defs->getDef('Site name'); ?>: </strong><?php echo $storeData['store_name']; ?></p>
					            <p><strong><?php echo $defs->getDef('Domain'); ?>: </strong><?php echo $storeData['domain']; ?></p>
					            <p><strong><?php echo $defs->getDef('Platform'); ?>: </strong><?php echo $storeData['platform_name']; ?>, version <?php echo $storeData['platform_version']; ?></p>
					            <p><strong><?php echo $defs->getDef('Extension'); ?>: </strong><?php echo $storeData['extension_name']; ?><?php if ($storeData['extension_version']) {
            echo ', version ' . $storeData['extension_version'];
        }
        ?></p>
					            <p><strong><?php echo $defs->getDef('Currency'); ?>: </strong><?php echo $storeData['currency']; ?></p>
					          </div>
									</div>

									<div id="settings" class="settings sweet-tab-item">
										<div class="">
											<form class="add:the-list: validate" method="post" enctype="multipart/form-data">

											<div class="sweet-form-group">
													<label for="trackerId"><?php echo $defs->getDef('Tracker ID'); ?>:</label>
													<input class="sweet-input" type="text" name="trackerId" value="<?php echo $trackerId ?>" id="trackerId">
													<span><?php echo $defs->getDef('Add your unique tracker ID from Sweet Analytics'); ?></span>
												</div>

												<div class="sweet-form-group">
													<input type="checkbox" name="tracker" id="tracker" value="true" <?php echo $checkTracker ?>>
													<label for="tracker"><?php echo $defs->getDef('Enable tracker'); ?></label>
												</div>

												<div class="sweet-form-group">
													<input type="checkbox" name="basketTracking" id="basketTracking" value="true" disabled> <!--<?php // echo $checkBasketTracker ?>-->
													<label for="basketTracking"><?php echo $defs->getDef('Enable basket tracking (not available yet)'); ?></label>
													</div>

												<input type="hidden" name="trackerSave" value="trackerSave">
												<input type="submit" value="<?php echo $defs->getDef('Save'); ?>" class="button button-primary menu_icons-ti-logger">

											</form>
										</div>
									</div>

									<div id="support" class="settings sweet-tab-item">
										<div class="">
											<form class="add:the-list: validate" method="post" enctype="multipart/form-data">

												<div class="sweet-form-group">
													<p><?php echo $defs->getDef('Message'); ?></p>
													<textarea class="sweet-textarea" name="message" id="message" rows="10" cols="30" placeholder="<?php echo $defs->getDef('Write your message here'); ?>"></textarea>
												</div>

												<div class="sweet-form-group">
													<input type="checkbox" name="includeLog" id="includeLog" value="true">
													<label for="includeLog"><?php echo $defs->getDef('Send logs'); ?></label>
												</div>

												<input type="hidden" name="contactSuport" value="email">
												<input type="submit" value="<?php echo $defs->getDef('Send'); ?>" class="button button-primary menu_icons-ti-logger">

											</form>
										</div>
									</div>


									<div id="about" class="about sweet-tab-item">
										<div>
											<p class="wrap-text">
												<?php echo $defs->getAbout(); ?>
											</p>
										</div>
									</div>
				        </div>
							</div>
							<footer class="sweet-footer">
								<p class="text-center"><?php echo $defs->getDef('Made with ♥ by Sweet Analytics'); ?></p>
							</footer>
					</div>
	      <?php endif;?>

			</div>
		</div><!-- end wrap -->
		<?php
}
}
