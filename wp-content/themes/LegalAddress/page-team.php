<?php
    /**
    *
    * Template Name: Team
    *
    */
    get_header();

    // --- Добавлено: форма и класс модалки по текущему языку ---
    $localized_form = get_localized_cf7_form();
    $form_shortcode = $localized_form['shortcode'];
    $form_id_class  = $localized_form['modal_class'];
    // -----------------------------------------------------------
?>
<div class="container content-page team-page">

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

            <div class="row justify-content-center mt-4 mt-sm-4 mt-md-5 tech">
                <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                    <?php the_field('team_top'); ?>
                </div>
            </div>

            <div class="row justify-content-center slider">
                <div class="col-12 col-sm-12 col-md-10 col-lg-9 col-xl-8">

                    <div class="one-photo">
                        <?php if( have_rows('team_people') ) : ?>
                            <?php while( have_rows('team_people') ) : the_row();
                                $photo = get_sub_field('team_people_loop_img');
                                $name = get_sub_field('team_people_loop_name');
                                $jobtitle = get_sub_field('team_people_loop_job_title');
                                $other = get_sub_field('team_people_loop_other');
                                $otherbottom = get_sub_field('team_people_loop_other_bottom');
                                $btn = get_sub_field('team_people_loop_btn');
                                $linkedin = get_sub_field('team_people_loop_linkedin');
                                // team_people_loop_shortcode больше не нужен — форма берётся по языку
                            ?>
                            <div>
                                <div class="team-card">
                                    <div class="photo">
                                        <img src="<?php echo esc_html( $photo ); ?>" width="" height="" alt="<?php echo esc_html( $name ); ?>" title="<?php echo esc_html( $name ); ?>">
                                    </div>
                                    <div class="data">
                                        <div class="name"><?php echo esc_html( $name ); ?></div>
                                        <div class="job-title"><?php echo esc_html( $jobtitle ); ?></div>
                                        <div class="success">
                                            <span><?php echo esc_html( $other ); ?></span>
                                            <span><?php echo esc_html( $otherbottom ); ?></span>
                                        </div>
                                        <ul>
                                            <li>
                                                <a class="btn green" href="javascript:void (0);" data-toggle="modal" data-target=".form_<?php echo esc_attr( $form_id_class ); ?>">
                                                    <?php echo esc_html( $btn ); ?>
                                                </a>
                                            </li>
                                            <li><a class="btn in" href="<?php echo esc_html( $linkedin ); ?>" target="_blank" rel="noopener noreferrer"></a></li>
                                        </ul>
                                        
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php
                            else :
                                echo '<p>No Data</p>';
                            endif;
                        ?>
                    </div>
                            
                    <div class="progress-wrapper">
                        <div class="progress-bar"><span></span></div>
                        <div class="counter"></div>
                    </div>

                </div>
            </div>

            <div class="row justify-content-center docs-wrapper">
                <div class="col-12 col-sm-12 col-md-12">

                    <?php the_field('team_people_bottom_text'); ?>

                    <div class="row add-scroll">
                        <?php if( have_rows('docs_loop') ) : ?>
                            <?php while( have_rows('docs_loop') ) : the_row();
                                $docsimg = get_sub_field('docs_loop_image');
                                $docsname = get_sub_field('docs_loop_name');
                            ?>
                            <div class="col-6 col-sm-4 col-md-4 col-lg-3">
                                <a class="doc" href="javascript:void (0);" data-toggle="modal" data-target="#exampleModal-pdf-cem-0">
                                    <img src="<?php echo esc_html( $docsimg ); ?>" width="290" height="410" alt="<?php echo esc_html( $docsname ); ?>" title="<?php echo esc_html( $docsname ); ?>">
                                </a>
                            </div>
                            <?php endwhile; ?>
                        <?php
                            else :
                                echo '<p>No Data</p>';
                            endif;
                        ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- Modal -->
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
<!-- Modal -->
<?php get_footer(); ?>