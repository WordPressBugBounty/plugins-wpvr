<div id="wpvr-admin-notice-five-star-review" class="wpvr-admin-notice-dashboard wpvr-promo-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpvr-dismiss-notice-five-star-review') ); ?>" data-id="five-star-review" data-lifespan="0">
    <div class="wpvr-review-step wpvr-review-step-1">
        <p> <?php echo __("Hey, I noticed you've made nice tour with WP VR! Are you enjoying WP VR?",'wpvr'); ?> </p>
        <div class="wpvr-review-actions">
            <button class="button-primary wpvr-review-switch-step" data-step="3" ><?php echo __("Yes","wpvr") ?></button><br>
            <button class="button-link wpvr-review-switch-step" data-step="2" id="review_not_now_feed_back"><?php echo __("Not Really","wpvr") ?></button>
        </div>
    </div>
<!--    <div class="wpvr-review-step wpvr-review-step-2" style="display:none;">-->
<!--        <p>--><?php //// echo __("We're sorry to hear you aren't enjoying Easy Digital Downloads. We would love a chance to improve. Could you take a minute and let us know what we can do better?","wpvr") ?><!--</p>-->
<!--        <div class="wpvr-review-actions">-->
<!--            <a href="https://rextheme.com/your-account/?active_tab=support" class="button button-secondary wpvr-promo-notice-dismiss" target="_blank">--><?php //echo __("Give Feedback","wpvr") ?><!--</a><br>-->
<!--            <button class="button-link wpvr-promo-notice-dismiss-no-feedback" id="review_not_now_feed_back">--><?php //// echo __("No thanks","wpvr") ?><!--</button>-->
<!--        </div>-->
<!--    </div>-->
    <div class="wpvr-review-step wpvr-review-step-3" style="display:none;">
        <p><?php echo __("That's awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us grow and motivate us?","wpvr") ?> <strong>~<?php echo __("Lincoln Islam","wpvr") ?></strong> -  Founder & Visionary</p>
        <div class="wpvr-review-actions">
            <a href="https://wordpress.org/support/plugin/wpvr/reviews/?filter=5#new-post" class="button button-primary wpvr-promo-notice-dismiss" target="_blank" rel="noopener noreferrer" id="review_rate_now">Ok, you deserve it!</a><br>
            <button class="button-link wpvr-promo-notice-dismiss" id="review_rated_already"><?php echo __("Already given","wpvr") ?></button>
            <button class="button-link wpvr-promo-notice-dismiss-next-week" id="review_not_now"><?php echo __("No thanks","wpvr") ?></button>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var steps = $('.wpvr-review-switch-step');
            steps.on('click', function (e) {
                e.preventDefault();
                var target = $(this).data('step');
                if (target) {
                    var notice = $(this).closest('.wpvr-promo-notice');
                    var review_step = notice.find('.wpvr-review-step-' + target);
                    if (review_step.length) {
                        var thisStep = $(this).closest('.wpvr-review-step');
                        // wpvrFadeOut(thisStep);
                        thisStep.hide();
                        review_step.show()
                        // wpvrFadeIn(review_step);
                    }
                }
            });

            // Add event listener for "No thanks" button
            var noThanksBtn = $('#review_rate_now, #review_not_now, #review_rated_already, #review_not_now_feed_back');
            noThanksBtn.on('click', function (e) {
                e.preventDefault();
                $(this).prop('disabled', true);
                if($(this).attr('href')){
                    window.open($(this).attr('href'), '_blank');
                }
                $(this).closest('.wpvr-promo-notice').fadeOut();
                var nonce = $(this).closest('.wpvr-promo-notice').attr('data-nonce');
                let btn_id = $(this).attr("id");
                let show = true;
                let frequency = "";

                if ("review_rate_now" === btn_id || "review_rated_already" === btn_id) {
                    if ("review_rated_already" === btn_id) e.preventDefault();
                    show = false;
                    frequency = "never";
                } else if ("review_not_now" === btn_id || "review_not_now_feed_back" === btn_id) {
                    e.preventDefault();
                    show = false;
                    frequency = "one_week";
                }

                const payload = {
                    show: show,
                    frequency: frequency,
                };
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'wpvr_review_request',
                        payload: payload,
                        nonce: nonce
                    },
                    success: function (response) {
                        if(response.success){
                            $(this).closest('.wpvr-promo-notice').fadeOut();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle error
                        console.error('Error in AJAX request');
                    }
                });
            });
        });

    </script>
</div>

<style>
    #wpvr-admin-notice-five-star-review .wpvr-review-actions {
        display: flex;
        gap: 12px;
    }
    #wpvr-admin-notice-five-star-review .wpvr-review-step {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        padding: 0px 20px;
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    #wpvr-admin-notice-five-star-review {
        margin: 0 auto;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: auto;
    }
    .wpvr-promo-notice-dismiss-next-week{
        color: #7696b1!important;
    }

    @media screen and (max-width: 782px) {
        /* Your CSS styles for screens with a width of 768 pixels or more */
        /* For example: */
        #wpvr-admin-notice-five-star-review .wpvr-review-step.wpvr-review-step-3 .wpvr-review-actions {
            padding: 10px;
            gap: 12px;
            display:block
        }
    }

    @media screen and (max-width: 1480px) {
        /* Your CSS styles for screens with a width of 768 pixels or more */
        /* For example: */
        #wpvr-admin-notice-five-star-review {
            padding: 9px 41px;
        }
        #wpvr-admin-notice-five-star-review .wpvr-review-step.wpvr-review-step-3 .wpvr-review-actions  {
            display: block;
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
        }
        #review_rated_already{
            margin-top: 5px;
            margin-bottom: 5px;
        }


        #wpvr-admin-notice-five-star-review .wpvr-review-step {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            padding: 0px 23px;
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

    }
</style>
