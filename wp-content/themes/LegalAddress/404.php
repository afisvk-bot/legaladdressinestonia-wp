<?php get_header(); ?>

<div class="container content-page">

  <div class="row">
    <div class="col-12 col-sm-12 col-md-12 head-title">
      <h1 class="h2 my-4">
        <?php the_title(); ?>Error 404: page not found
      </h1>
    </div>
  </div>

  <div class="row justify-content-center mt-4">
    <div class="col-12 col-sm-12 col-md-11 col-lg-11">
      <?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>
      <div class="row mt-4 mt-sm-4 mt-md-5 tech">
        <div class="col-12 col-sm-12 col-md-11 col-lg-9">
            <p>You may be here for several reasons:</p>

            <ol>
              <li>Most likely the URL was entered with an error.</li>
              <li>The page you are looking for has been moved or renamed.</li>
              <li>This page no longer exists.</li>
            </ol>

            <p>Or you have the option to go to the <a href="/">home</a> page.</p>
        </div>
      </div>
    </div>
  </div>
  
</div>

<?php get_footer(); ?>