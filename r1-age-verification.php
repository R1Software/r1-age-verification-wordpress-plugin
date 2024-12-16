<?php
/**
 * Plugin Name: R1 Age Verification
 * Plugin URI: https://r1software.com/r1-age-verification-wordpress-plugin
 * Description: A plugin to add an age verification overlay to your WordPress site.
 * Version: 1.0
 * Author: R1 Software
 * Author URI: https://r1software.com
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin activation
function age_verification_activate()
{
    // Add default options
    add_option('age_verification_overlay_image', plugin_dir_url(__FILE__) . 'images/overlay.jpg');
    add_option('age_verification_default_image', plugin_dir_url(__FILE__) . 'images/default.jpg');
    add_option('age_verification_cookie_duration', 30); // Default 30 days
    add_option('age_verification_threshold_age', 21); // Default threshold age is 21

}
register_activation_hook(__FILE__, 'age_verification_activate');

// Plugin deactivation
function age_verification_deactivate()
{
    // Remove plugin options
    delete_option('age_verification_overlay_image');
    delete_option('age_verification_default_image');
    delete_option('age_verification_cookie_duration');
    delete_option('age_verification_threshold_age');
}
register_deactivation_hook(__FILE__, 'age_verification_deactivate');

// Add plugin options page
function age_verification_options_page()
{
    add_options_page(
        'Age Verification Options',
        'Age Verification',
        'manage_options',
        'age-verification',
        'age_verification_options_page_html'
    );
}
add_action('admin_menu', 'age_verification_options_page');

// Options page HTML
function age_verification_options_page_html()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save options
    if (isset($_POST['submit'])) {
        update_option('age_verification_overlay_image', sanitize_text_field($_POST['overlay_image']));
        update_option('age_verification_default_image', sanitize_text_field($_POST['default_image']));
        update_option('age_verification_cookie_duration', intval($_POST['cookie_duration']));
        update_option('age_verification_threshold_age', intval($_POST['threshold_age']));

        // Add settings updated message
        add_settings_error('age_verification_settings', 'age_verification_settings_updated', 'Settings updated.', 'updated');
    }

    // Show errors/updates
    settings_errors('age_verification_settings');

    // Get plugin options
    $overlay_image = get_option('age_verification_overlay_image');
    $default_image = get_option('age_verification_default_image');
    $cookie_duration = get_option('age_verification_cookie_duration');
    $threshold_age = get_option('age_verification_threshold_age');

    // Output options page HTML
    ?>
    <div class="wrap">
        <h1>Age Verification Options</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">Overlay Image</th>
                    <td>
                        <input type="text" name="overlay_image" value="<?php echo esc_attr($overlay_image); ?>" class="regular-text" placeholder="Enter the URL of the overlay image" />
                        <p class="description">The image to be used as the overlay background.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Default Image</th>
                    <td>
                        <input type="text" name="default_image" value="<?php echo esc_attr($default_image); ?>" class="regular-text" placeholder="Enter the URL of the default image" />
                        <p class="description">The image to be displayed in the age verification form.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Cookie Duration</th>
                    <td>
                        <input type="number" name="cookie_duration" value="<?php echo esc_attr($cookie_duration); ?>" class="small-text" min="1" /> days
                        <p class="description">The number of days the age verification cookie should last.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Threshold Age</th>
                    <td>
                        <input type="number" name="threshold_age" value="<?php echo esc_attr($threshold_age); ?>" class="small-text" min="18" /> years
                        <p class="description">The minimum age required to view the content.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
        <p style="text-align: center;">
            <small>Powered by <a href="https://r1software.com" target="_blank" style="color:green;">R1 Software</a></small>
        </p>
    </div>
    <?php
}

// Enqueue scripts and styles
function age_verification_enqueue_scripts()
{
    // Enqueue styles
    wp_enqueue_style('age-verification-styles', plugin_dir_url(__FILE__) . 'css/age-verification.css', array(), '1.0');

    // Enqueue scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('age-verification-scripts', plugin_dir_url(__FILE__) . 'js/age-verification.js', array('jquery'), '1.0', true);

    // Pass plugin options to the JavaScript
    $overlay_image = get_option('age_verification_overlay_image');
    $default_image = get_option('age_verification_default_image');
    $cookie_duration = get_option('age_verification_cookie_duration');
    $threshold_age = get_option('age_verification_threshold_age');

    wp_localize_script('age-verification-scripts', 'ageVerificationOptions', array(
        'overlayImage' => $overlay_image,
        'defaultImage' => $default_image,
        'cookieDuration' => $cookie_duration,
        'thresholdAge' => $threshold_age
    ));
}
add_action('wp_enqueue_scripts', 'age_verification_enqueue_scripts');

// Add age verification overlay
function age_verification_add_overlay()
{
    // Output the age verification HTML
    ?>
    <div id="R1_PORTAL">
        <div class="r1-age-verification-popup-container r1-age-verification-popup-show">
            <div class="Background__Container-sc-4lq1r6-0 sJhos" style="position: fixed;">
                <div class="Background__Base-sc-4lq1r6-1 egiTHk">
                </div>
                <div class="Background__Overlay-sc-4lq1r6-2 djWGwD" style="display: block; background-color: rgba(0, 0, 0, 0.7);"></div>
            </div>
            <div class="r1-age-verification-popup-inner">
                <div class="r1-age-verification-item-container">
                    <div class="r1-age-verification-item-error">
                        <div>You are not old enough to view this content. Please navigate away.</div>
                    </div>
                    <div class="r1-age-verification-item-content">
                        <div class="r1-age-verification-item-message">ARE YOU OF LEGAL SMOKING AGE?</div>
                        <div class="r1-age-verification-item-caption">Please, enter your year of birth:</div>
                        <div class="r1-age-verification-item-allow-container">
                            <div class="r1-age-verification-item-allow-form"><input type="number" inputmode="numeric"
                                    pattern="[0-9]{3}" name="year" placeholder="YYYY" maxlength="9999"
                                    class="r1-age-verification-item-allow-year-input"><button type="button"
                                    class="r1-age-verification-item-allow-year-submit">ENTER</button></div>
                        </div>
                        <div class="r1-age-verification-item-additionalInfo">By entering this website, you
                            certify that you are of legal smoking age in the state in which you reside.
                            <p style="text-align: center;">
                                <small>Powered by <a href="https://r1software.com" target="_blank" style="color:green;">R1 Software</a></small>
                            </p>
                        </div>
                    </div>
                    <div class="r1-age-verification-item-image">
                        <div class="r1-age-verification-item-image-imageContainer">
                            <div class="r1-age-verification-item-image-image"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'age_verification_add_overlay');