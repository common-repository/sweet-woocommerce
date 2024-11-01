<?php
namespace SweetAnalytics;

function sweet_activate()
{
    $setup = new SweetAnalyticsSetup();
    if ($setup->errorMessage) {
        set_transient('fx-admin-notice-sweet', true, 5);
    }
    register_uninstall_hook(__FILE__, 'sweet_uninstall');
}
register_activation_hook(__FILE__, 'sweet_activate');

function sweet_uninstall()
{
    /*  codes to perform during unistallation */
    $sweetOptions = array(
        'SWEET_INIT_ERROR',
        'SWEET_TRACKER',
        'SWEET_BASKET_TRACKER',
        'SWEET_TRACKER_ID',
    );

    foreach ($sweetOptions as $sweetOption) {
        delete_option($sweetOption);
    }

    file_put_contents(SWEET_ANALYTICS_PLUGIN_PATH . "/core/log/log.csv", "");
}

/* Add admin notice */
add_action('admin_notices', 'SweetAnalytics\sweetNotices');

/**
 * Admin Notice on Activation.
 * @since 0.0.1
 */
function sweetNotices()
{
    /* Check transient, if available display notice */
    if (get_transient('fx-admin-notice-sweet')) {
        initNotice();
        /* Delete transient, only display this notice once. */
        delete_transient('fx-admin-notice-sweet');
    } else {
        initNotice();
    }
}

function initNotice()
{
    $error = get_option('SWEET_INIT_ERROR');
    $success = get_option('SWEET_INIT_SUCCESS');
    $defs = new SweetAnalyticsDefs();
    if ($error):
    ?>
  <div class="error">
      <p><?php echo $defs->getDef($error); ?></p>
  </div>
  <?php
else:
        if (empty($success)):
        ?>
			    <div class="updated notice is-dismissible">
			        <p><?php echo $defs->getDef('Thank you for using Sweet Analytics! <strong>You are awesome</strong>.'); ?></p>
			    </div>
			    <?php
    update_option('SWEET_INIT_SUCCESS', true);
    endif;
    endif;
}

?>
