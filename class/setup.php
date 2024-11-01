<?php
namespace SweetAnalytics;

define('SWEET_ANALYTICS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SWEET_ANALYTICS_PLUGIN_URL', plugin_dir_url(__FILE__));
require_once SWEET_ANALYTICS_PLUGIN_PATH . '../core/sweet.php';
require_once SWEET_ANALYTICS_PLUGIN_PATH . '/defs.php';
require_once SWEET_ANALYTICS_PLUGIN_PATH . '../core/Logger.php';
require_once SWEET_ANALYTICS_PLUGIN_PATH . '../sweet-admin-functions.php';
require_once SWEET_ANALYTICS_PLUGIN_PATH . '../sweet-tracker-functions.php';


class SweetAnalyticsSetup
{

    public $sweetObj;
    public $errorMessage = "";
    private $shopInfo;

    public function __construct()
    {
        $this->sweetObj = new SweetAnalyticsCore();
        $config = $this->fetchShopInfo();
        $this->shopInfo = $config;
    }

    public function getShopInfo()
    {
        return $this->shopInfo;
    }

    public function getTrackerId()
    {
        $trackerId = get_option('SWEET_TRACKER_ID');
        return $trackerId;
    }

    private function woocommerce_version_check()
    {
        if (function_exists('is_woocommerce_active') && is_woocommerce_active()) {
            global $woocommerce;
            return $woocommerce->version;
        }
        return false;
    }

    private function fetchShopInfo()
    {
        $blogInfoArray = array('store_name' => 'name', 'platform_version' => 'version', 'domain' => 'url');

        $timeZone = get_option('timezone_string');
        if (empty($timeZone)) {
            $timeZone = date_default_timezone_get();
        }

        $storeInfo = array(
            'extension_version' => $this->woocommerce_version_check(),
            'extension_name' => 'Woocommerce',
            'currency' => get_woocommerce_currency(),
            'timezone' => $timeZone,
            'platform_name' => 'Wordpress',
        );

        foreach ($blogInfoArray as $key => $value) {
            $storeInfo[$key] = get_bloginfo($value);
        }

        return $storeInfo;
    }

}
