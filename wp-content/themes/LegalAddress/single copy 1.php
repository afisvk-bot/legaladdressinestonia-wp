<?php get_header(); ?>

<div itemscope itemtype="http://schema.org/Article" class="container content-page single-page">

	<div class="row">

		<div class="col-12 col-sm-12 col-md-12 head-title">
			<h1 itemprop="headline" class="h2 my-4">
				<?php the_title(); ?>
			</h1>
		</div>

	</div>

	<div class="row">

   		<div class="col-12 col-sm-12 col-md-12 col-lg-11 mx-lg-auto my-4">
   			<?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>
		</div>

		<article itemscope itemtype="http://schema.org/Article" class="col-12 col-sm-12 col-sm-12 col-lg-11 mx-lg-auto mb-5" <?php post_class('post'); ?> id="post-<?php the_ID(); ?>">


				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

					<div itemscope itemprop="image" itemtype="http://schema.org/ImageObject" class="single-image mb-3">
						<?php 
							if ( has_post_thumbnail() ) { 
							    $image_id = get_post_thumbnail_id();
							    $image_url = wp_get_attachment_image_url($image_id, 'full');
							    ?>
							    <div itemscope itemtype="http://schema.org/ImageObject">
							        <?php the_post_thumbnail( 'full', [ 
							            'alt' => esc_html( get_the_title() ),
							            'title' => esc_html( get_the_title() ),
							            'itemprop' => 'contentUrl'
							        ] ); ?>
							        <meta itemprop="url" content="<?php echo esc_url($image_url); ?>">
							    </div>
							<?php } 
						?>
					</div>

					<div class="row">
						<div class="col-12 col-sm-12 col-sm-12 col-lg-8 mx-lg-auto">
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

		   			<div class="row article-content">
						<div itemprop="articleBody" class="col-12 col-sm-12 col-sm-12 col-lg-9 col-xl-8 mx-lg-auto">
							<?php the_content(); ?>
						</div>
					</div>

				<?php endwhile; else: ?>
					<p><?php _e('К сожалению, такой страницы не существует.'); ?></p>
				<?php endif; ?>
			

		</article>

	</div>

</div>


<section class="blog">
  	<div class="container">
  		
  		<div class="row">
  			<div class="col-12 col-sm-12 col-sm-12 col-lg-11 mx-lg-auto">
  				<h3 class="mb-4">
	  				<?php $l = substr(get_locale(), 0, 2);
			            if ($l =='en') {
			              	echo "Related articles";
			            } elseif ($l =='de') {
			              	echo "Verwandte Artikel";
			            } elseif ($l =='fr') {
			              	echo "Articles connexes";
			            } elseif ($l =='ru') {
			              	echo "Похожие статьи";
			            } elseif ($l =='uk') {
	                        echo "Пов'язані статті";
			            } elseif ($l =='es') {
			              	echo "Artículos relacionados";
			            } elseif ($l =='zh') {
			              	echo "相关文章";
			            } elseif ($l =='pt') {
			              	echo "Artigos relacionados";
			            } elseif ($l =='tr') {
			              	echo "İlgili makaleler";
			            }
			        ?>
			    </h3>

  				<?php
					$categories = get_the_category($post->ID);
					if ($categories) {
						$category_ids = array();
						foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
						$args=array(
							'category__in' => $category_ids,
							'post__not_in' => array($post->ID),
							'showposts' => '3',
							'orderby' => 'rand',
							'ignore_sticky_posts' => '1',
							'no_found_rows' => true,
							'cache_results' => false
						);
						$my_query = new wp_query($args);
							if( $my_query->have_posts() ) {
								echo '<div class="row">';
									while ($my_query->have_posts()) {
										$my_query->the_post();
										?>
										<div class="col-md-4 col-lg-4 mb-4">
											<article>
												<a href="<?php the_permalink() ?>">
										            <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
										            <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
										            <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
										            <span class="txt-grey"><?php the_excerpt(); ?></span>
										        </a>
									        </article>
						            	</div>
										<?php
									}
								echo '</div>';
							}
						wp_reset_query();
					}
				?>

  			</div>
  		</div>

  	</div>
</section>
<?php get_footer(); ?>