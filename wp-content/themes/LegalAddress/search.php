<?php
	/**
    *
    * Template Name: Search
    *
    */
	get_header(); ?>
<div class="container mb-5 content-page">
	<div class="row">
		<div class="col-12 article-par-tag mt-5">
			<div class="tags-title">
				<div><?php $l = substr(get_locale(), 0, 2); if ($l =='en'): ?>Tags<?php else: ?>Теги<?php endif; ?>:</div> <?php the_tags( '<ul class="tags"><li>','</li><li>','</li></ul>'); ?>
			</div>
			<?php get_search_form(); ?>
		</div>
		<div class="col-sm-12 col-lg-12">
			<h1 class="h2 title mb-4">
				<?php $l = substr(get_locale(), 0, 2); if ($l =='en'): ?>
					<?php echo 'Search results: ' . '<span class="color-green">' . get_search_query() . '</span>'; ?>
				<?php else: ?>	
					<?php echo 'Результат поиска: ' . '<span class="color-green">' . get_search_query() . '</span>'; ?>
				<?php endif; ?>
			</h1>

			<div class="row">
				<?php while (have_posts()) : the_post(); ?>
			        <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 arhive" itemscope itemtype="http://schema.org/Article">
			        	<article class="article-box">
			        		<a href="<?php the_permalink() ?>">
			                  	<div class="article-img">
			                    	<?php the_post_thumbnail('thumbnail', array('class' => 'img-fluid')); ?>
			                    	<div class="figure">
						                <svg xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 1920 100">
						                  	<path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z"></path>
						                </svg>
						             </div>
			                  	</div>
			                  	<div class="article-body">
			                    	<h3 itemprop="headline"><?php the_title(); ?></h3>
			                    	<?php the_excerpt();?>
			                    </div>
			                    <div class="article-footer">
			                    	<?php the_date('d.m.Y'); ?>
			                    </div>
			                </a>
			            </article>
			        </div>
		        <?php endwhile; ?>
		        <div class="col-12 mt-5">
					<?php the_posts_pagination(); ?>
				</div>
	    	</div>
		</div>		
	</div>
</div>
<?php get_footer(); ?>







