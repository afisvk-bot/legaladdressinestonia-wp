<?php
/*
* Template Name: Service — L2 & L3
* Template Post Type: service, licensing, industry, target
*/
get_header();

   

?>

<div class="container content-page single-page services">
    <div class="row">
        <div class="col-12 col-sm-12 col-md-12 head-title">
            <h1 class="h2 my-4">
                <?php the_title(); ?>
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-11 mx-lg-auto my-4">
            <?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>
        </div>
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-5">
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                <div class="row">
                    <div class="col-12 col-sm-12 col-lg-11 mx-lg-auto">
                        <div class="single-image mb-3">
                            <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'full', [ 'alt' => esc_html ( get_the_title() ), 'title' => esc_html ( get_the_title() )] ); } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-11 col-lg-8 col-xl-8 mx-lg-auto">
                        <div class="data">
                            <div class="date">
                                <meta itemprop="datePublished" content="<?php the_time('Y-m-d') ?>">
                                <meta itemprop="dateModified" content="<?php the_modified_time('Y-m-d')?>">
                                <svg fill="none" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m12.6667 4h-9.33337c-.73638 0-1.33333.59695-1.33333 1.33333v7.33337c0 .7363.59695 1.3333 1.33333 1.3333h9.33337c.7363 0 1.3333-.597 1.3333-1.3333v-7.33337c0-.73638-.597-1.33333-1.3333-1.33333z" stroke="#030e1a"></path><path d="m2 6.66667c0-1.25708 0-1.88562.39053-2.27614.39052-.39053 1.01906-.39053 2.27614-.39053h6.66663c1.2571 0 1.8856 0 2.2762.39053.3905.39052.3905 1.01906.3905 2.27614z" fill="#030e1a"></path><path d="m4.66797 2v2" stroke="#030e1a" stroke-linecap="round"></path><path d="m11.332 2v2" stroke="#030e1a" stroke-linecap="round"></path><g fill="#030e1a"><path d="m7.0013 8h-2c-.18409 0-.33333.14924-.33333.33333v.66667c0 .18409.14924.33333.33333.33333h2c.1841 0 .33334-.14924.33334-.33333v-.66667c0-.18409-.14924-.33333-.33334-.33333z"></path><path d="m7.0013 10.668h-2c-.18409 0-.33333.1492-.33333.3333v.6667c0 .1841.14924.3333.33333.3333h2c.1841 0 .33334-.1492.33334-.3333v-.6667c0-.1841-.14924-.3333-.33334-.3333z"></path><path d="m11.0013 8h-2c-.18409 0-.33333.14924-.33333.33333v.66667c0 .18409.14924.33333.33333.33333h2c.1841 0 .3333-.14924.3333-.33333v-.66667c0-.18409-.1492-.33333-.3333-.33333z"></path><path d="m11.0013 10.668h-2c-.18409 0-.33333.1492-.33333.3333v.6667c0 .1841.14924.3333.33333.3333h2c.1841 0 .3333-.1492.3333-.3333v-.6667c0-.1841-.1492-.3333-.3333-.3333z"></path></g></svg> <?php echo get_the_date( 'F j, Y' ); ?>
                            </div>
                            
                            <div class="author">
                                <?php $l = substr(get_locale(), 0, 2);
                                    if ($l =='en') {
                                      echo "Author";
                                    } elseif ($l =='de') {
                                      echo "Autor";
                                    } elseif ($l =='uk') {
                                      echo "Автор";
                                    } elseif ($l =='fr') {
                                      echo "Auteur";
                                    } elseif ($l =='ru') {
                                      echo "Автор";
                                    } elseif ($l =='es') {
                                      echo "Autor";
                                    } elseif ($l =='zh') {
                                      echo "作者";
                                    } elseif ($l =='pt') {
                                      echo "Autor";
                                    } elseif ($l =='tr') {
                                      echo "Yazar";
                                    }
                                ?>: <?php the_author_posts_link(); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php ob_start(); // ── собираем контентные модули для оглавления ── ?>

                <div class="flex-cont">

                    <!-- CONTENT FLEX -->
                    <?php get_template_part('template-parts/content', 'flex'); ?>

                    <?php
                        // ── Оглавление: строим из собранных модулей и проставляем якоря ──
                        $i10v_body = ob_get_clean();
                        if ( function_exists( 'i10v_build_toc' ) ) {
                            // list( $i10v_body, $i10v_toc ) = i10v_build_toc( $i10v_body, array( 'h2', 'h3' ) );
                            list( $i10v_body, $i10v_toc ) = i10v_build_toc( $i10v_body, array( 'h2' ) );
                            if ( $i10v_toc ) {
                                echo '<div class="row"><div class="col-12 col-sm-12 col-md-11 col-lg-8 col-xl-8 mx-lg-auto">' . $i10v_toc . '</div></div>';
                            }
                        }
                        echo $i10v_body; // тело модулей с якорями (или как есть, если хелпера нет)
                    ?>

                </div>

                <?php endwhile; else: ?>
                    <p><?php _e('Unfortunately, such a page does not exist.'); ?></p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>