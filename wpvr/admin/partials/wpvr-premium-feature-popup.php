<?php
/**
 * Premium feature upgrade popup.
 *
 * Renders only the #wpvr_premium_feature_popup overlay.
 * Include this partial instead of the full wpvr_confirmation_alert.php
 * on pages that need the hotspot-limit notice but not the delete/alert modals.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    wpvr
 * @subpackage wpvr/admin/partials
 */
?>
<!-- `wpvr-premium-feature` block -->
<section class="wpvr-premium-feature" id="wpvr_premium_feature_popup" style="display:none">
    <div class="wpvr-premium-feature__wrapper">
        <!-- `wpvr-premium-feature__body` element in the `wpvr-premium-feature` block  -->
        <span class="wpvr-premium-feature__close-btn" id="wpvr_premium_feature_close">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                    <g clip-path="url(#clip0_1_11)">
                        <path d="M16.5 5.5L5.5 16.5" stroke="#A8B3C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.5 5.5L16.5 16.5" stroke="#A8B3C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_1_11">
                        <rect width="22" height="22" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
			</span>
        <div class="wpvr-premium-feature__body">

            <!-- `wpvr-premium-feature__message` element in the `wpvr-premium-feature` block  -->
            <div class="wpvr-premium-feature__message">

                <span class="wpvr-premium-feature__svg-icon" >
                    
                    <svg width="114" height="96" viewBox="0 0 114 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M32 71.9351C24.4247 70.7671 23.3425 67.4087 21.9366 57C20.954 66.876 19.7021 70.621 12 71.7469C18.7172 74.5633 21.0786 75.5852 22.2785 87C23.9334 75.4393 25.4073 74.4172 32 71.9351Z" fill="#216DF0"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8091 47.9512C7.33608 47.2512 6.69721 45.2385 5.86713 39C5.28757 44.9191 4.54782 47.1637 0 47.8385C3.96697 49.5264 5.3611 50.139 6.06851 56.9805C7.04565 50.0515 7.91712 49.4389 11.8091 47.9512Z" fill="#00B4FF"/>
                    <circle cx="63" cy="48" r="48" fill="#00B4FF" fill-opacity="0.1"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65.9115 29.8177C64.3273 27.3941 60.7315 27.3941 59.1473 29.8177L51.1486 42.0535L45.0348 38.5896C41.9718 36.8542 38.2891 39.5738 39.1188 42.9584L43.2724 59.9021C43.4594 60.6652 44.1521 61.2026 44.9484 61.2026H80.1104C80.9068 61.2026 81.5995 60.6652 81.7866 59.9021L85.94 42.9584C86.7699 39.5738 83.0871 36.8542 80.024 38.5896L73.9101 42.0535L65.9115 29.8177ZM63.0125 31.6584C62.7864 31.3122 62.2725 31.3122 62.0464 31.6584L53.1599 45.2522C52.6624 46.0132 51.6472 46.2562 50.851 45.8052L43.316 41.5361C42.8785 41.2882 42.3524 41.6768 42.4709 42.1603L46.3057 57.8038H78.7532L82.588 42.1603C82.7064 41.6768 82.1805 41.2882 81.7428 41.5361L74.2078 45.8052C73.4115 46.2562 72.3965 46.0132 71.8991 45.2522L63.0125 31.6584Z" fill="#00B4FF"/>
                    <path d="M46.4365 64.6012C45.4842 64.6012 44.7122 65.3621 44.7122 66.3007C44.7122 67.2391 45.4842 68 46.4365 68H78.6233C79.5755 68 80.3475 67.2391 80.3475 66.3007C80.3475 65.3621 79.5755 64.6012 78.6233 64.6012H46.4365Z" fill="#00B4FF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M114 26.961C109.455 26.2602 108.806 24.2452 107.962 18C107.372 23.9256 106.621 26.1726 102 26.8481C106.03 28.538 107.447 29.1511 108.167 36C109.16 29.0636 110.044 28.4503 114 26.961Z" fill="#216DF0"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M108.809 74.9512C104.336 74.2512 103.697 72.2385 102.867 66C102.288 71.9191 101.548 74.1637 97 74.8385C100.967 76.5264 102.361 77.139 103.069 83.9805C104.046 77.0515 104.917 76.4389 108.809 74.9512Z" fill="#00B4FF"/>
                    </svg>

                </span>

                <h4 class="wpvr-premium-feature__heading">
                    <?php esc_html_e( 'This is a Premium Feature', 'wpvr' ); ?>
                </h4>

                <p class="wpvr-premium-feature__subheading">
                    <?php esc_html_e( 'Upgrade to Pro to unlock this and start using the feature.', 'wpvr' ); ?>
                </p>

                <div class="wpvr-premium-feature__btn-area">

                    <?php
                        $current_date = date('Y-m-d H:i:s');
                        $start_date = '2026-03-04 00:00:00';
                        $end_date = '2026-03-16 23:59:59';
                        $discount_percentage = '';
                        $discount_price = '';
                        if ($current_date >= $start_date && $current_date <= $end_date) {
                            $discount_percentage = "Save 30%";
                            $discount_price = "$69.99";
                        } 
                        else {
                            $discount_percentage = "Save 20%";
                            $discount_price = "$79.99";
                        }
                        $price = '$99.99';
                    ?>

                    <div class="wpvr-premium-feature__discount-price">
                        <p class="wpvr-premium-feature__discount-price-label" data-discount="<?php echo $discount_percentage; ?>"><?php printf( esc_html__('Starting at %s/year', 'wpvr'), '<span style= "font-weight:600; color:#0F2F72;">' . esc_html( $discount_price ) . '</span>' ); ?></p>
                        <p style="text-decoration: line-through; color: #999;"><?php printf( esc_html__('Normally %s/year', 'wpvr'), esc_html( $price ) ); ?></p>
                        
                    </div>

                    <a href="https://rextheme.com//wpvr/wpvr-pricing/" class="wpvr-premium-feature__btn" target="_blank" role="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="21" viewBox="0 0 17 21" fill="none"><path d="M11.4548 7.28939H5.20673V5.20668C5.20764 4.37842 5.53706 3.58433 6.12274 2.99866C6.70841 2.41299 7.50249 2.08356 8.33076 2.08265C9.787 2.08265 11.1441 3.08942 11.4878 4.42465C11.5212 4.55781 11.5805 4.68306 11.6625 4.79319C11.7444 4.90332 11.8474 4.99616 11.9653 5.06636C12.0833 5.13657 12.214 5.18275 12.3498 5.20225C12.4857 5.22176 12.6241 5.2142 12.7571 5.18001C12.89 5.14582 13.0149 5.08568 13.1245 5.00304C13.2341 4.92041 13.3263 4.81691 13.3958 4.69851C13.4652 4.58011 13.5106 4.44914 13.5292 4.31314C13.5479 4.17714 13.5395 4.03879 13.5044 3.90606C12.9222 1.64285 10.7465 0 8.33076 0C6.95036 0.0016277 5.62696 0.550709 4.65086 1.5268C3.67476 2.50289 3.12567 3.82628 3.12403 5.20668V7.73075C2.19679 8.13589 1.4076 8.80227 0.852824 9.64851C0.298052 10.4948 0.0017136 11.4842 0 12.4961V15.6201C0.00164064 17.0006 0.550734 18.3239 1.52683 19.3C2.50293 20.2761 3.82633 20.8252 5.20673 20.8268H11.4548C12.8352 20.8252 14.1586 20.2761 15.1347 19.3C16.1108 18.3239 16.6599 17.0006 16.6615 15.6201V12.4961C16.6599 11.1157 16.1108 9.7923 15.1347 8.8162C14.1586 7.8401 12.8352 7.29101 11.4548 7.28939ZM14.5788 15.6201C14.5779 16.4484 14.2485 17.2425 13.6628 17.8282C13.0771 18.4138 12.2831 18.7433 11.4548 18.7442H5.20673C4.37847 18.7433 3.58438 18.4138 2.99871 17.8282C2.41303 17.2425 2.08361 16.4484 2.0827 15.6201V12.4961C2.08361 11.6679 2.41303 10.8738 2.99871 10.2881C3.58438 9.70242 4.37847 9.37299 5.20673 9.37209H11.4548C12.2831 9.37299 13.0771 9.70242 13.6628 10.2881C14.2485 10.8738 14.5779 11.6679 14.5788 12.4961V15.6201ZM9.37209 13.5374V14.5788C9.37209 14.8549 9.26238 15.1198 9.06709 15.3151C8.8718 15.5104 8.60694 15.6201 8.33076 15.6201C8.05458 15.6201 7.78972 15.5104 7.59443 15.3151C7.39915 15.1198 7.28944 14.8549 7.28944 14.5788V13.5374C7.28944 13.2613 7.39915 12.9964 7.59443 12.8011C7.78972 12.6058 8.05458 12.4961 8.33076 12.4961C8.60694 12.4961 8.8718 12.6058 9.06709 12.8011C9.26238 12.9964 9.37209 13.2613 9.37209 13.5374Z" fill="white"/></svg>
                        <?php esc_html_e( 'Upgrade to PRO Now', 'wpvr' ); ?>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- `wpvr-premium-feature` block  end -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let discountLabel = document.querySelector(".wpvr-premium-feature__discount-price-label");
        if (discountLabel) {
            discountLabel.style.setProperty("--discount-content-value", `"${discountLabel.getAttribute('data-discount')}"`);
        }
    });
</script>

<style>
    p.wpvr-premium-feature__discount-price-label:before {
        content: var(--discount-content-value, "Save 30%");
    }
</style>
