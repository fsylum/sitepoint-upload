<?php
/*
Plugin Name: Simple Uploader
Plugin URI: http://sitepoint.com
Description: Simple plugin to demonstrate AJAX upload with WordPress
Version: 0.1.0
Author: Firdaus Zahari
Author URI: http://www.sitepoint.com/author/fzahari/
*/
function su_allow_subscriber_to_uploads() {
    $subscriber = get_role('subscriber');

    if ( ! $subscriber->has_cap('upload_files') ) {
        $subscriber->add_cap('upload_files');
    }
}
add_action('admin_init', 'su_allow_subscriber_to_uploads');

function su_image_form_html(){
    ob_start();
    ?>
        <?php if ( is_user_logged_in() ): ?>
            <p class="form-notice"></p>
            <form action="" method="post" class="image-form">
                <?php wp_nonce_field('image-submission'); ?>
                <p><input type="text" name="user_name" placeholder="Your Name" required></p>
                <p><input type="email" name="user_email" placeholder="Your Email Address" required></p>
                <p class="image-notice"></p>
                <p><input type="file" name="async-upload" class="image-file" accept="image/*" required></p>
                <input type="hidden" name="image_id">
                <input type="hidden" name="action" value="image_submission">
                <div class="image-preview"></div>
                <hr>
                <p><input type="submit" value="Submit"></p>
            </form>
        <?php else: ?>
            <p>Please <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">login</a> first to submit your image.</p>
        <?php endif; ?>
    <?php
    $output = ob_get_clean();
    return $output;
}
add_shortcode('image_form', 'su_image_form_html');

function su_load_scripts() {
    wp_enqueue_script('image-form-js', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), '0.1.0', true);

    $data = array(
                'upload_url' => admin_url('async-upload.php'),
                'ajax_url'   => admin_url('admin-ajax.php'),
                'nonce'      => wp_create_nonce('media-form')
            );

    wp_localize_script( 'image-form-js', 'su_config', $data );
}
add_action('wp_enqueue_scripts', 'su_load_scripts');

function su_image_submission_cb() {
    check_ajax_referer('image-submission');

    $user_name  = filter_var( $_POST['user_name'],FILTER_SANITIZE_STRING );
    $user_email = filter_var( $_POST['user_email'], FILTER_VALIDATE_EMAIL );
    $image_id   = filter_var( $_POST['image_id'], FILTER_VALIDATE_INT );

    if ( ! ( $user_name && $user_email && $image_id ) ) {
        wp_send_json_error( array('msg' => 'Validation failed. Please try again later.') );
    }

    $to      = get_option('admin_email');
    $subject = 'New image submission!';
    $message = sprintf(
                    'New image submission from %s (%s). Link: %s',
                    $user_name,
                    $user_email,
                    wp_get_attachment_url( $image_id )
                );

    $result = wp_mail( $to, $subject, $message );

    if ( $result ) {
        wp_send_json_error( array('msg' => 'Email failed to send. Please try again later.') );
    } else {
        wp_send_json_success( array('msg' => 'Your submission successfully sent.') );
    }
}
add_action('wp_ajax_image_submission', 'su_image_submission_cb');
