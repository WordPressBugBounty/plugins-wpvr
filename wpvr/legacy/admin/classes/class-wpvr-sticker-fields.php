<?php
if (!defined('ABSPATH')) exit;

/** Sticker controls shared by every legacy hotspot repeater item. */
class WPVR_Sticker_Fields {
    public static function render($hotspot) {
        $templates = array(
            'social_proof' => __('Social Proof', 'wpvr'),
            'discount_button' => __('Simple Discount Button', 'wpvr'),
            'button_with_separate_icon' => __('Button with separate icon', 'wpvr'),
            'add_to_cart' => __('Add to cart', 'wpvr'),
            'social_share' => __('Social Share', 'wpvr'),
        );
        $template = $hotspot['hotspot-sticker-template'] ?? 'social_proof';
        if (!isset($templates[$template])) $template = 'social_proof';
        $buttons = 'discount_button button_with_separate_icon add_to_cart social_share';
        // name => label, control type, default, applicable templates (empty means all).
        $fields = array(
            'client-name' => array(__('Client Name', 'wpvr'), 'text', 'Elena R.', 'social_proof'),
            'client-avatar' => array(__('Client Avatar', 'wpvr'), 'media', '', 'social_proof'),
            'review-text' => array(__('Review Text', 'wpvr'), 'textarea', 'The support is super responsive and responds without worries to our requests and needs! Big up to the entire RexTheme team!', 'social_proof'),
            'rating' => array(__('Star Rating', 'wpvr'), 'number', 5, 'social_proof', 1, 5),
            'star-color' => array(__('Star Color', 'wpvr'), 'text', '#EF991F', 'social_proof'),
            'text-color' => array(__('Text Color', 'wpvr'), 'text', '#ffffff', 'social_proof add_to_cart'),
            'prefix-text' => array(__('Text Before Button', 'wpvr'), 'text', '$1,090 -', 'add_to_cart'),
            'sub-text' => array(__('Sub Text', 'wpvr'), 'text', 'Ready to ship', 'add_to_cart'),
            'show-placeholder-text' => array(__('Show Additional Text', 'wpvr'), 'toggle', 'on', 'add_to_cart social_share'),
            'main-bg' => array(__('Main Background', 'wpvr'), 'toggle', 'on', $buttons),
            'bg-color' => array(__('Background Color', 'wpvr'), 'text', '#201b2c', ''),
            'bg-opacity' => array(__('Background Opacity (%)', 'wpvr'), 'number', 95, '', 0, 100),
            'blur' => array(__('Blur (px)', 'wpvr'), 'number', 12, '', 0, 40),
            'brightness' => array(__('Brightness (%)', 'wpvr'), 'number', 100, '', 0, 200),
            'border-color' => array(__('Border Color', 'wpvr'), 'text', '#3a3051', ''),
            'border-radius' => array(__('Border Radius (px)', 'wpvr'), 'corners', 20, ''),
            'padding' => array(__('Padding (px)', 'wpvr'), 'sides', 21, $buttons),
            'btn-text' => array(__('Button Text', 'wpvr'), 'text', 'GET 20% OFF NOW', $buttons),
            'btn-url' => array(__('Button URL', 'wpvr'), 'url', '', 'discount_button button_with_separate_icon add_to_cart'),
            'btn-new-tab' => array(__('Open in New Tab', 'wpvr'), 'toggle', 'off', 'discount_button button_with_separate_icon add_to_cart'),
            'btn-bg' => array(__('Button Background', 'wpvr'), 'toggle', 'on', $buttons),
            'btn-color' => array(__('Button Color', 'wpvr'), 'text', '#EF991F', $buttons),
            'btn-text-color' => array(__('Button Text Color', 'wpvr'), 'text', '#000000', $buttons),
            'btn-width' => array(__('Button Width (px)', 'wpvr'), 'number', '', $buttons, 0),
            'btn-height' => array(__('Button Height (px)', 'wpvr'), 'number', '', $buttons, 0),
            'btn-radius' => array(__('Button Radius (px)', 'wpvr'), 'number', 10, $buttons, 0),
            'btn-border' => array(__('Button Border Width (px)', 'wpvr'), 'number', 0, $buttons, 0),
            'btn-border-color' => array(__('Button Border Color', 'wpvr'), 'text', '#EF991F', $buttons),
            'btn-text-size' => array(__('Button Text Size (px)', 'wpvr'), 'number', 18, $buttons, 8),
            'btn-text-weight' => array(__('Button Font Weight', 'wpvr'), 'number', 700, $buttons, 100, 900),
            'btn-icon' => array(__('Button Icon', 'wpvr'), 'text', 'fas fa-tag', 'discount_button button_with_separate_icon'),
            'btn-icon-color' => array(__('Button Icon Color', 'wpvr'), 'text', '#000000', 'discount_button button_with_separate_icon'),
            'icon-box-bg-color' => array(__('Icon Box Background Color', 'wpvr'), 'text', '#ffffff', 'button_with_separate_icon'),
            'icon-box-radius' => array(__('Icon Box Radius (px)', 'wpvr'), 'number', 30, 'button_with_separate_icon', 0),
            'icon-box-border' => array(__('Icon Box Border Width (px)', 'wpvr'), 'number', 0, 'button_with_separate_icon', 0),
            'icon-box-border-color' => array(__('Icon Box Border Color', 'wpvr'), 'text', '#ffffff', 'button_with_separate_icon'),
            'icon-box-size' => array(__('Icon Box Size (px)', 'wpvr'), 'number', 60, 'button_with_separate_icon', 20),
            'social-color' => array(__('Social Icon Color', 'wpvr'), 'text', '#ffffff', 'social_share'),
            'social-size' => array(__('Social Icon Size (px)', 'wpvr'), 'number', 20, 'social_share', 10),
            'social-gap' => array(__('Social Icon Gap (px)', 'wpvr'), 'number', 16, 'social_share', 0),
            'social-links' => array(__('Social Links', 'wpvr'), 'links', array(
                array('id' => '1', 'icon' => 'fab fa-linkedin-in', 'customSvg' => '', 'url' => 'https://linkedin.com', 'openNewTab' => 'on'),
                array('id' => '2', 'icon' => 'fab fa-facebook-f', 'customSvg' => '', 'url' => 'https://facebook.com', 'openNewTab' => 'on'),
                array('id' => '3', 'icon' => 'fab fa-instagram', 'customSvg' => '', 'url' => 'https://instagram.com', 'openNewTab' => 'on'),
                array('id' => '4', 'icon' => 'fab fa-dribbble', 'customSvg' => '', 'url' => 'https://dribbble.com', 'openNewTab' => 'on'),
            ), 'social_share'),
        );
        $special = array('bg-opacity' => 75, 'border-color' => '#40355a', 'btn-height' => 60, 'btn-text-weight' => 600);
        $overrides = array(
            'discount_button' => array('border-radius' => 15),
            'button_with_separate_icon' => array_merge($special, array('border-radius' => 135, 'btn-text' => 'Limited “Midnight Horizon” Edition', 'btn-color' => '#ffffff', 'btn-width' => 356, 'btn-radius' => 30, 'btn-border-color' => '#ffffff')),
            'add_to_cart' => array_merge($special, array('border-radius' => 15, 'btn-text' => 'ADD TO CART', 'btn-color' => '#3f04fe', 'btn-text-color' => '#ffffff', 'btn-width' => 163, 'btn-border-color' => '#ffffff', 'btn-icon' => 'none')),
            'social_share' => array_merge($special, array('border-radius' => 15, 'btn-text' => 'SHARE EXPERIENCE', 'btn-color' => '#201a2b', 'btn-text-color' => '#ffffff', 'btn-width' => 222, 'btn-border' => 1, 'btn-border-color' => '#3a3051', 'btn-icon' => 'none')),
        );
        $sections = array(
            'client-name' => array(__('Testimonial', 'wpvr'), 'social_proof'),
            'prefix-text' => array(__('Product Text', 'wpvr'), 'add_to_cart'),
            'main-bg' => array(__('Card Appearance', 'wpvr'), ''),
            'btn-text' => array(__('Button', 'wpvr'), $buttons),
            'icon-box-bg-color' => array(__('Separate Icon', 'wpvr'), 'button_with_separate_icon'),
            'social-color' => array(__('Social Icons', 'wpvr'), 'social_share'),
        );
        ?>
        <div class="wpvr-legacy-sticker-settings" style="display:<?php echo ($hotspot['hotspot-type'] ?? '') === 'sticker' ? 'block' : 'none'; ?>">
            <div class="wpvr-sticker-panel-heading"><?php esc_html_e('Sticker Settings', 'wpvr'); ?></div>
            <label class="wpvr-sticker-template-label">
                <?php esc_html_e('Sticker Template', 'wpvr'); ?>:
                <select name="hotspot-sticker-template" class="wpvr-sticker-template">
                    <?php foreach ($templates as $key => $label) { ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($template, $key); ?>><?php echo esc_html($label); ?></option>
                    <?php } ?>
                </select>
            </label>
            <?php foreach ($fields as $key => $field) {
                list($label, $type, $default, $applies) = $field;
                $defaults = array();
                $parts = $type === 'corners' ? array('topLeft', 'topRight', 'bottomRight', 'bottomLeft') : array('top', 'right', 'bottom', 'left');
                foreach ($templates as $id => $unused) {
                    $value = $overrides[$id][$key] ?? $default;
                    $defaults[$id] = in_array($type, array('corners', 'sides'), true) ? array_fill_keys($parts, $value) : $value;
                }
                $name = 'hotspot-sticker-' . $key;
                $value = $hotspot[$name] ?? ($key === 'main-bg' ? ($hotspot['hotspot-sticker-card-bg'] ?? $defaults[$template]) : $defaults[$template]);
                if (in_array($type, array('corners', 'sides'), true)) {
                    if (is_object($value)) $value = (array) $value;
                    if (!is_array($value)) $value = array_fill_keys($parts, is_numeric($value) ? $value : $defaults[$template][$parts[0]]);
                }
                if ($type === 'links' && !is_array($value)) {
                    $decoded = json_decode((string) $value, true);
                    $value = is_array($decoded) ? $decoded : $defaults[$template];
                }
                $visible = !$applies || in_array($template, explode(' ', $applies), true);
                ?>
                <?php if (isset($sections[$key])) { ?>
                    <h4 class="wpvr-sticker-section" data-templates="<?php echo esc_attr($sections[$key][1]); ?>"><?php echo esc_html($sections[$key][0]); ?></h4>
                <?php } ?>
                <div class="wpvr-sticker-field" data-templates="<?php echo esc_attr($applies); ?>" data-kind="<?php echo esc_attr($type); ?>" data-defaults="<?php echo esc_attr(wp_json_encode($defaults)); ?>" style="margin-top:15px;display:<?php echo $visible ? 'block' : 'none'; ?>">
                    <label style="display:block">
                        <span class="wpvr-sticker-field-label"><?php echo esc_html($label); ?>:</span>
                        <?php if ($type === 'textarea') { ?>
                            <textarea name="<?php echo esc_attr($name); ?>" class="wpvr-sticker-value" rows="4"><?php echo esc_textarea($value); ?></textarea>
                        <?php } elseif ($type === 'number') { ?>
                            <!-- The legacy repeater does not serialize number inputs. -->
                            <input type="hidden" name="<?php echo esc_attr($name); ?>" class="wpvr-sticker-value" value="<?php echo esc_attr($value); ?>">
                            <input type="number" class="wpvr-sticker-number" value="<?php echo esc_attr($value); ?>" step="1" min="<?php echo esc_attr($field[4] ?? 0); ?>" <?php if (isset($field[5])) { ?>max="<?php echo esc_attr($field[5]); ?>"<?php } ?>>
                        <?php } elseif ($type === 'toggle') { ?>
                            <select name="<?php echo esc_attr($name); ?>" class="wpvr-sticker-value">
                                <option value="on" <?php selected($value, 'on'); ?>><?php esc_html_e('On', 'wpvr'); ?></option>
                                <option value="off" <?php selected($value, 'off'); ?>><?php esc_html_e('Off', 'wpvr'); ?></option>
                            </select>
                        <?php } elseif (in_array($type, array('corners', 'sides', 'links'), true)) { ?>
                            <input type="hidden" name="<?php echo esc_attr($name); ?>" class="wpvr-sticker-value" value="<?php echo esc_attr(wp_json_encode($value)); ?>">
                        <?php } else { ?>
                            <input type="<?php echo esc_attr($type === 'media' ? 'url' : $type); ?>" name="<?php echo esc_attr($name); ?>" class="wpvr-sticker-value" value="<?php echo esc_attr($value); ?>">
                        <?php } ?>
                    </label>
                    <?php if (in_array($type, array('corners', 'sides'), true)) { ?>
                        <div class="wpvr-sticker-sides">
                        <?php foreach ($parts as $part) { ?>
                        <label class="wpvr-sticker-side">
                            <?php echo esc_html(array('topLeft' => __('Top left', 'wpvr'), 'topRight' => __('Top right', 'wpvr'), 'bottomRight' => __('Bottom right', 'wpvr'), 'bottomLeft' => __('Bottom left', 'wpvr'), 'top' => __('Top', 'wpvr'), 'right' => __('Right', 'wpvr'), 'bottom' => __('Bottom', 'wpvr'), 'left' => __('Left', 'wpvr'))[$part]); ?>
                            <input type="number" min="0" data-part="<?php echo esc_attr($part); ?>" value="<?php echo esc_attr($value[$part] ?? 0); ?>" style="width:100%">
                        </label>
                    <?php } ?>
                        </div>
                    <?php } elseif ($type === 'media') { ?>
                        <button type="button" class="button wpvr-sticker-avatar-select"><?php esc_html_e('Select Image', 'wpvr'); ?></button>
                        <button type="button" class="button wpvr-sticker-avatar-remove"><?php esc_html_e('Remove', 'wpvr'); ?></button>
                    <?php } elseif ($type === 'links') { ?>
                        <div class="wpvr-sticker-links"></div>
                        <button type="button" class="button wpvr-sticker-link-add"><?php esc_html_e('Add Social Link', 'wpvr'); ?></button>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }
}
