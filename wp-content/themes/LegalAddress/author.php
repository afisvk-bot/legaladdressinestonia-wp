<?php
/**
 * Template: Author archive page
 *
 * Требования:
 * - Заголовок, аватар, текст об авторе, ссылка на сайт, количество статей автора
 * - Карточки всех статей автора
 * - Полная поддержка переводов (WPML / Loco Translate / Poedit и т.п.)
 *
 * ВАЖНО: замените 'legaladdress' на текстовый домен вашей темы (Text Domain
 * из style.css), если он отличается — иначе строки не подхватятся переводом.
 */
get_header();

// $text_domain = 'legaladdress';
$author_id = (int) get_query_var( 'author' );
?>

<section class="blog">

    <div class="container mb-5 content-page services author">

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 head-title">
                <h1 class="h2 my-4">
                    <?php
                    printf(
                        /* translators: %s: имя автора */
                        esc_html( la_pll( 'Автор: %s' ) ),
                        esc_html( get_the_author_meta( 'display_name', $author_id ) )
                    );
                    ?>
                </h1>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-sm-12 col-md-11 col-lg-11 mx-lg-auto my-4">
                <?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>
            </div>
        </div>

        <div class="row my-0 my-sm-4 my-md-5">

            <div class="col-12 col-sm-10 col-md-8 col-lg-8 mx-auto">

                <div class="row no-gutters align-items-center author-bio py-4">

                    <div class="col-12 col-sm-12 col-md-auto col-lg-auto avatar mb-3 mb-sm-3 mb-md-3 mb-lg-0">
                        <?php
                            echo get_avatar(
                                $author_id,
                                120,
                                '',
                                esc_attr( get_the_author_meta( 'display_name', $author_id ) )
                            );
                        ?>
                    </div>

                    <div class="col-12 col-sm-12 col-md-9 col-lg-8">
                        <ul class="author-meta m-0 p-0">
                            <li>
                                <b><?php la_pll_e( 'Об авторе:' ); ?></b>
                                <?php echo esc_html( get_the_author_meta( 'description', $author_id ) ); ?>
                            </li>
                            <li>
                                <b><?php la_pll_e( 'Вебсайт:' ); ?></b>
                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                                    <?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?>
                                </a>
                            </li>
                            <li>
                                <?php
                                $count_args = array(
                                    'author'         => $author_id,
                                    'category_name'  => 'blog',
                                    'post_type'      => 'post',
                                    'post_status'    => 'publish',
                                    'posts_per_page' => -1,
                                    'fields'         => 'ids',
                                );
                                $count_query = new WP_Query( $count_args );
                                $total_count = $count_query->found_posts;
                                wp_reset_postdata();
                                ?>
                                <b><?php la_pll_e( 'Всего статей:' ); ?></b>
                                <?php echo esc_html( $total_count ); ?>
                            </li>
                        </ul>
                    </div>

                </div>

                <?php
                    $current_lang = pll_current_language();

                    // Формируем имя мета-поля для текущего языка
                    $meta_key = 'autor_add_info_' . $current_lang;

                    // Получаем значение поля
                    $extra_info = get_field( $meta_key, 'user_' . $author_id );

                if ( $extra_info ) : ?>
                    <div class="row no-gutters align-items-center author-bio tech mt-4 py-4">
                        <div class="col-12 col-sm-12 col-md-12">
                            <?php echo wp_kses_post( $extra_info ); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

        <div class="row mt-5">
            <div class="col-12 col-sm-12 col-md-12 col-lg-11 mx-lg-auto">
                <?php
                $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

                $args = array(
                    'author'         => $author_id,
                    'category_name'  => 'blog',
                    'post_type'      => 'post',
                    'post_status'    => 'publish',
                    'posts_per_page' => 12,
                    'paged'          => $paged,
                );
                $query = new WP_Query( $args );
                $count = $query->found_posts;

                printf(
                    '<h2 class="h3 my-4">%s (%s)</h2>',
                    esc_html( la_pll( 'Все статьи автора:' ) ),
                    esc_html( $count )
                );

                if ( $query->have_posts() ) :
                    ?>
                    <div class="row">
                        <?php
                        while ( $query->have_posts() ) :
                            $query->the_post();
                            ?>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 mb-4 arhive" itemscope itemtype="http://schema.org/Article">
                                
                                <article class="article-box">
                                    <a href="<?php the_permalink() ?>">
                                        <meta itemprop="datePublished" content="<?php the_time('Y-m-d') ?>">
                                        <meta itemprop="dateModified" content="<?php the_modified_time('Y-m-d')?>">
                                        <span class="date d-block mb-2"><?php echo get_the_date( 'F j, Y' ); ?></span>
                                        <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'thumbnail', [ 'alt' => esc_html ( get_the_title() ), 'title' => esc_html ( get_the_title() )] ); } ?>
                                        <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
                                        <span class="txt-grey"><?php the_excerpt(); ?></span>
                                    </a>
                                </article>

                            </div>

                        <?php endwhile; ?>

                        <div class="col-12 mb-5">
                            <div class="pagination">
                                <?php the_posts_pagination(); ?>
                            </div>
                        </div>

                    </div>
                    <?php
                else :
                    ?>
                    <p><?php la_pll_e( 'Нет статей.' ); ?></p>
                    <?php
                endif;

                wp_reset_postdata();
                ?>
            </div>
        </div>

    </div>

</section>

<?php get_footer(); ?>