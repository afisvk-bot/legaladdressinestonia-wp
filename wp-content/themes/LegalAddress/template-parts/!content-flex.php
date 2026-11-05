<?php
/**
 * Вывод Flexible Content поля "content_blocks".
 * Вставьте этот код в page.php / template-part или в тот файл,
 * где должны отображаться блоки страницы.
 */

 // Modal Forsm & BTNs
    $localized_form = get_localized_cf7_form();
    $form_shortcode = $localized_form['shortcode'];
    $form_id_class  = $localized_form['modal_class'];

    // ===== Данные для Service Schema (schema.org) =====
    // serviceType берём из ACF-поля страницы; если поле не заполнено — берём заголовок страницы
    $schema_service_type = get_field( 'seo_service_type' );
    if ( ! $schema_service_type ) {
        $schema_service_type = get_the_title();
    }

    // url — реальный адрес текущей страницы (корректно работает и с Polylang)
    $schema_service_url = get_permalink();

    // Данные о компании-поставщике услуги — рекомендуется вынести в ACF Options Page,
    // чтобы не дублировать на каждой странице услуг
    $schema_provider_name     = get_field( 'company_name', 'option' ) ?: 'eBusiness Solutions OÜ';
    $schema_area_served_name  = get_field( 'company_country', 'option' ) ?: 'Estonia';
    $schema_area_served_code  = get_field( 'company_country_code', 'option' ) ?: 'EE';
    $schema_provider_url      = get_field( 'company_url', 'option' ) ?: home_url( '/services/' );

    // Сюда во время обхода блока "tariffs" сложим все числовые цены пакетов,
    // чтобы затем взять реальный МИНИМУМ, а не хардкодить цифру
    $schema_tariff_prices = array();

