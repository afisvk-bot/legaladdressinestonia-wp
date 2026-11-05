<?php
	/**
    *
    * Template Name: Contact page
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
    <div class="row justify-content-center">
        <div class="col-12 col-sm-12 col-md-11 col-lg-11 mx-lg-auto my-4">
            <?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>
        </div>
    </div>

	<div class="row justify-content-between mt-2 mt-sm-4 mt-md-4">
		<div class="col-12 col-sm-12 col-md-4 col-xl-4 order-2 order-md-1 order-lg-1 mt-4 mt-sm-4 mt-md-0">
        	<?php the_content(); ?>
        </div>
		<div class="col-12 col-sm-12 col-md-8 col-xl-8 order-1 order-md-2 order-lg-2">
			<div class="row">

				<div class="col-12 col-sm-12 col-md-7 col-xl-7 pl-3 pl-md-3 pl-lg-5">
					<ul class="px-0 mt-0">
						<li><b><?php the_field('customer_care'); ?></b></li>
						<li><?php the_field('tel'); ?> <a class="underlined black" href="tel:+37256262629">+372 56262629</a></li>
						<li><?php the_field('address'); ?></li>
						<li>E-mail: <a class="underlined black" href="mailto:info@legaladdressinestonia.com">info@legaladdressinestonia.com</a></li>
						<li><?php the_field('social_media'); ?>: <a class="underlined black" href="https://www.linkedin.com/company/legal-address-in-estonia/" target="_blank" rel="noopener noreferrer">Linkedin</a></li>
					</ul>
					<ul class="px-0">
						<li><b><?php the_field('location'); ?></b></li>
						<li class="mb-4"><?php the_field('location_data'); ?></li>
						<li class="mb-4">*<?php the_field('location_data_add'); ?></li>
						<li><a class="underlined black" href="https://www.google.com/maps/place/Legal+Address+in+Estonia/@58.6436206,22.2941961,7z/data=!4m10!1m2!2m1!1zTGVnYWwgQWRkcmVzcyBpbiBFc3RvbmlhLCBQw6RldmFsaWxsZSB0biA2LTE1LCAxMzUxNyBUYWxsaW5uLCDQrdGB0YLQvtC90LjRjw!3m6!1s0x46929515dc06a42b:0x7d2f04f6ad11e7aa!8m2!3d59.4222413!4d24.6471131!15sCkxMZWdhbCBBZGRyZXNzIGluIEVzdG9uaWEsIFDDpGV2YWxpbGxlIHRuIDYtMTUsIDEzNTE3IFRhbGxpbm4sINCt0YHRgtC-0L3QuNGPWksiSWxlZ2FsIGFkZHJlc3MgaW4gZXN0b25pYSBww6RldmFsaWxsZSB0biA2IDE1IDEzNTE3IHRhbGxpbm4g0Y3RgdGC0L7QvdC40Y-SAR5idXNpbmVzc19tYW5hZ2VtZW50X2NvbnN1bHRhbnSaASRDaGREU1VoTk1HOW5TMFZKUTBGblNVTmtiMHRpTVhwblJSQULgAQD6AQQIABAa!16s%2Fg%2F11sbnzvmj4?hl=ru-RU&entry=ttu&g_ep=EgoyMDI2MDcxMi4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noopener noreferrer"><?php the_field('view_on_google_maps'); ?></a></li>
					</ul>
				</div>

				<div class="col-12 col-sm-12 col-md-5 col-xl-5">
					<ul class="px-0 mt-0">
						<li><b><?php the_field('business_information'); ?></b></li>
						<li><?php the_field('bi_1'); ?></li>
						<li><?php the_field('bi_2'); ?></li>
						<li><?php the_field('bi_3'); ?></li>
						<li><?php the_field('bi_4'); ?></li>
					</ul>
					<ul class="px-0">
						<li><b><?php the_field('working_hours'); ?></b></li>
						<li class="mb-0"><?php the_field('wh_1'); ?></li>
						<li><?php the_field('wh_2'); ?></li>
					</ul>
				</div>

			</div>
		</div>
	</div>

</div>
<?php get_footer(); ?>