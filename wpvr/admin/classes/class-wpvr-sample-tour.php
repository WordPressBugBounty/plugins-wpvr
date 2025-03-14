<?php
/**
 * WPVR Sample Tour Class
 *
 * This class handles the creation of a sample virtual tour for the WPVR plugin.
 * It includes methods to copy necessary images to the WordPress uploads folder
 * and generate a sample tour post with predefined panorama data.
 *
 * @since 8.5.21
 */

class WPVR_Sample_Tour {


    /**
     * Check if running in WordPress Playground.
     *
     * @return bool True if in Playground, false otherwise.
     * @since 8.5.22
     */
    public function wpvr_is_playground() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Check for Playground domains
        $playground_domains = [
            'playground.wordpress.net', // Online Playground
            'localhost:5400',           // Local Playground default port (adjust if needed)
        ];
        foreach ($playground_domains as $domain) {
            if (strpos($host, $domain) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create a sample virtual tour for the WPVR plugin.
     *
     * This method copies the necessary images to the WordPress uploads folder
     * and generates a sample tour post with predefined panorama data.
     *
     * @since 8.5.21
     */
    public function create_sample_tour() {

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Initialize the Filesystem API
        global $wp_filesystem;
        if (!isset($wp_filesystem)) {
            $filesystem_initialized = WP_Filesystem();
            if (!$filesystem_initialized) {
                wp_send_json_error(__('Failed to initialize WP_Filesystem', 'wpvr'));
                return;
            }
        }

        $file_save_url = wp_upload_dir();
        $zip_file_path = WPVR_PLUGIN_DIR_PATH . 'sample_tour/wpvr_sample_tour.zip';
        $unzipfile = unzip_file($zip_file_path, $file_save_url['basedir'] . '/wpvr/temp/');

        if (is_wp_error($unzipfile)) {
            WPVR_Sample_Tour_Import::wpvr_delete_temp_file();
            wp_send_json_error(__('Failed to unzip file', 'wpvr'));
        }

        $result = glob($file_save_url["basedir"] . '/wpvr/temp/*.json');
        if (!$result) {
            WPVR_Sample_Tour_Import::wpvr_delete_temp_file();
            wp_send_json_error(__('Tour json file not found', 'wpvr'));
        }

        $tour_json = $result[0];
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $getfile = file_get_contents($tour_json, false, stream_context_create($arrContextOptions));

        $file_content = json_decode($getfile, true);

        $new_title = $file_content['title'];
        $new_data = $file_content['data'];

        if(empty($new_data)){
            return;
        }

        $new_post_id = wp_insert_post(array(
            'post_title'    => $new_title,
            'post_type'     => 'wpvr_item',
            'post_status'     => 'publish',
        ));
        if ($new_post_id) {
            if ($new_data['panoid']) {
                $new_data['panoid'] = 'pano' . $new_post_id;
            }
            if ($new_data['preview']) {
                $new_data['preview'] = WPVR_Sample_Tour_Import::prepare_preview_file_to_import($file_save_url, $new_post_id, $new_data);
            }

            if ($new_data['cpLogoImg']) {
                $new_data['cpLogoImg'] = WPVR_Sample_Tour_Import::prepare_company_log_image_to_import($new_data['cpLogoImg'], $file_save_url, $new_post_id, $new_data);
            }

            if ($new_data['bg_music_url']) {
                $new_data['bg_music_url'] = WPVR_Sample_Tour_Import::prepare_bg_music_url_to_import($new_data, $file_save_url, $new_post_id);
            }
            if ($new_data['panodata']) {

                if ($new_data['panodata']["scene-list"]) {

                    foreach ($new_data['panodata']["scene-list"] as $key => $panoscenes) {

                        if ($panoscenes['scene-type'] == 'cubemap') {

                            // face 0
                            if ($panoscenes["scene-attachment-url-face0"]) {
                                $cube_name = '_face0.jpg';
                                $new_data['panodata']["scene-list"][$key]['scene-attachment-url'] = WPVR_Sample_Tour_Import::prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key);
                            }

                            // face 1
                            if ($panoscenes["scene-attachment-url-face1"]) {
                                $cube_name = '_face1.jpg';
                                $new_data['panodata']["scene-list"][$key]['scene-attachment-url'] = WPVR_Sample_Tour_Import::prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key);
                            }

                            // face 2
                            if ($panoscenes["scene-attachment-url-face2"]) {
                                $cube_name = '_face2.jpg';
                                $new_data['panodata']["scene-list"][$key]['scene-attachment-url'] = WPVR_Sample_Tour_Import::prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key);
                            }

                            // face 3
                            if ($panoscenes["scene-attachment-url-face0"]) {
                                $cube_name = '_face3.jpg';
                                $new_data['panodata']["scene-list"][$key]['scene-attachment-url'] = WPVR_Sample_Tour_Import::prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key);
                            }

                            // face 4
                            if ($panoscenes["scene-attachment-url-face4"]) {
                                $cube_name = '_face4.jpg';
                                $new_data['panodata']["scene-list"][$key]['scene-attachment-url'] = WPVR_Sample_Tour_Import::prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key);
                            }

                            // face 5
                            if ($panoscenes["scene-attachment-url-face5"]) {
                                $cube_name = '_face5.jpg';
                                $new_data['panodata']["scene-list"][$key]['scene-attachment-url'] = WPVR_Sample_Tour_Import::prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key);
                            }
                        } else {
                            if ($panoscenes["scene-attachment-url"]) {
                                $cube_name = '.jpg';
                                $new_data['panodata']["scene-list"][$key]['scene-attachment-url'] = WPVR_Sample_Tour_Import::prepare_scene_attachment_url_to_import($panoscenes, $cube_name, $file_save_url, $new_post_id, $key);
                            }
                        }
                    }
                }
                update_post_meta($new_post_id, 'panodata', $new_data);
                WPVR_Sample_Tour_Import::wpvr_delete_temp_file();
            }
        }
    }

}
