<?php
	/**
    *
    * Template Name: По-центру
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
        <div class="col-12 col-sm-12 col-md-11 col-lg-11">

            <?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>

            <div class="row mt-4 mt-sm-4 mt-md-5 tech">
                <div class="col-12 col-sm-12 col-md-11 col-lg-9">
                    <?php the_content(); ?>
                </div>
            </div>

        </div>
    </div>
</div>
<?php get_footer(); ?>