<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Responsible for managing Tour Import feature
 *
 * @link       http://rextheme.com/
 * @since      8.5.21
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin/classes
 */

class WPVR_Sample_Tour_Import {

    /**
     * Prepare tour import feature, if tour has preview file
     *
     * @param mixed $file_save_url
     * @param mixed $new_post_id
     * @param mixed $new_data
     *
     * @return void
     * @since 8.5.21
     */
    public static function prepare_preview_file_to_import($file_save_url, $new_post_id, $new_data)
    {
        $preview_file_path = WPVR_PLUGIN_DIR_PATH . 'sample-data/scene_preview.jpg';
        if (!file_exists($preview_file_path)) {
            return $new_data['preview']; // Return original if file doesn't exist
        }
        $media_get = self::wpvr_handle_media_import_from_path($preview_file_path, $new_post_id);
        if ($media_get['status'] == 'error') {
            wp_delete_post($new_post_id, true);
            wp_send_json_error($media_get['message']);
        } elseif ($media_get['status'] == 'success') {
            return $new_data['preview'] = $media_get['message'];
        } else {
            wp_delete_post($new_post_id, true);
            wp_send_json_error('Media transfer process failed');
        }
    }


    /**
     * Prepare tour import feature, if tour has company logo
     *
     * @param mixed $logo
     * @param mixed $file_save_url
     * @param mixed $new_post_id
     * @param mixed $new_data
     *
     * @return void
     * @since 8.5.21
     */
    public static function prepare_company_log_image_to_import($logo, $file_save_url, $new_post_id, $new_data)
    {
        $get_logo_format = explode(".", $logo);
        $logo_format = end($get_logo_format);
        $logo_file_path = WPVR_PLUGIN_DIR_PATH . 'sample-data/logo_img.' . $logo_format;
        if (!file_exists($logo_file_path)) {
            return $new_data['cpLogoImg']; // Return original if file doesn't exist
        }
        $media_get = self::wpvr_handle_media_import_from_path($logo_file_path, $new_post_id);
        if ($media_get['status'] == 'error') {
            wp_delete_post($new_post_id, true);
            wp_send_json_error($media_get['message']);
        } elseif ($media_get['status'] == 'success') {
            return $new_data['cpLogoImg'] = $media_get['message'];
        } else {
            wp_delete_post($new_post_id, true);
            wp_send_json_error('Media transfer process failed');
        }
    }


    /**
     * Prepare tour import feature, if tour has background music url
     *
     * @param mixed $new_data
     * @param mixed $file_save_url
     * @param mixed $new_post_id
     *
     * @return array
     * @since 8.5.21
     */
    public static function prepare_bg_music_url_to_import($new_data, $file_save_url, $new_post_id)
    {
        $music_url = $new_data['bg_music_url'];
        $get_music_format = explode(".", $music_url);
        $music_format = end($get_music_format);
        $music_file_path = WPVR_PLUGIN_DIR_PATH . 'sample-data/bg_music.' . $music_format;
        if (!file_exists($music_file_path)) {
            return $new_data['bg_music_url']; // Return original if file doesn't exist
        }
        $media_get = self::wpvr_handle_media_import_from_path($music_file_path, $new_post_id);
        if ($media_get['status'] == 'error') {
            wp_delete_post($new_post_id, true);
            wp_send_json_error($media_get['message']);
        } elseif ($media_get['status'] == 'success') {
            return $new_data['bg_music_url'] = $media_get['message'];
        } else {
            wp_delete_post($new_post_id, true);
            wp_send_json_error('Media transfer process failed');
        }
    }


    /**
     * Prepare scene attachment url to import
     *
     * @param mixed $panoscenes
     * @param mixed $cube_name
     * @param mixed $file_save_url
     * @param mixed $new_post_id
     * @param mixed $key
     *
     * @return array
     * @since 8.5.21
     */
    public static function prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key)
    {
        $scene_id = $panoscenes['scene-id'];
        $scene_file_path = WPVR_PLUGIN_DIR_PATH . 'sample-data/' . $scene_id . $cube_name;
        if (!file_exists($scene_file_path)) {
            return null; // Return null if file doesn't exist
        }
        $media_get = self::wpvr_handle_media_import_from_path($scene_file_path, $new_post_id);
        if ($media_get['status'] == 'error') {
            wp_delete_post($new_post_id, true);
            wp_send_json_error($media_get['message']);
        } elseif ($media_get['status'] == 'success') {
            return $media_get['message'];
        } else {
            wp_delete_post($new_post_id, true);
            wp_send_json_error('Media transfer process failed');
        }
    }


    /**
     * Handle media importing from local file path
     *
     * @param string $file_path Local file path
     * @param int $post_id Post ID
     *
     * @return array
     * @since 8.5.21
     */
    public static function wpvr_handle_media_import_from_path($file_path, $post_id)
    {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return array(
                'status' => 'error',
                'message' => 'File does not exist or is not readable: ' . $file_path
            );
        }

        // Include required WordPress files
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Create a temporary copy for WordPress to handle
        $temp_file = wp_tempnam(basename($file_path));
        if (!copy($file_path, $temp_file)) {
            return array(
                'status' => 'error',
                'message' => 'Failed to create temporary file copy'
            );
        }

        $file_array = array(
            'name' => basename($file_path),
            'tmp_name' => $temp_file
        );

        $id = media_handle_sideload($file_array, $post_id);

        if (is_wp_error($id)) {
            @unlink($temp_file);
            return array(
                'status' => 'error',
                'message' => $id->get_error_message()
            );
        }

        $value = wp_get_attachment_url($id);
        return array(
            'status' => 'success',
            'message' => $value
        );
    }

    /**
     * Handle media importing
     *
     * @param mixed $url
     * @param mixed $post_id
     *
     * @return array
     * @since 8.5.21
     */
    public static function wpvr_handle_media_import($url, $post_id)
    {
        add_filter('https_ssl_verify', '__return_false');
        add_filter('https_local_ssl_verify', '__return_false');

        $tmp = download_url($url);
        $file_array = array(
            'name' => basename($url),
            'tmp_name' => $tmp
        );

        if (is_wp_error($tmp)) {
            @unlink($file_array['tmp_name']);
            return (array(
                'status' => 'error',
                'message' => $tmp->get_error_message()
            ));
        }

        $id = media_handle_sideload($file_array, $post_id);

        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
            return (array(
                'status' => 'error',
                'message' => $tmp->get_error_message()
            ));
        }
        remove_filter('https_ssl_verify', '__return_false');
        remove_filter('https_local_ssl_verify', '__return_false');

        $value = wp_get_attachment_url($id);
        return (array(
            'status' => 'success',
            'message' => $value
        ));
    }


    /**
     * Delete temporary files (deprecated - no longer needed with direct file loading)
     *
     * @return void
     * @since 8.5.21
     * @deprecated No longer needed with direct file loading approach
     */
    public static function wpvr_delete_temp_file()
    {
        // This method is deprecated and no longer needed since we load files directly
        // from the sample-data directory instead of extracting from ZIP files.
        // Keeping for backward compatibility but it does nothing.
        return;
    }

}
