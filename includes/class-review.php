<?php

if (!defined('ABSPATH')) {
    exit;
}

class GF_Restrict_Dates_Review {

    public function __construct() {
        add_action('admin_notices', [$this, 'review_request']);
        add_action('wp_ajax_pcafe_rda_review_dismiss', [$this, 'review_dismiss']);
        add_action('admin_notices', array($this, 'rda_admin_notice'));
        add_action("admin_footer", array($this, 'rda_footer_script'));
        add_action('wp_ajax_rda-notice-dismiss', array($this, 'rda_ajax_fn_dismiss_notice'));
    }

    public function review_request() {
        if (! is_super_admin()) {
            return;
        }

        $time = time();
        $load = false;

        $review = get_option('pcafe_rda_review_status');

        if (! $review) {
            $review_time = strtotime("+15 days", time());
            update_option('pcafe_rda_review_status', $review_time);
        } else {
            if (! empty($review) && $time > $review) {
                $load = true;
            }
        }
        if (! $load) {
            return;
        }

        $this->review();
    }

    public function review() {
        $current_user = wp_get_current_user();
?>
        <div class="notice notice-info is-dismissible pcafe_rda_review_notice">
            <p><?php echo sprintf(__('Hey %1$s 👋, I noticed you are using <strong>%2$s</strong> for a few days - that\'s Awesome!  If you feel <strong>%2$s</strong> is helping your business to grow in any way, Could you please do us a BIG favor and give it a 5-star rating on WordPress to boost our motivation?', 'gravityforms'), $current_user->display_name, 'Restrict Dates Add-On For Gravity Forms'); ?></p>

            <ul style="margin-bottom: 5px">
                <li style="display: inline-block">
                    <a style="padding: 5px 5px 5px 0; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://wordpress.org/support/plugin/restrict-dates-add-on-for-gravity-forms/reviews/#new-post') ?>">
                        <span class="dashicons dashicons-external"></span><?php esc_html_e(' Ok, you deserve it!', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="already_done" data-status="already">
                        <span class="dashicons dashicons-smiley"></span>
                        <?php esc_html_e('I already did', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="later" data-status="later">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('Maybe Later', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://pluginscafe.com/support/') ?>">
                        <span class="dashicons dashicons-sos"></span>
                        <?php esc_html_e('I need help', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="never" data-status="never">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e('Never show again', 'gravityforms') ?>
                    </a>
                </li>
            </ul>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.already_done, .later, .never, .notice-dismiss', function(event) {
                    event.preventDefault();
                    var $this = $(this);
                    var status = $this.attr('data-status');
                    data = {
                        action: 'pcafe_rda_review_dismiss',
                        status: status,
                    };
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: data,
                        success: function(data) {
                            $('.pcafe_rda_review_notice').remove();
                        },
                        error: function(data) {}
                    });
                });
            });
        </script>
        <?php
    }

    public function review_dismiss() {
        $status = $_POST['status'];

        if ($status == 'already' || $status == 'never') {
            $next_try     = strtotime("+30 days", time());
            update_option('pcafe_rda_review_status', $next_try);
        } else if ($status == 'later') {
            $next_try     = strtotime("+10 days", time());
            update_option('pcafe_rda_review_status', $next_try);
        }
        wp_die();
    }

    public function rda_is_active_gravityforms() {
        if (!method_exists('GFForms', 'include_payment_addon_framework')) {
            return false;
        }
        return true;
    }

    function rda_admin_notice() {

        $show = false;
        if (rdfgf_fs()->is_not_paying()) {
            $show = true;
        }

        if (isset($_GET['show_notices'])) {
            delete_transient('rda-notice');
            $show = true;
        }

        if (! $this->rda_is_active_gravityforms()) { ?>
            <div id="rda-notice-error" class="rda-notice-error notice notice-error">
                <div class="notice-container" style="padding:10px">
                    <span> <?php _e("Restrict Dates Add-On needs to active gravity forms.", "gravityforms"); ?></span>
                </div>
            </div>
            <?php
        } else {
            if ($show && false == get_transient('rda-notice') && current_user_can('install_plugins')) {
            ?>

                <div id="rda-notice" class="rda-notice notice is-dismissible">
                    <div class="notice_container">
                        <div class="notice_wrap">
                            <div class="rda_img">
                                <img width="100px" src="<?php echo GF_RESTRICT_DATES_ADDON_URL; ?>assets/images/rda-logo.png" class="rda_logo" alt="restric-date-add-on-gravity-forms">
                            </div>
                            <div class="notice-content">
                                <div class="notice-heading">
                                    <?php _e("Hi there, Thanks for using Restrict Dates Add-On for Gravity Forms", "gravityforms"); ?>
                                </div>
                                <?php _e("Did you know our PRO version includes the ability to use prevent submission with wrong dates, inline datepicker and more features? Check it out!", "gravityforms"); ?>
                                <div class="rda-review-notice-container">
                                    <a href="https://pluginscafe.com/plugin/restrict-dates-for-gravity-forms-pro/" class="rda-notice-close rda-review-notice button-primary" target="_blank">
                                        <?php _e("See The Demo", "gravityforms"); ?>
                                    </a>
                                    <span class="dashicons dashicons-smiley"></span>
                                    <a href="#" class="rda-notice-close notice-dis rda-review-notice">
                                        <?php _e("Dismiss", "gravityforms"); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="rda_upgrade_btn">
                            <a href="<?php echo rdfgf_fs()->get_upgrade_url(); ?>">
                                <?php _e('Upgrade Now!', 'gravityforms'); ?>
                            </a>
                        </div>
                    </div>
                    <style>
                        .notice_container {
                            display: flex;
                            align-items: center;
                            padding: 10px 0;
                            gap: 15px;
                            justify-content: space-between;
                        }

                        img.rda_logo {
                            max-width: 90px;
                        }

                        .notice-heading {
                            font-size: 16px;
                            font-weight: 500;
                            margin-bottom: 5px;
                        }

                        .rda-review-notice-container {
                            margin-top: 11px;
                            display: flex;
                            align-items: center;
                        }

                        .rda-notice-close {
                            padding-left: 5px;
                        }

                        span.dashicons.dashicons-smiley {
                            padding-left: 15px;
                        }

                        .notice_wrap {
                            display: flex;
                            align-items: center;
                            gap: 15px;
                        }

                        .rda_upgrade_btn a {
                            text-decoration: none;
                            font-size: 15px;
                            background: #7BBD02;
                            color: #fff;
                            display: inline-block;
                            padding: 10px 20px;
                            border-radius: 3px;
                            transition: 0.3s;
                        }

                        .rda_upgrade_btn a:hover {
                            background: #69a103;
                        }
                    </style>
                </div>

        <?php
            }
        }
    }

    function rda_ajax_fn_dismiss_notice() {
        $notice_id = (isset($_POST['notice_id']) ? sanitize_key($_POST['notice_id']) : '');
        $repeat_notice_after = 60 * 60 * 24 * 7;
        if (!empty($notice_id)) {
            if (!empty($repeat_notice_after)) {
                set_transient($notice_id, true, $repeat_notice_after);
                wp_send_json_success();
            }
        }
    }

    function rda_footer_script() {
        ?>

        <script type="text/javascript">
            var $ = jQuery;
            var admin_url_rda = '<?php echo  admin_url("admin-ajax.php"); ?>';
            jQuery(document).on("click", '#rda-notice .notice-dis', function() {
                $(this).parents('#rda-notice').find('.notice-dismiss').click();
            });
            jQuery(document).on("click", '#rda-notice .notice-dismiss', function() {

                var notice_id = $(this).parents('#rda-notice').attr('id') || '';
                jQuery.ajax({
                    url: admin_url_rda,
                    type: 'POST',
                    data: {
                        action: 'rda-notice-dismiss',
                        notice_id: notice_id,
                    },
                });
            });
        </script>

<?php
    }
}

new GF_Restrict_Dates_Review();
