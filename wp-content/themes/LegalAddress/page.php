<?php get_header(); ?>
<div class="container content-page">
	<div class="row">
		<div class="col-12 col-sm-12 col-md-12 head-title">
			<h1 class="h2 my-4"><?php the_title(); ?></h1>
			<?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>
		</div>
		<div class="col-12 col-sm-12 col-md-12">
			<div class="my-5 pb-5">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>