<?php
	/**
    *
    * Template Name: Archive category
    *
    */
	get_header(); ?>
<section class="blog">
	<div class="container mb-5 content-page">
		<div class="row">
			<div class="col-12 col-sm-12 col-md-12 head-title">
				<?php
					the_archive_title( '<h1 class="h2 my-4">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' );
				?>
			</div>
		</div>
		<div class="row">
	   		<div class="col-sm-12 col-lg-11 mx-lg-auto my-4 pb-5">
				<?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>
				<div class="row mt-5">
					<?php while (have_posts()) : the_post(); ?>
			            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 mb-4 arhive" itemscope itemtype="http://schema.org/Article">
			            	<article class="article-box">
			            		<a href="<?php the_permalink() ?>">
			                      	<span class="date d-block mb-2"><?php echo get_the_date( 'F j, Y' ); ?></span>
			                      	<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'thumbnail', [ 'alt' => esc_html ( get_the_title() ), 'title' => esc_html ( get_the_title() )] ); } ?>
			                        <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
			                        <span class="txt-grey"><?php the_excerpt(); ?></span>
			                    </a>
			                </article>
			            </div>
					<?php endwhile; ?>
					<div class="col-12 my-5 pt-5">
						<?php the_posts_pagination(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php get_footer(); ?>