if ( have_rows( 'content_blocks' ) ) :

    while ( have_rows( 'content_blocks' ) ) : the_row();

        // ===== Layout: CTA =====
        if ( get_row_layout() === 'cta' ) :

            $anchor          = get_sub_field( 'anchor');
            $button_styles   = get_sub_field( 'button_styles');
            ?>
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-11 col-lg-8 col-xl-8 mx-lg-auto block-cta">
                        <a href="javascript:void(0);"
                           data-toggle="modal"
                           data-target=".form_<?php echo esc_attr( $form_id_class ); ?>"
                           class="btn <?php echo esc_attr( $button_styles ); ?>">
                            <?php echo esc_html( $anchor ); ?>
                        </a>
                    </div>
                </div>
            <?php


        // ===== Layout: Text =====
        elseif ( get_row_layout() === 'text_block' ) :

            $content = get_sub_field( 'content' );
            ?>
            <div class="row">
                <div class="col-12 col-sm-12 col-md-11 col-lg-8 col-xl-8 mx-lg-auto block-text">
                    <?php echo $content; ?>
                </div>
            </div>
            <?php


        // ===== Layout: Image + Text =====
        elseif ( get_row_layout() === 'img_text' ) :

            $image_left  = get_sub_field( 'image_left' );
            $title_right = get_sub_field( 'title_right' );
            $text_right  = get_sub_field( 'text_right' );
            ?>

            <div class="row align-items-center justify-content-center service-img-text">
                <div class="col-md-12 col-lg-4 text-center">
                  <img width="320" height="320" src="<?php echo $image_left; ?>" class="lazyload" alt="<?php echo $title_right; ?>">
                </div>
                <div class="col-md-12 col-lg-7 px-3 px-sm-3 px-md-4 mt-4 mt-lg-0">
                  <h2 class="mt-0 mb-4"><?php echo $title_right; ?></h2>
                  <?php echo $text_right; ?>
                </div>
            </div>
            <?php


        // ===== Layout: Text + Image =====
        elseif ( get_row_layout() === 'text_img' ) :

            $title_left  = get_sub_field( 'title_left' );
            $text_left   = get_sub_field( 'text_left' );
            $image_right = get_sub_field( 'image_right' );
            ?>

            <div class="row align-items-center justify-content-center service-img-text">
                <div class="col-md-12 col-lg-4 text-center order-1 order-lg-2">
                    <img width="320" height="320" src="<?php echo $image_right; ?>" class="lazyload" alt="<?php echo $title_left; ?>">
                </div>
                <div class="col-md-12 col-lg-7 order-2 order-md-1 px-3 px-sm-3 px-md-4 mt-4 mt-lg-0">
                    <h2 class="mt-0 mb-4"><?php echo $title_left; ?></h2>
                    <?php echo $text_left; ?>
                </div>
            </div>
            <?php


        // ===== Layout: Provider & Logos =====
        elseif ( get_row_layout() === 'provider' ) :

            $provider_title = get_sub_field( 'provider_title' );
            $provider_text  = get_sub_field( 'provider_text' );
            $provider_logos = get_sub_field( 'provider_logos' );
            ?>

            <div class="row align-items-center justify-content-center provider">
                <div class="col-12 col-sm-12 col-md-10 col-lg-9 col-xl-9 text-center">
                    <h2 class="mt-0 mb-4"><?php echo $provider_title; ?></h2>
                    <div class="txt-grey"><?php echo $provider_text; ?></div>
                </div>

                <div class="col-12 col-sm-12 col-md-10 col-lg-11 col-xl-9 my-5">
                    <div class="ser-logos">
                        <?php echo $provider_logos; ?>
                    </div>
                </div>
            </div>
            <?php


        // ===== Layout: FINAL CTA =====
        elseif ( get_row_layout() === 'final_cta' ) :

            $final_cta_title = get_sub_field( 'final_cta_title' );
            $final_cta_text  = get_sub_field( 'final_cta_text' );
            $final_cta_ancor = get_sub_field( 'final_cta_ancor' );
            $final_cta_image = get_sub_field( 'final_cta_image' );
            ?>

            <div class="row align-items-center justify-content-center service-final-cta">
                <div class="col-md-5 col-lg-6">
                    <h2 class="mt-0 mb-4"><?php echo $final_cta_title; ?></h2>
                    
                    <p class="txt-grey mb-5"><?php echo $final_cta_text; ?></p>

                    <a href="javascript:void(0);"
                       data-toggle="modal"
                       data-target=".form_<?php echo esc_attr( $form_id_class ); ?>"
                       class="btn <?php echo esc_attr( $button_styles ); ?>">
                        <?php echo esc_html( $final_cta_ancor ); ?>
                    </a>
                </div>
                <div class="col-md-7 col-lg-6 text-center">
                    <img width="493" height="637" src="<?php echo $final_cta_image; ?>" class="lazyload" alt="<?php echo $final_cta_title; ?>">
                </div>
            </div>
            <?php


        // ===== Layout: Steps =====
        elseif ( get_row_layout() === 'steps' ) :

            $steps_title        = get_sub_field( 'steps_title' );
            $step_1             = get_sub_field( 'step_1' );
            $step_2             = get_sub_field( 'step_2' );
            $step_3             = get_sub_field( 'step_3' );
            $step_4             = get_sub_field( 'step_4' );
            $step_description   = get_sub_field( 'step_description' );
            $form_id            = get_sub_field( 'form_id' );
            ?>

            <div class="row align-items-center justify-content-center service-step">
                <div class="col-md-12 col-lg-7 col-xl-8">
                    <h2 class="mt-0 mb-4 text-center"><?php echo $steps_title; ?></h2>
                </div>
                <div class="col-12 col-sm-10 col-md-9 col-lg-6 col-xl-5 order-2 order-lg-1">
                    <div class="steps mt-3 mt-md-5">
                        <div class="step-number">
                            <div class="step-number-inner">1</div>
                            <div class="step-txt"><?php echo $step_1; ?></div>
                        </div>
                        <div class="step-number">
                            <div class="step-number-inner">2</div>
                            <div class="step-txt"><?php echo $step_2; ?></div>
                        </div>
                        <div class="step-number">
                            <div class="step-number-inner">3</div>
                            <div class="step-txt"><?php echo $step_3; ?></div>
                        </div>
                        <div class="step-number">
                            <div class="step-number-inner">4</div>
                            <div class="step-txt"><?php echo $step_4; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6 col-xl-5 order-1 order-lg-2">
                    <img src="/wp-content/themes/LegalAddress/img/steps.webp" class="lazyload" width="620" height="620" alt="<?php echo $steps_title; ?>">
                </div>
                <div class="col-md-12 col-lg-9 col-xl-7 mt-3 mt-lg-5 order-3 order-lg-3">
                    <div class="txt-grey my-5 fs-xs text-center">
                        <p><?php echo $step_description; ?></p>
                    </div>
                </div>
            </div>

            <!-- Calendly inline widget begin -->
            <?php get_template_part( 'template-parts/content', 'consultation' ); ?>

        <?






        // ===== Layout: FAQ =====
        elseif ( get_row_layout() === 'service_faq' ) :
 
            $faq_title = get_sub_field( 'faq_title' );
            ?>

            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-9 col-xl-9 mx-lg-auto">
                    <div class="service-faq">
                        <h2 class="mt-0 mb-4"><?php echo esc_html( $faq_title ); ?></h2>
                        <?php if ( have_rows( 'faq_items' ) ) :
                            $item       = 0;
                            $faq_schema = array(
                                '@context'   => 'https://schema.org',
                                '@type'      => 'FAQPage',
                                'mainEntity' => array(),
                            );
                            ?>
                            <div class="myaccordion" id="accordionExample">
                                <?php while ( have_rows( 'faq_items' ) ) : the_row();
         
                                    $question = get_sub_field( 'question' );
                                    $answer   = get_sub_field( 'answer' );
         
                                    $aria           = $item === 0 ? 'aria-expanded="true"'  : 'aria-expanded="false"';
                                    $collapse_class = $item === 0 ? ' show' : '';
                                    ?>
                                    <div class="myaccard">
                                        <div class="card-header myacheader" id="heading<?php echo esc_attr( $item ); ?>">
                                            <a class="collapsed" data-toggle="collapse"
                                               data-target="#collapse<?php echo esc_attr( $item ); ?>"
                                               aria-controls="collapse<?php echo esc_attr( $item ); ?>"
                                               data-parent="#accordionExample" <?php echo $aria; ?>>
                                                <span><?php echo esc_html( $question ); ?></span>
                                                <i class="fa fa-angle-down"></i>
                                            </a>
                                        </div>
                                        <div id="collapse<?php echo esc_attr( $item ); ?>"
                                             class="collapse<?php echo esc_attr( $collapse_class ); ?>"
                                             aria-labelledby="heading<?php echo esc_attr( $item ); ?>"
                                             data-parent="#accordionExample">
                                            <div class="card-body">
                                                <?php echo wp_kses_post( $answer ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
         
                                    // Копим данные для JSON-LD прямо во время обхода тех же рядов
                                    $faq_schema['mainEntity'][] = array(
                                        '@type'          => 'Question',
                                        'name'           => wp_strip_all_tags( $question ),
                                        'acceptedAnswer' => array(
                                            '@type' => 'Answer',
                                            'text'  => wp_strip_all_tags( $answer ),
                                        ),
                                    );
                                    $item++;
                                endwhile; ?>
                            </div>
                            <script type="application/ld+json">
                                <?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>



            <!-- Block Author/Expert -->
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-9 col-xl-9 mx-lg-auto">
                    <div class="row">
                        <div class="col-12 col-sm-10 col-md-8 col-lg-9 col-xl-8 mx-auto">
                            <div class="author-block">
                                <?php
                                    $source_id = 1199;
                                    // получаем ID перевода под ТЕКУЩИЙ язык страницы, на которой мы сейчас находимся
                                    $page_id = function_exists('pll_get_post') 
                                        ? pll_get_post($source_id, pll_current_language()) 
                                        : $source_id;

                                    if (!$page_id) {
                                        $page_id = $source_id; // fallback, если перевод не найден
                                    }

                                    $expert_name         = get_field('expert-name', $page_id);
                                    $expert_jobtitle     = get_field('expert-jobtitle', $page_id);
                                    $expert_desc         = get_field('expert-desc', $page_id);
                                    $expert_label        = get_field('expert-label', $page_id);
                                    $expert_linkedin     = get_field('expert-linkedin', $page_id);
                                ?>
                                <div class="status ico-stat">
                                    <i class="expert"></i><span><?php echo esc_attr( $expert_label ); ?></span>
                                </div>
                                <div class="avatar">
                                    <img src="/wp-content/uploads/2026/08/jana-kamoza.webp" width="92" height="92" alt="<?php echo esc_attr( $expert_name ); ?>">
                                </div>
                                <ul class="author-data">
                                    <li><b><?php echo esc_attr( $expert_name ); ?></b></li>
                                    <li><?php echo esc_attr( $expert_jobtitle ); ?></li>
                                    <li><?php echo esc_attr( $expert_desc ); ?></li>
                                    <li><a class="underlined black" href="<?php echo esc_attr( $expert_linkedin ); ?>" target="_blank" rel="noopener noreferrer">Linkedin</a></li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        <?php


        // ===== Layout: Tariffs =====
        elseif ( get_row_layout() === 'tariffs' ) :

            $tariff_title = get_sub_field( 'tariff_title' );
            ?>
                <div class="row justify-content-between service-tariffs-title">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                        <h2 class="mt-0 mb-5 pb-0 pb-md-3"><?php echo $tariff_title; ?></h2>
                    </div>
                </div>

                <div class="row no-gutters justify-content-between service-tariffs">
                    <?php
                        // Получаем массив строк репитера без нарушения указателя have_rows()
                        $tariff_items_raw = get_sub_field( 'tariff_items' );
                        $tariffs_count    = is_array( $tariff_items_raw ) ? count( $tariff_items_raw ) : 0;

                        // Определяем класс колонки через switch
                        switch ( $tariffs_count ) {
                            case 4:
                                $col_class = 'col-md-12 col-lg-3 tariffs';
                                break;
                            case 2:
                                $col_class = 'col-md-12 col-lg-6 tariffs';
                                break;
                            case 3:
                            default:
                                $col_class = 'col-md-12 col-lg-4 tariffs';
                                break;
                        }
                    ?>

                    <?php if ( have_rows( 'tariff_items' ) ) : ?>
                        <?php while ( have_rows( 'tariff_items' ) ) : the_row(); ?>
                            <?php
                                $top                    = get_sub_field( 'top' );
                                $name                   = get_sub_field( 'name' );
                                $description_green      = get_sub_field( 'description_green' );
                                $description            = get_sub_field( 'description' );
                                $price_from             = get_sub_field( 'price_from' );
                                $price                  = get_sub_field( 'price' );
                                $price_up_to            = get_sub_field( 'price_up_to' );
                                $price_year             = get_sub_field( 'price_year' );
                                $package_items          = get_sub_field( 'package_items' );
                                $package_star           = get_sub_field( 'package_star' );
                                $package_button_styles  = get_sub_field( 'package_button_styles' );
                                $package_anchor         = get_sub_field( 'package_anchor' );

                                // Копим цену для Service Schema прямо во время обхода тех же рядов
                                if ( $price !== '' && $price !== null ) {
                                    // На случай, если в поле цена введена как "1 280" или "1,280" — чистим до числа
                                    $clean_price = (float) preg_replace( '/[^\d.]/', '', str_replace( ',', '.', $price ) );
                                    if ( $clean_price > 0 ) {
                                        $schema_tariff_prices[] = $clean_price;
                                    }
                                }
                            ?>
                            <div class="<?php echo esc_attr( $col_class ); ?>">
                                <div class="packag">
                                    <?php if ( $top ) : ?>
                                        <div class="top">Top</div>
                                    <?php endif; ?>
                                    <div class="num"></div>
                                    <div class="tariff-name mb-2"><?php echo esc_html( $name ); ?></div>
                                    <div class="txt-green">
                                        <?php echo $description_green; ?>
                                    </div>
                                    <div class="txt-grey">
                                        <?php echo $description; ?>
                                    </div>
                                    <?php if ( $price ) : ?>
                                        <div class="price">
                                            <span class="from"><?php echo esc_html( $price_from ); ?>&nbsp;</span><?php echo esc_html( $price ); ?>&nbsp;&#8364;<span class="from">&nbsp;<?php echo esc_html( $price_year ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <ul class="list-checked">
                                        <?php echo $package_items; ?>
                                    </ul>
                                    <div class="txt-grey mb-4 fs-xs">
                                        <?php echo $package_star; ?>
                                    </div>
                                    <a href="javascript:void(0);"
                                       data-toggle="modal"
                                       data-target=".form_<?php echo esc_attr( $form_id_class ); ?>"
                                       class="btn <?php echo esc_attr( $package_button_styles ); ?>">
                                        <?php echo esc_html( $package_anchor ); ?>
                                    </a>
                                </div>
                            </div>    
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            <?php
        endif;

    endwhile;

else :
    // Блоки не заполнены — можно ничего не выводить
    // или показать заглушку для админа
endif;

// ===== Динамическая Service Schema =====
// Реальная минимальная цена среди всех пакетов тарифа, найденных на странице
$schema_min_price = ! empty( $schema_tariff_prices ) ? min( $schema_tariff_prices ) : null;

if ( $schema_min_price ) :
    $service_schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'serviceType' => $schema_service_type,
        'provider'    => array(
            '@type'      => 'LegalService',
            'name'       => $schema_provider_name,
            'areaServed' => $schema_area_served_code,
            'url'        => $schema_provider_url,
        ),
        'areaServed' => array(
            '@type' => 'Country',
            'name'  => $schema_area_served_name,
        ),
        'offers' => array(
            '@type'         => 'Offer',
            'priceCurrency' => 'EUR',
            'price'         => (string) $schema_min_price,
            'url'           => $schema_service_url,
        ),
    );
    ?>
    <script type="application/ld+json">
        <?php echo wp_json_encode( $service_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
    </script>
<?php endif; ?>


<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Person",
          "@id": "https://legaladdressinestonia.com/author/jana-kamoza/",
          "name": "Jana Kamoza",
          "jobTitle": "CEO & Legal Advisor at eBusiness Solutions OÜ",
          "image": "/wp-content/uploads/2026/07/jana-kamoza.webp",
          "sameAs": [
            "https://www.linkedin.com/in/jana-kamoza-international-business-legal"
          ],
          "worksFor": {
            "@type": "Organization",
            "name": "eBusiness Solutions OÜ"
          }
        },
        {
          "@type": "Service",
          "@id": "https://legaladdressinestonia.com/services/",
          "name": "Chief accountant",
          "url": "https://legaladdressinestonia.com/services/",
          "reviewedBy": { "@id": "https://legaladdressinestonia.com/#jana-kamoza" },
          "author": { "@id": "https://legaladdressinestonia.com/author/jana-kamoza/" }
        }
      ]
    }
</script>

<div class="modal fade form_<?php echo esc_attr( $form_id_class ); ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <div class="modal-content">
            <?php echo do_shortcode( $form_shortcode ); ?>
        </div>
    </div>
</div>