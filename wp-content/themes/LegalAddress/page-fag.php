<?php
	/**
    *
    * Template Name: FAQ
    *
    */
	get_header(); ?>
<div class="container content-page">

	<div class="row">
        <div class="col-12 col-sm-12 col-md-12 head-title">
            <h1 class="h2 my-4">
                <?php the_title(); ?>
            </h1>
        </div>
    </div>

    <div class="row justify-content-center mt-4">

        <div class="col-12 col-sm-12 col-md-12 col-lg-11">
            
            <?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>

            <div class="row justify-content-center mt-4 mt-sm-4 mt-md-5">

                <div class="col-12 col-sm-12 col-md-12 col-lg-9">

                    <div class="faq-page">

                        <?php if ( have_rows( 'faqs_items' ) ) :
                            $item       = 0;
                            $faq_schema = array(
                              '@context'   => 'https://schema.org',
                              '@type'      => 'FAQPage',
                              'mainEntity' => array(),
                            );
                        ?>

                        <div class="myaccordion" id="accordionExample">

                            <?php while ( have_rows( 'faqs_items' ) ) : the_row();

                                $faqs_question = get_sub_field( 'faqs_question' );
                                $faqs_answer   = get_sub_field( 'faqs_answer' );

                                $aria           = $item === 0 ? 'aria-expanded="true"'  : 'aria-expanded="false"';
                                $collapse_class = $item === 0 ? ' show' : '';
                            ?>

                            <div class="myaccard">
                                <div class="card-header myacheader" id="heading<?php echo esc_attr( $item ); ?>">
                                    <a class="collapsed" data-toggle="collapse"
                                        data-target="#collapse<?php echo esc_attr( $item ); ?>"
                                        aria-controls="collapse<?php echo esc_attr( $item ); ?>"
                                        data-parent="#accordionExample" <?php echo $aria; ?>>
                                        <span><?php echo esc_html( $faqs_question ); ?></span>
                                        <i class="fa fa-angle-down"></i>
                                    </a>
                                </div>
                                <div id="collapse<?php echo esc_attr( $item ); ?>"
                                     class="collapse<?php echo esc_attr( $collapse_class ); ?>"
                                     aria-labelledby="heading<?php echo esc_attr( $item ); ?>"
                                     data-parent="#accordionExample">
                                    <div class="card-body">
                                        <?php echo wp_kses_post( $faqs_answer ); ?>
                                    </div>
                                </div>
                            </div>
                              <?php

                              $faq_schema['mainEntity'][] = array(
                                  '@type'          => 'Question',
                                  'name'           => wp_strip_all_tags( $faqs_question ),
                                  'acceptedAnswer' => array(
                                        '@type' => 'Answer',
                                        'text'  => wp_strip_all_tags( $faqs_answer ),
                                  ),
                              );
                              $item++;
                            endwhile; ?>

                        </div>

                        <script type="application/ld+json">
                            <?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
                        </script>

                        <?php endif;  ?>

                    </div>
                    
                </div>
            </div>

        </div>
    </div>

</div>
<?php get_footer(); ?>