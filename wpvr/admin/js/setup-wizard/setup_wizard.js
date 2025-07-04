(function ($) {
    "use strict";
    let wizard = "";
    const steps = rex_wpvr_wizard_translate_string;
    const popularIndustries = popular_industries;
    const modifiedPopularIndustries = Object.entries(popularIndustries).map(([key, value]) => ({key, value}));
    const { stepOne, stepTwo, stepThree, stepFour, stepFive } = steps;
    let industryName = '';
    let mediaResizer = false;
    let vrGlassSupport = false;
    let convertToWebP = false;
    let isWPVRActive = window?.wpvr_global_obj?.is_wpvr_active;
    let isToggleButtonChecked = true;
    let discountPrice = discount_information;

    const prevToggle = () => {
        wizard.previousStep();
    };

    const nextToggle = () => {
        wizard.nextStep();
    };

    /**
     * Initializes a wizard if the wizard container exists, using the provided configuration steps.
     *
     * @param {Object} stepOne - Configuration for the first step of the wizard.
     * @param {Object} stepTwo - Configuration for the second step of the wizard.
     * @param {Object} stepThree - Configuration for the third step of the wizard.
     * @param {Object} stepFour - Configuration for the fourth step of the wizard.
     * @since 8.4.9
     */
    if ($("#wizardContainer")?.length > 0) {
        wizard = rexWizard({
          general: {
            title: "Welcome to the Wizard",
            currentStep: 0,
            logo: logoUrl,
            targetElement: "wizardContainer",
            logoStyles: "setup-wizard__logo",
          },
          steps: [
            {
              stepText: `${stepOne?.step_text}`,
              html: `
            <section class="setup-wizard__welcome-section-container">
                <div class="setup-wizard__welcome-text-content">
                    <h1 class="setup-wizard__welcome-heading setup-wizard__heading-one">
                        ${stepOne?.welcome_section_heading}
                        <span class="setup-wizard__heading-one-highlight">${stepOne?.welcome_section_strong_heading[0]}</span>
                    </h1>
                    <p class="setup-wizard__welcome-description setup-wizard__description">
                        ${stepOne?.welcome_section_description}
                    </p>
                </div>
                    
                <!-- video container -->
                <div class="setup-wizard__welcome-video-container">
                <div id="video_play_button" class="setup-wizard__video-play-button">
                    <button aria-label="Play YouTube Video">
                        <svg width="100" height="70" viewBox="0 0 100 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                            d="M97.7467 10.9613C97.1734 8.84245 96.055 6.91079 94.5028 5.35872C92.9507 3.80666 91.0189 2.68836 88.9001 2.11523C81.1428 0 49.9254 0 49.9254 0C49.9254 0 18.7069 0.0641905 10.9508 2.17942C8.83206 2.75265 6.90045 3.87099 5.34839 5.42304C3.79634 6.9751 2.678 8.90671 2.10477 11.0255C-0.241546 24.8081 -1.15183 45.8081 2.16896 59.0387C2.74219 61.1575 3.86053 63.0891 5.41258 64.6412C6.96464 66.1932 8.89625 67.3115 11.015 67.8848C18.7717 70 49.9896 70 49.9896 70C49.9896 70 81.2076 70 88.9642 67.8848C91.083 67.3115 93.0146 66.1932 94.5667 64.6412C96.1187 63.0891 97.2371 61.1575 97.8103 59.0387C100.284 45.2372 101.047 24.2499 97.7461 10.9619"
                            fill="#FF0000" />
                            <path d="M39.9895 50.0004L65.887 35.0006L39.9895 20.0002V50.0004Z" fill="white" />
                        </svg>
                    </button>
                </div>
                <div id="setup_video" class="setup-wizard__welcome-video-iframe" style="display: none">
                    <iframe id="recommendation-video_set" title="Video"></iframe>
                </div>
                    <img id="recommendation-preview" class="setup-wizard__welcome-video-preview" loading="lazy"
                    src="${thumnailImage}" alt="preview image" />
                </div>

                <!-- setup wizard buttons -->
                <div class="setup-wizard__main-buttons">
                    <a href="${setup_wizard_admin_url}post-new.php?post_type=wpvr_item&wpvr-guide-tour=1" class="setup-wizard__button-left create-your-first-tour" id="create_first_tour">
                    ${stepOne?.welcome_section_button_text[0]}
                    </a>
                    <a href="https://rextheme.com/docs/wp-vr/" target="_blank" class="setup-wizard__button-right">
                    ${stepOne?.welcome_section_button_text[1]}
                    </a>
                </div>
            </section>
        <!-- features container -->
          <section class="setup-wizard__features-section-container">
            <div class="setup-wizard__features-text-content">
              <h1 class="setup-wizard__feature-heading setup-wizard__heading-one">
                <span class="setup-wizard__heading-one-highlight">${stepOne?.feature_section_strong_heading}</span>
              </h1>
              <p class="setup-wizard__feature-description setup-wizard__description">
              ${stepOne?.feature_section_description}
              </p>
            </div>

            <div class="setup-wizard__feature-lists">
              <div class="setup-wizard__single-feature">
                <div class="setup-wizard__feature-image">
                  <img src="${stepOne.feature_icon_one}" alt="explainer video" />
                </div>
                <div class="setup-wizard__feature-text-content">
                  <p class="setup-wizard__feature-heading">
                    ${stepOne.feature_explainer_video[0]}
                  </p>
                  <p class="setup-wizard__feature-description">
                  ${stepOne.feature_explainer_video[1]}
                  </p>
                </div>
              </div>
              <div class="setup-wizard__single-feature">
                <div class="setup-wizard__feature-image">
                  <img src="${stepOne.feature_icon_two}" alt="brand identity" />
                </div>
                <div class="setup-wizard__feature-text-content">
                  <p class="setup-wizard__feature-heading">
                  ${stepOne.feature_brand_identity[0]}
                  </p>
                  <p class="setup-wizard__feature-description">
                  ${stepOne.feature_brand_identity[1]}
                  </p>
                </div>
              </div>
              <div class="setup-wizard__single-feature">
                <div class="setup-wizard__feature-image">
                  <img src="${stepOne.feature_icon_three}" alt="lead generation form" />
                </div>
                <div class="setup-wizard__feature-text-content">
                  <p class="setup-wizard__feature-heading">
                  ${stepOne.feature_lead_forms[0]}
                  </p>
                  <p class="setup-wizard__feature-description">
                  ${stepOne.feature_lead_forms[1]}
                  </p>
                </div>
              </div>
              <div class="setup-wizard__single-feature">
                <div class="setup-wizard__feature-image">
                  <img src="${stepOne.feature_icon_four}" alt="explainer video" />
                </div>
                <div class="setup-wizard__feature-text-content">
                  <p class="setup-wizard__feature-heading">
                  ${stepOne.feature_password[0]}
                  </p>
                  <p class="setup-wizard__feature-description">
                  ${stepOne.feature_password[1]}
                  </p>
                </div>
              </div>
              <div class="setup-wizard__single-feature">
                <div class="setup-wizard__feature-image">
                  <img src="${stepOne.feature_icon_five}" alt="explainer video" />
                </div>
                <div class="setup-wizard__feature-text-content">
                  <p class="setup-wizard__feature-heading">
                  ${stepOne.feature_sharing[0]}
                  </p>
                  <p class="setup-wizard__feature-description">
                  ${stepOne.feature_sharing[1]}
                  </p>
                </div>
              </div>
            </div>

            <!-- setup wizard buttons -->
            <div class="setup-wizard__feature-list-button-container">
              <a href="https://rextheme.com/wpvr/360-virtual-tours/" target="_blank" class="setup-wizard__feature-list-button">
              ${stepOne?.feature_section_button_text[0]}
                <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M15.171 7.02277L1.47856 6.65834" stroke="#73707D" stroke-width="1.5" stroke-miterlimit="10"
                    stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M10.0348 11.8971L15.1709 7.008L10.2614 1.85151" stroke="#73707D" stroke-width="1.5"
                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </a>
            </div>

            <!--pro features -->
            <div class="setup-wizard__pro-features-section-container">
              <div class="setup-wizard__pro-features">
                <div class="setup-wizard__pro-features-text-content">
                  <h1 class="setup-wizard__pro-feature-heading">
                  ${stepOne?.pro_feature_section_heading}
                    <span class="setup-wizard__pro-feature-heading-highlight">${stepOne?.pro_feature_section_strong_heading}</span>
                  </h1>
                </div>
                <div class="setup-wizard__pro-feature-lists">
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_one}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_one}  
                    </p>
                  </div>
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_two}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_two}  
                    </p>
                  </div>
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_three}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_three}  
                    </p>
                  </div>
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_four}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_four}  
                    </p>
                  </div>
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_five}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_five}  
                    </p>
                  </div>
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_six}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_six}  
                    </p>
                  </div>
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_seven}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_seven}  
                    </p>
                  </div>
                  <div class="setup-wizard__single-pro-feature">
                    <img class="setup-wizard__single-pro-feature-icon" src="${stepOne.pro_feature_icon_eight}"
                      alt="pro-feature" />
                    <p class="setup-wizard__single-pro-feature-title">
                      ${stepOne.pro_feature_title_eight}  
                    </p>
                  </div>
                </div>
              </div>
              <div class="setup-wizard__pro-features-price">
                
                  <div class="pro-text">
                  <p>
                  Starting at
                  <span class="setup-wizard__pro-features-price-amount">${discountPrice?.discount_price}</span>/year
                  </p>
                  <p>Normally $79.99/year</p>
                  </div>
                <div class="setup-wizard__pro-features-price-tag">
                  ${discountPrice?.discount_percentage_text}
                </div>
              </div>
              <div class="setup-wizard__pro-feature-list-button-container">
                <a href="https://rextheme.com/wpvr/wpvr-pricing/" target="_blank"
                  class="setup-wizard__pro-feature-list-button">
                  ${stepOne.pro_feature_button_text}
                  <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.171 7.02277L1.47856 6.65834" stroke="#ffffff" stroke-width="1.5" stroke-miterlimit="10"
                      stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10.0348 11.8971L15.1709 7.008L10.2614 1.85151" stroke="#ffffff" stroke-width="1.5"
                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </a>
              </div>
            </div>
        </section>
          
            <!-- setup wizard buttons -->
        <section class="setup-wizard__footer-buttons">
            <a href="${setup_wizard_admin_url}post-new.php?post_type=wpvr_item&wpvr-guide-tour=1" class="setup-wizard__button-left create-your-first-tour" id="create_your_tour_second">
              ${stepOne.footer_section_button_text[0]}
            </a>
            <a href="#" class="setup-wizard__button-right next-step-button" id="setup_wizard_button_right"> ${stepOne.footer_section_button_text[1]} </a>
                </section>`,
              isNextStep: true,
              isPreviousStep: false,
              isSkip: false,
            },
            {
              stepText: `${stepTwo?.step_text}`,
              html: `
                <section class="setup-wizard__industry-section-container">
                    <div class="setup-wizard__industry-text-content">
                    <h1 class="setup-wizard__industry-heading setup-wizard__heading-one">
                        ${stepTwo.industry_section_heading}
                        <span class="setup-wizard__heading-one-highlight">${stepTwo.industry_section_strong_heading}</span>
                    </h1>
                    </div>
                    <div class="setup-wizard__popular-industries">
                    <div id="popular_industries" class="setup-wizard__popular-industry-list">
                        <div class="setup-wizard__single-industry handle-selected-industry" data-industry-name="real_estate">
                        <span class="setup-wizard__single-industry-image"><svg width="43" height="40" viewBox="0 0 43 40"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M35.5023 40.0001H7.16701C5.05319 40.0001 3.3334 38.2804 3.3334 36.1665V15.9968C3.3334 15.2604 3.93036 14.6633 4.66683 14.6633C5.4033 14.6633 6.00026 15.2604 6.00026 15.9968V36.1665C6.00026 36.8099 6.52363 37.3333 7.16701 37.3333H35.5023C36.1457 37.3333 36.6691 36.8099 36.6691 36.1665V15.9968C36.6691 15.2604 37.2661 14.6633 38.0025 14.6633C38.739 14.6633 39.336 15.2604 39.336 15.9968V36.1665C39.336 38.2803 37.6162 40.0001 35.5023 40.0001Z"
                                fill="#A8A1BD"></path>
                            <path
                                d="M41.3362 20.6632C40.995 20.6632 40.6538 20.533 40.3934 20.2726L23.8098 3.68918C22.4452 2.32441 20.2246 2.32441 18.8598 3.68918L2.27631 20.2727C1.7556 20.7935 0.911294 20.7935 0.39059 20.2727C-0.130197 19.752 -0.130197 18.9077 0.39059 18.387L16.9741 1.80338C19.3786 -0.601126 23.2911 -0.601126 25.6956 1.80338L42.2791 18.3869C42.7998 18.9077 42.7998 19.7519 42.2791 20.2726C42.0188 20.533 41.6775 20.6632 41.3362 20.6632Z"
                                fill="#A8A1BD"></path>
                            <path
                                d="M26.6688 40.0001H16.0014C15.2649 40.0001 14.668 39.403 14.668 38.6667V26.8325C14.668 24.5348 16.5373 22.6655 18.8349 22.6655H23.8353C26.1329 22.6655 28.0022 24.5348 28.0022 26.8325V38.6667C28.0022 39.403 27.4053 40.0001 26.6688 40.0001ZM17.3348 37.3332H25.3354V26.8325C25.3354 26.0053 24.6624 25.3324 23.8353 25.3324H18.8349C18.0078 25.3324 17.3348 26.0053 17.3348 26.8325V37.3332Z"
                                fill="#A8A1BD"></path>
                            </svg>
                        </span>
                        <h3 class="setup-wizard__single-industry-title">
                            ${popularIndustries.real_estate.name}
                        </h3>
                        </div>
                        <div class="setup-wizard__single-industry handle-selected-industry" data-industry-name="hotel">
                        <span class="setup-wizard__single-industry-image"><svg width="49" height="42" viewBox="0 0 49 42"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M44.5938 14.4911H35.8277C35.3043 14.4911 34.88 14.915 34.88 15.4379V39.1065H29.9046V29.284C29.9046 28.7612 29.4803 28.3373 28.9569 28.3373H20.4277C19.9043 28.3373 19.48 28.7612 19.48 29.284V39.1065H14.5046V4.78698C14.5046 3.74296 15.3549 2.89349 16.4 2.89349H32.9846C34.0297 2.89349 34.88 3.74296 34.88 4.78698V10.0533C34.88 10.5761 35.3043 11 35.8277 11C36.3511 11 36.7754 10.5761 36.7754 10.0533V4.78698C36.7754 2.69882 35.0749 1 32.9846 1H16.4C14.3097 1 12.6092 2.69882 12.6092 4.78698V39.1065H4.79077C3.7457 39.1065 2.89538 38.257 2.89538 37.213V18.2781C2.89538 17.2341 3.7457 16.3846 4.79077 16.3846H8.10769C8.63106 16.3846 9.05538 15.9607 9.05538 15.4379C9.05538 14.915 8.63106 14.4911 8.10769 14.4911H4.79077C2.70052 14.4911 1 16.1899 1 18.2781V37.213C1 39.3012 2.70052 41 4.79077 41H35.8277C36.3511 41 36.7754 40.5761 36.7754 40.0533V16.3846H44.5938C45.6389 16.3846 46.4892 17.2341 46.4892 18.2781V37.213C46.4892 38.257 45.6389 39.1065 44.5938 39.1065H40.5662C40.0428 39.1065 39.6185 39.5304 39.6185 40.0533C39.6185 40.5761 40.0428 41 40.5662 41H44.5938C46.6841 41 48.3846 39.3012 48.3846 37.213V18.2781C48.3846 16.1899 46.6841 14.4911 44.5938 14.4911ZM21.3754 30.2308H28.0092V39.1065H21.3754V30.2308ZM7.75231 23.0118C8.27567 23.0118 8.7 23.4357 8.7 23.9586V27.9822C8.7 28.5051 8.27567 28.929 7.75231 28.929C7.22894 28.929 6.80462 28.5051 6.80462 27.9822V23.9586C6.80462 23.4357 7.22894 23.0118 7.75231 23.0118ZM41.6323 28.929C41.1089 28.929 40.6846 28.5051 40.6846 27.9822V23.9586C40.6846 23.4357 41.1089 23.0118 41.6323 23.0118C42.1557 23.0118 42.58 23.4357 42.58 23.9586V27.9822C42.58 28.5051 42.1557 28.929 41.6323 28.929ZM30.1415 12.4793H19.2431C18.7197 12.4793 18.2954 12.0554 18.2954 11.5325C18.2954 11.0097 18.7197 10.5858 19.2431 10.5858H30.1415C30.6649 10.5858 31.0892 11.0097 31.0892 11.5325C31.0892 12.0554 30.6649 12.4793 30.1415 12.4793ZM28.4831 19.6982C29.0064 19.6982 29.4308 20.1221 29.4308 20.645C29.4308 21.1678 29.0064 21.5917 28.4831 21.5917H20.9015C20.3782 21.5917 19.9538 21.1678 19.9538 20.645C19.9538 20.1221 20.3782 19.6982 20.9015 19.6982H28.4831Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.5"></path>
                            </svg>
                        </span>
                        <h3 class="setup-wizard__single-industry-title">${popularIndustries.hotel.name}</h3>
                        </div>
                        <div class="setup-wizard__single-industry handle-selected-industry" data-industry-name="art_gallery">
                        <span class="setup-wizard__single-industry-image"><svg width="45" height="39" viewBox="0 0 45 39"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M22.7755 14.302C20.3531 14.302 18.3893 16.2657 18.3893 18.6882C18.3893 21.1106 20.353 23.0745 22.7755 23.0745C25.1979 23.0745 27.1618 21.1107 27.1618 18.6882C27.1618 16.2657 25.198 14.302 22.7755 14.302ZM22.7755 20.9857C21.5066 20.9857 20.4779 19.9571 20.4779 18.6881C20.4779 17.4192 21.5066 16.3906 22.7755 16.3906C24.0444 16.3906 25.0731 17.4192 25.0731 18.6881C25.0731 19.9571 24.0444 20.9857 22.7755 20.9857Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.2"></path>
                            <path
                                d="M40.2685 4.27637L11.7581 1.03901C10.6516 0.881691 9.53054 1.20472 8.67739 1.92673C7.82435 2.58818 7.27754 3.56865 7.16311 4.64197L6.64099 8.92378H5.02215C2.72459 8.92378 1.00141 10.9602 1.00141 13.2578V34.6144C0.943542 36.8053 2.67271 38.6284 4.86374 38.6863C4.91651 38.6877 4.96938 38.688 5.02215 38.6873H33.6892C35.9867 38.6873 38.0754 36.912 38.0754 34.6144V33.7789C38.7877 33.6413 39.4635 33.3568 40.0597 32.9435C40.9057 32.2312 41.4474 31.2224 41.5739 30.1238L43.976 8.92378C44.2208 6.62091 42.5682 4.54932 40.2685 4.27637ZM35.9867 34.6144C35.9867 35.7632 34.838 36.5986 33.6892 36.5986H5.02215C3.98441 36.6291 3.11848 35.8126 3.08799 34.7748C3.08639 34.7213 3.08709 34.6679 3.09009 34.6144V30.7504L11.1837 24.7977C12.156 24.0512 13.5255 24.1175 14.4212 24.9543L20.1129 29.9671C20.9772 30.6928 22.0652 31.0985 23.1937 31.1159C24.076 31.1267 24.9439 30.8917 25.7002 30.437L35.9868 24.4843V34.6144H35.9867ZM35.9867 22.0301L24.6034 28.6617C23.626 29.2408 22.3887 29.1359 21.5226 28.4006L15.7788 23.3355C14.1326 21.921 11.7265 21.8342 9.98269 23.1267L3.09009 28.1395V13.2578C3.09009 12.109 3.87337 11.0125 5.02215 11.0125H33.6892C34.9165 11.0633 35.9077 12.032 35.9867 13.2578V22.0301ZM41.8893 8.64183C41.8886 8.64872 41.888 8.65572 41.8872 8.66262L39.433 29.8626C39.4372 30.4123 39.1865 30.933 38.7542 31.2725C38.5453 31.4814 38.0753 31.5858 38.0753 31.6903V13.2578C37.9929 10.8789 36.0687 8.97765 33.6891 8.92378H8.72956L9.19951 4.85086C9.30146 4.32344 9.57721 3.8455 9.98279 3.49319C10.4407 3.17656 10.9944 3.02894 11.5494 3.07541L40.0076 6.36505C41.1558 6.47409 41.9983 7.49344 41.8893 8.64183Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.2"></path>
                            </svg>
                        </span>
                        <h3 class="setup-wizard__single-industry-title">
                        ${popularIndustries.art_gallery.name}
                        </h3>
                        </div>
                        <div class="setup-wizard__single-industry handle-selected-industry" data-industry-name="beauty_parlor">
                        <span class="setup-wizard__single-industry-image"><svg width="46" height="38" viewBox="0 0 46 38"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.44942 28.2084C6.94935 28.7532 4.67741 29.7427 2.8206 31.1174C2.56619 31.3028 2.42609 31.6046 2.44575 31.9246C2.46541 32.2447 2.64163 32.5269 2.915 32.6788C8.58247 35.8567 16.9284 35.5538 22.4816 31.6918V36.9313C22.4816 37.2182 22.7144 37.451 23.0013 37.451C23.2882 37.451 23.521 37.2182 23.521 36.9313V31.692C29.0783 35.5568 37.4292 35.8551 43.0802 32.6788C43.3589 32.5234 43.5356 32.2383 43.5531 31.9152C43.5704 31.5951 43.427 31.2954 43.1783 31.12C41.3172 29.742 39.0457 28.752 36.5487 28.2076C40.1326 26.1741 43.0727 23.3485 44.8666 20.1624C45.0385 19.864 45.0446 19.5023 44.8831 19.1937C44.7211 18.8845 44.4182 18.6838 44.08 18.6571C41.2266 18.3921 38.1884 18.8156 35.2107 19.8752C37.7118 15.4976 39.0247 10.6725 38.8913 6.19955C38.8913 6.19884 38.8913 6.19857 38.8913 6.19787C38.8788 5.82133 38.6774 5.47889 38.3524 5.2823C38.0289 5.08607 37.633 5.06614 37.2944 5.22988C33.7261 6.95748 30.3974 9.87101 27.7864 13.5055C27.4357 9.05454 26.106 4.85972 23.9372 1.50511C23.7329 1.18915 23.3834 1 23.0018 1C23.0014 1 23.0014 1 23.001 1C22.6197 1 22.2702 1.18844 22.0659 1.50414C19.8248 4.96307 18.5558 9.17878 18.215 13.5037C15.6094 9.87844 12.2811 6.96607 8.70742 5.22917C8.36737 5.06579 7.96985 5.08571 7.64406 5.28399C7.31863 5.48093 7.1169 5.82257 7.10441 6.19955C6.97096 10.6661 8.28661 15.4915 10.7919 19.8753C7.81355 18.816 4.77606 18.3932 1.92957 18.6564C1.58749 18.6835 1.28534 18.882 1.12196 19.1879C0.957511 19.4951 0.959194 19.8578 1.12807 20.1603C2.92811 23.3475 5.86979 26.1743 9.44942 28.2084ZM3.57305 31.8549C5.59394 30.3971 8.12296 29.4091 10.9088 28.9896C14.033 30.504 17.6842 31.4193 21.1448 31.3329C16.1142 34.3881 8.75878 34.6767 3.57305 31.8549ZM24.8567 31.3322C28.3708 31.4244 32.0082 30.4836 35.0934 28.9896C37.8697 29.4077 40.3937 30.3941 42.4228 31.8549C37.3143 34.6431 29.9531 34.4281 24.8567 31.3322ZM43.9483 19.6753C40.4618 25.8442 32.5272 30.4408 24.9599 30.2826C28.8006 28.1803 32.0233 24.9101 34.3466 21.3208C37.5602 19.9723 40.8821 19.407 43.9483 19.6753ZM37.8142 6.17165C37.8308 6.18184 37.8511 6.20043 37.8525 6.23187C38.123 15.3112 32.267 25.0839 24.4557 29.3674C26.6625 25.5875 28.009 20.4946 27.88 15.1933C30.4863 11.1895 33.986 7.9871 37.8142 6.17165ZM22.938 2.06929C22.9539 2.04459 22.9809 2.03954 23.001 2.03954C23.0209 2.03954 23.0483 2.04459 23.0642 2.06965C28.0394 9.76492 28.207 21.7643 22.9916 29.7229C17.938 21.9919 17.8253 9.96045 22.938 2.06929ZM8.18221 6.17298C8.19239 6.16687 8.2093 6.15908 8.22825 6.15908C8.23676 6.15908 8.24588 6.16076 8.255 6.16519C12.0233 7.99658 15.5225 11.1993 18.1229 15.1936C18.0169 19.9905 19.1055 25.1838 21.5468 29.3674C13.5431 24.9786 7.87838 15.096 8.14298 6.23249C8.14395 6.20238 8.16494 6.18379 8.18221 6.17298ZM11.6558 21.3206C14.0187 24.9727 17.2579 28.2111 21.0431 30.2827C13.4243 30.4479 5.54453 25.8041 2.01839 19.6918C5.25691 19.3923 8.59699 20.0368 11.6558 21.3206Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.7"></path>
                            </svg>
                        </span>
                        <h3 class="setup-wizard__single-industry-title">
                        ${popularIndustries.beauty_parlor.name}
                        </h3>
                        </div>
                        <div class="setup-wizard__single-industry handle-selected-industry" data-industry-name="car_showroom">
                        <span class="setup-wizard__single-industry-image"><svg width="44" height="35" viewBox="0 0 44 35"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M39.8368 14.5593L36.833 3.74622C36.612 2.95748 36.1395 2.26247 35.4874 1.76679C34.8352 1.27112 34.0391 1.00188 33.22 1H10.78C9.96088 1.00188 9.16476 1.27111 8.51262 1.76678C7.86049 2.26246 7.38801 2.95748 7.16697 3.74622L4.16772 14.545C3.2854 14.6851 2.48187 15.135 1.90132 15.814C1.32077 16.493 1.00122 17.3566 1 18.25V28C1.00045 28.3977 1.15864 28.779 1.43984 29.0602C1.72105 29.3414 2.10231 29.4995 2.5 29.5H3.25V32.5C3.25065 33.0965 3.48791 33.6685 3.90973 34.0903C4.33154 34.5121 4.90346 34.7493 5.5 34.75H8.5C9.09654 34.7493 9.66846 34.5121 10.0903 34.0903C10.5121 33.6685 10.7493 33.0965 10.75 32.5V29.5H33.25V32.5C33.2507 33.0965 33.4879 33.6685 33.9097 34.0903C34.3315 34.5121 34.9035 34.7493 35.5 34.75H38.5C39.0965 34.7493 39.6685 34.5121 40.0903 34.0903C40.5121 33.6685 40.7493 33.0965 40.75 32.5V29.5H41.5C41.8977 29.4995 42.279 29.3414 42.5602 29.0602C42.8414 28.779 42.9995 28.3977 43 28V18.25C42.9976 17.3592 42.6779 16.4984 42.0982 15.822C41.5185 15.1456 40.7168 14.698 39.8368 14.5593ZM2.5 21.25H6.25C6.84654 21.2507 7.41846 21.4879 7.84027 21.9097C8.26209 22.3315 8.49935 22.9035 8.5 23.5H2.5V21.25ZM9.25 32.5C9.2498 32.6989 9.17072 32.8895 9.03011 33.0301C8.8895 33.1707 8.69885 33.2498 8.5 33.25H5.5C5.30115 33.2498 5.1105 33.1707 4.96989 33.0301C4.82928 32.8895 4.7502 32.6989 4.75 32.5V29.5H9.25V32.5ZM15.25 28H2.5V25H15.25V28ZM27.25 28H16.75V25H27.25V28ZM41.5 28H40C39.9015 28 39.804 28.0194 39.713 28.057C39.622 28.0947 39.5393 28.15 39.4696 28.2196C39.4 28.2893 39.3447 28.372 39.307 28.463C39.2694 28.554 39.25 28.6515 39.25 28.75V32.5C39.2498 32.6989 39.1707 32.8895 39.0301 33.0301C38.8895 33.1707 38.6989 33.2498 38.5 33.25H35.5C35.3011 33.2498 35.1105 33.1707 34.9699 33.0301C34.8293 32.8895 34.7502 32.6989 34.75 32.5V29.5H37C37.1989 29.5 37.3897 29.421 37.5303 29.2803C37.671 29.1397 37.75 28.9489 37.75 28.75C37.75 28.5511 37.671 28.3603 37.5303 28.2197C37.3897 28.079 37.1989 28 37 28H28.75V25H41.5V28ZM41.5 23.5H35.5C35.5007 22.9035 35.7379 22.3315 36.1597 21.9097C36.5815 21.4879 37.1535 21.2507 37.75 21.25H41.5V23.5ZM41.5 19.75H37.75C36.7558 19.7512 35.8026 20.1466 35.0996 20.8496C34.3966 21.5526 34.0012 22.5058 34 23.5H10C9.99883 22.5058 9.60337 21.5526 8.90036 20.8496C8.19735 20.1466 7.2442 19.7512 6.25 19.75H2.5V18.25C2.50065 17.6535 2.73791 17.0815 3.15973 16.6597C3.58154 16.2379 4.15346 16.0007 4.75 16C4.91398 16.0001 5.07345 15.9464 5.20402 15.8472C5.33458 15.7479 5.42903 15.6087 5.4729 15.4507L8.61279 4.14759C8.74518 3.67434 9.02853 3.25728 9.41973 2.95987C9.81094 2.66247 10.2886 2.50099 10.78 2.5H33.22C33.7114 2.50098 34.1891 2.66246 34.5803 2.95987C34.9715 3.25727 35.2549 3.67433 35.3872 4.14759L38.263 14.5H7.75C7.55109 14.5 7.36032 14.579 7.21967 14.7197C7.07902 14.8603 7 15.0511 7 15.25C7 15.4489 7.07902 15.6397 7.21967 15.7803C7.36032 15.921 7.55109 16 7.75 16H39.25C39.8465 16.0007 40.4185 16.2379 40.8403 16.6597C41.2621 17.0815 41.4993 17.6535 41.5 18.25V19.75Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.5"></path>
                            </svg>
                        </span>
                        <h3 class="setup-wizard__single-industry-title">
                             ${popularIndustries.car_showroom.name}
                        </h3>
                        </div>
                        <div class="setup-wizard__single-industry handle-selected-industry" data-industry-name="pinterest">
                        <span class="setup-wizard__single-industry-image"><svg width="40" height="42" viewBox="0 0 40 42"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M31.0631 17.6957C31.5196 17.6656 31.9228 18.0033 31.9587 18.4619L32.7847 29.0556C32.8205 29.5142 32.4775 29.9154 32.0185 29.9512C31.9965 29.9528 31.9745 29.9536 31.953 29.9536C31.5221 29.9536 31.1571 29.622 31.1229 29.185L30.2969 18.5913C30.2611 18.1327 30.6041 17.7315 31.0631 17.6957Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.4"></path>
                            <path
                                d="M25.0471 17.6931C25.5073 17.6931 25.8805 18.0662 25.8805 18.5264V29.1201C25.8805 29.5803 25.5073 29.9535 25.0471 29.9535C24.5869 29.9535 24.2138 29.5803 24.2138 29.1201V18.5264C24.2138 18.0662 24.5869 17.6931 25.0471 17.6931Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.4"></path>
                            <path
                                d="M19.0317 17.6953C19.4907 17.7311 19.8337 18.1323 19.7979 18.5909L18.9719 29.1846C18.9377 29.6216 18.5727 29.9532 18.1418 29.9532C18.1202 29.9532 18.0983 29.9524 18.0763 29.9508C17.6173 29.915 17.2743 29.5138 17.3101 29.0552L18.1361 18.4615C18.1723 18.0025 18.5776 17.6567 19.0317 17.6953Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.4"></path>
                            <path
                                d="M1.02444 21.7906C1.02444 15.0456 6.32046 9.514 12.9771 9.09448L13.0418 8.23094C12.7727 8.10678 12.5763 7.8487 12.5556 7.53483C12.5467 7.40096 12.5381 7.24959 12.5381 7.10718C12.5381 5.95199 13.0882 3.42716 14.4009 2.55273C15.714 1.64046 17.4921 1.56559 18.841 2.29313C19.4501 1.87606 20.1704 1.65145 20.9252 1.65145C21.413 1.65145 21.894 1.74707 22.294 1.91878C22.7835 2.11247 23.2217 2.4091 23.5814 2.78507C24.3785 2.21867 25.3343 1.89437 26.33 1.91512C27.132 1.32308 28.1082 1 29.1149 1C30.6243 1 32.025 1.74336 32.7912 2.77246L32.8661 2.86971C35.2355 2.01286 37.5637 3.52519 37.5637 7.20036C37.5637 7.31592 37.5552 7.43148 37.5454 7.54704C37.5196 7.85847 37.3217 8.1138 37.0526 8.2349L38.9822 33.9765C39.1165 35.7831 38.4862 37.5808 37.2537 38.9085C36.0196 40.2379 34.2711 41 32.4571 41H17.637C15.823 41 14.0746 40.2379 12.8404 38.9085C11.6689 37.6465 11.0499 35.9594 11.1057 34.2468C5.31003 33.0023 1.02444 27.7839 1.02444 21.7906ZM12.5783 14.4129C8.98505 14.9839 6.28771 18.0585 6.28771 21.7906C6.28771 25.0672 8.42923 27.9174 11.4926 28.8957L12.5783 14.4129ZM33.0476 4.6381C32.8408 4.78499 32.5784 4.82935 32.3351 4.75895C32.0917 4.68896 31.8932 4.51156 31.7959 4.27759C31.7764 4.22998 31.7499 4.19173 31.7027 4.11564C31.6194 4.04566 30.9273 2.66667 29.1149 2.66667C28.3906 2.66667 27.6911 2.92546 27.1459 3.39543C26.9778 3.53988 26.7622 3.60986 26.5379 3.59481C25.6114 3.52645 24.6894 3.88411 24.0615 4.5734C23.8784 4.77482 23.6054 4.87288 23.3377 4.8387C23.0675 4.8033 22.8315 4.6381 22.7062 4.3964C22.4856 3.97241 22.1219 3.64282 21.6592 3.45972C21.4431 3.36694 21.1892 3.31812 20.9252 3.31812C20.3828 3.31812 19.8729 3.52889 19.4904 3.91138C19.195 4.20679 18.7246 4.23812 18.393 3.98258C17.543 3.32829 16.2861 3.27214 15.3384 3.9305C14.8772 4.23797 14.3596 5.56075 14.2335 6.64453H18.6282C19.8286 6.64453 20.8051 7.62109 20.8051 8.82145V14.3105C20.8051 15.5567 22.5548 15.6152 22.5548 14.409V8.82145C22.5548 7.62109 23.5314 6.64453 24.7317 6.64453H35.872C35.6498 4.21317 34.0663 3.91569 33.0476 4.6381ZM14.062 37.7745C14.9954 38.7795 16.2649 39.3333 17.637 39.3333H32.4571C33.8292 39.3333 35.0987 38.7795 36.0322 37.7745C36.9644 36.7707 37.4217 35.4657 37.3204 34.1006L35.3873 8.31283H24.7329C24.4575 8.31283 24.2247 8.54557 24.2247 8.82104V14.4049C24.2247 17.7666 19.138 17.7932 19.138 14.3195V8.82104C19.138 8.54557 18.9053 8.31283 18.6294 8.31283H14.7068C14.7005 8.39655 12.6641 35.4818 12.7737 34.1002C12.6724 35.4657 13.1297 36.7707 14.062 37.7745ZM11.2145 32.5751L11.3618 30.6016C7.41442 29.5266 4.62104 25.9333 4.62104 21.7906C4.62104 17.0905 8.11591 13.2347 12.7062 12.7065L12.8522 10.7587C7.17231 11.2332 2.69111 16.0049 2.69111 21.7906C2.69111 26.9192 6.29666 31.4049 11.2145 32.5751Z"
                                fill="#A8A1BD" stroke="#A8A1BD" stroke-width="0.4"></path>
                            </svg>
                        </span>
                        <h3 class="setup-wizard__single-industry-title">${popularIndustries.pinterest.name}</h3>
                        </div>
                    </div>
                    </div>
                </section>

            <!-- general settings section container -->
            <section class="setup-wizard__settings-section-container">
                <div class="setup-wizard__settings-text-content">
                <h1 class="setup-wizard__settings-heading setup-wizard__heading-one">
                    <span class="setup-wizard__heading-one-highlight">${stepTwo.general_section_strong_heading}</span>
                </h1>
                </div>

                <div class="setup-wizard__settings-content">
                <div class="setup-wizard__settings-single-content">
                    <input type="checkbox" id="media_resizer" />
                    <label for="media_resizer">Enable mobile media resizer</label>
                </div>

                <div class="setup-wizard__settings-single-content">
                    ${
                        !isWPVRActive ? '<div class="setup-wizard__settings-single-content--pro-content">\n                    pro\n                    </div>': ''
                    }
                    <input type="checkbox" id="convert_to_webp" ${!isWPVRActive ? 'disabled' : ''} />
                    <label for="convert_to_webp">Convert any jpeg or png format image to webp on media
                    upload</label>
                </div>

                <div class="setup-wizard__settings-single-content">
                    ${
                         !isWPVRActive ? '<div class="setup-wizard__settings-single-content--pro-content">\n                    pro\n                    </div>': ''
                    }
                    
                    <input type="checkbox" id="vr_glass_support" ${!isWPVRActive ? 'disabled' : ''} />
                    <label for="vr_glass_support">VR Glass Support</label>
                </div>
                </div>
            </section>

            <!-- setup wizard buttons -->
            <section class="setup-wizard__footer-buttons">
                <a href="${setup_wizard_admin_url}post-new.php?post_type=wpvr_item&wpvr-guide-tour=1" class="setup-wizard__button-left create-your-first-tour">
                ${stepTwo.footer_section_button_text[0]}
                </a>
                <a href="#" class="setup-wizard__button-right next-step-button" id="second_step_next_btn"> ${stepTwo.footer_section_button_text[1]} </a>
            </section>

                  `,
              isNextStep: true,
              isPreviousStep: true,
              isSkip: true,
            },
            {
              stepText: `${stepThree?.step_text}`,
              html: `
        <!-- done section container -->
          <section class="setup-wizard__done-section-container">
            <!-- text content -->
            <div class="setup-wizard__done-text-content setup-wizard__done-text-content--done-icon">
              <img src="${stepThree.done_icon}" alt="done" />
              <h1 class="setup-wizard__done-heading setup-wizard__heading-one">
                You Are
                <span class="setup-wizard__heading-one-highlight">Done</span>
              </h1>
            </div>

            <!-- testimonial container -->
            <div class="setup-wizard__testimonial">
              <h2 class="setup-wizard__testimonial-title">Testimonials</h2>
              <div class="setup-wizard__testimonial-card">
                <div class="setup-wizard__testimonial-single-card">
                  <p class="setup-wizard__testimonial-text-content">
                    ${stepThree.testimonial_one[0]}
                  </p>
                  <p class="setup-wizard__testimonial-text-author">
                  ${stepThree.testimonial_one[1]}
                  </p>
                  <img class="setup-wizard__testimonial-quote-icon" src="${stepThree.quote_icon}" alt="Quote" />
                </div>
                <div class="setup-wizard__testimonial-single-card">
                  <p class="setup-wizard__testimonial-text-content">
                  ${stepThree.testimonial_two[0]}
                  </p>
                  <p class="setup-wizard__testimonial-text-author">
                  ${stepThree.testimonial_two[1]}
                  </p>
                  <img class="setup-wizard__testimonial-quote-icon" src="${stepThree.quote_icon}" alt="Quote" />
                </div>
              </div>
            </div>

            <!-- subscribe button -->
            <div class="setup-wizard__subscribe-button-container">
              <!-- switcher -->
              <label class="setup-wizard__switch">
                <input id="wpvr-opt-in-toggle-button" type="checkbox" ${isToggleButtonChecked ? "checked": ''} />
                <span class="setup-wizard__switch-slider setup-wizard__switch-round"></span>
              </label>
              <p>
                Opt-in to receive tips, discounts, and recommendations from
                the RexTheme team directly in your inbox.
              </p>
            </div>
          </section>

        <!-- setup wizard buttons -->
          <section class="setup-wizard__footer-buttons">
            <a href="${setup_wizard_admin_url}post-new.php?post_type=wpvr_item&wpvr-guide-tour=1" class="setup-wizard__button-left create-your-first-tour last-step">
            ${stepThree.footer_section_button_text[0]}
            </a>
            <a href="https://rextheme.com/wpvr/wpvr-pricing/" target="_blank" class="setup-wizard__button-right" id="wpvr-upgrade-to-pro">
            ${stepThree.footer_section_button_text[1]}
            </a>
          </section>
                  `,
              isNextStep: true,
              isPreviousStep: true,
              isSkip: false,
            },
          ],
        });
    }

    /**
     * Navigation bar onclick event
     *
     * since 7.4.14
     */
    $( document ).on('click', '.setup-wizard__pregress-step', function () {
        if ( !$( this ).hasClass( 'step-active' ) ) {
            if ( $( this ).next().hasClass( 'step-active' ) ) {
                prevToggle();
            }
            else if ( $( this ).prev().hasClass( 'step-active' ) ) {
                nextToggle();
            }
        }
    });

    /**
     * Adds event listeners to handle clicks on navigation buttons within the wizard.
     *
     * @since 8.4.10
     */
    $(document).on("click", ".next-step-button", () => {
        nextToggle();
    });

    /**
     * Handles click events on the "Skip" button, redirecting to the license manager page.
     *
     * @since 8.4.10
     */
    $(document).on("click", "#skip-setup-wizard", () => {
        window.location =
            "post-new.php?post_type=wpvr_item";
        return false;
    });

    /**
     * Handles the change event for the wpvr-opt-in-toggle-button.
     * If the checkbox is checked, it triggers the createContact function.
     *
     * @since 8.4.10
     */
    $(document).on("change", "#wpvr-opt-in-toggle-button", function () {
        isToggleButtonChecked = !isToggleButtonChecked;
    });

    /**
     * Sends an AJAX request to create a contact using the provided user information.
     * The function retrieves the user's name and email from the global `wpvr_global_obj` object,
     * and sends a POST request to the server with this information.
     * If the request is successful, it displays a success message.
     * If the request fails, it displays an error message.
     *
     * @since 8.4.10
     */
    function createContact(){
        let name = window?.wpvr_global_obj?.user_information?.name;
        let email = window?.wpvr_global_obj?.user_information?.email;
        let url = window?.wpvr_global_obj?.ajaxurl;
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                action: 'wpvr_create_contact',
                email: email,
                name: name,
                industry: industryName,
                security: window.wpvr_global_obj.ajax_nonce
            },
            success: function(response){
            },
            error: function(jqXHR){
            }
        });
    }

    /**
     * Handles the click event on elements with the class 'handle-selected-industry'.
     * When an element is clicked, it retrieves the industry name from the data attribute
     * and assigns it to the global variable `industryName`.
     *
     * @since 8.4.10
     */
    $(document).on('click', '.handle-selected-industry', function(){
        industryName = $(this).data('industry-name');
        $('.handle-selected-industry').removeClass('active');
        $(this).addClass('active');

    });

    /**
     * Handles the click event on elements with the class 'setup-wizard__done-buttons-progress'.
     * When an element is clicked, it redirects the user to the page for creating a new WPVR item
     * with the guide tour parameter set to 1.
     *
     * @since 8.4.10
     */
    $(document).on('click', '.setup-wizard__done-buttons-progress', function(){
        window.location = 'post-new.php?post_type=wpvr_item&wpvr-guide-tour=1';
    });

    /**
     * Handles the click event on elements with the class 'setup-wizard__done-buttons-progress'.
     * When an element is clicked, it redirects the user to the page for creating a new WPVR item
     * with the guide tour parameter set to 1.
     *
     * @since 8.4.10
     */
    $(document).on('click', '.setup-wizard__done-buttons-progress', function(){
        window.location = 'post-new.php?post_type=wpvr_item&wpvr-guide-tour=1';
    });

    /**
     * Displays a list of popular industries by fetching their logos and names,
     * and appending them to the container element with the ID 'popular_industries'.
     *
     * @since 8.4.10
     */
    function displayIndustries() {
        const container = $('#popular_industries');

        function fetchAndCreateIndustryElement(industry) {
            const industryDiv = $('<div>', {
                class: 'rex-wpvr-setup-wizard-single-builder handle-selected-industry',
                'data-industry-name': industry.key
            });

            fetch(industry.value.logo_url)
                .then(response => response.text())
                .then(svgContent => {
                    const span = $(`<span class="rex-wpvr-setup-builder-image">${svgContent}</span>`);
                    industryDiv.append(span);
                    appendHeading();
                })
                .catch(error => {
                    console.error('Error fetching the SVG:', error);
                });
    
            function appendHeading() {
                const heading = $('<h3>', {
                    class: 'rex-wpvr-builder-heading',
                    text: industry.value.name
                });
                industryDiv.append(heading);
            }
    
            return industryDiv;
        }
    
        const fetchPromises = [];

        modifiedPopularIndustries.forEach(industry => {
            const promise = fetchAndCreateIndustryElement(industry);
            fetchPromises.push(promise);
        });

        Promise.all(fetchPromises)
            .then(industryDivs => {
                industryDivs.forEach(div => {
                    container.append(div);
                });
            })
            .catch(error => console.error('Error fetching SVGs:', error));
    }

    /**
     * Handles the click event on the element with the ID 'second_step_next_btn'.
     * When the element is clicked, it prevents the default action, retrieves the state of various checkboxes,
     * and sends an AJAX POST request to save the general settings.
     * On success, it updates the button text, moves to the next step, and checks the opt-in toggle.
     * On error, it resets the button text.
     *
     * @param {Event} e - The click event.
     * @since 8.4.10
     */
        // Step 1: Capture the state before the DOM element is removed
    let mediaResizerState = false;
    let convertToWebPState = false;
    let vrGlassSupportState = false;

    $(document).on('change', '#media_resizer', function() {
        mediaResizerState = $(this).is(':checked');
    });

    $(document).on('change', '#convert_to_webp', function() {
        convertToWebPState = $(this).is(':checked');
    });

    $(document).on('change', '#vr_glass_support', function() {
        vrGlassSupportState = $(this).is(':checked');
    });

    // Step 2: Use the stored state when the button is clicked
    $(document).on('click', "#second_step_next_btn", function(e) {
        e.preventDefault();
        // Use the stored state
        mediaResizer = mediaResizerState;
        convertToWebP = convertToWebPState;
        vrGlassSupport = vrGlassSupportState;

        let url = window.wpvr_global_obj.ajaxurl;
        $("#rex-wpvr-get-general-settings-button").text('Next...');
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                action: 'wpvr_save_general_settings',
                media_resizer: mediaResizer,
                convert_to_webp: convertToWebP,
                vr_glass_support: vrGlassSupport,
                security: window.wpvr_global_obj.ajax_nonce
            },
            success: function(response) {
                $("#rex-wpvr-get-general-settings-button").text('Next');
                nextToggle();
            },
            error: function(error) {
                $("#rex-wpvr-get-general-settings-button").text('Next');
                return;
            }
        });
    });

    /**
     * Handles the click event on elements with the class 'setup-wizard__button-left'.
     * When an element is clicked, it prevents the default action, constructs a URL for creating a new WPVR item,
     * sets the href attribute of the clicked element to this URL, and redirects the browser to the URL.
     *
     * @param {Event} e - The click event.
     * @since 8.4.10
     */
    $(document).on('click', '.create-your-first-tour', function (e){
        e.preventDefault();
        alert('hello');
        // const domain = window.location.origin;
        const url = `${setup_wizard_admin_url}post-new.php?post_type=wpvr_item&wpvr-guide-tour=1`;
        $(this).attr('href', url);
        if($('#wpvr-opt-in-toggle-button').is(':checked') && $( this ).hasClass( 'last-step' ) ) {
            createContact();
        }
        window.location.href = url;
    });

    /**
     * Handles the click event on the element with the ID 'wpvr-upgrade-to-pro'.
     * When the element is clicked, it checks if the opt-in toggle button is checked.
     * If the toggle button is checked, it triggers the createContact function.
     *
     * @param {Event} e - The click event.
     * @since 8.4.10
     */
    $(document).on('click', '#wpvr-upgrade-to-pro', function(e){
        if(isToggleButtonChecked) {
            createContact();
        }
    });

    // play YouTube video
    document.getElementById("video_play_button").addEventListener("click", () => {
        const yt_video = "https://www.youtube.com/embed/SWsv-bplne8?autoplay=1";

        // Show the video iframe
        document.getElementById("setup_video").style.display = "block";

        document.getElementById(
            "setup_video"
        ).innerHTML = `<iframe id="recommendation-video_set" title="Video" src="${yt_video}" allow="autoplay"></iframe>`;

        // Hide the preview image and play button
        document.getElementById("recommendation-preview").style.display = "none";
        document.getElementById("video_play_button").style.display = "none";
    });
})(jQuery);