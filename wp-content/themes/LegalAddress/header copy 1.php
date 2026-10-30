<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="UTF-8">
<title><?php wp_title( '|', true, 'left' ); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link rel="shortcut icon" href="/favicon.ico">
<link rel="icon" sizes="16x16 32x32 64x64" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="196x196" href="/favicon-192.png">
<link rel="icon" type="image/png" sizes="160x160" href="/favicon-160.png">
<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96.png">
<link rel="icon" type="image/png" sizes="64x64" href="/favicon-64.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16.png">
<link rel="apple-touch-icon" href="/favicon-57.png">
<link rel="apple-touch-icon" sizes="114x114" href="/favicon-114.png">
<link rel="apple-touch-icon" sizes="72x72" href="/favicon-72.png">
<link rel="apple-touch-icon" sizes="144x144" href="/favicon-144.png">
<link rel="apple-touch-icon" sizes="60x60" href="/favicon-60.png">
<link rel="apple-touch-icon" sizes="120x120" href="/favicon-120.png">
<link rel="apple-touch-icon" sizes="76x76" href="/favicon-76.png">
<link rel="apple-touch-icon" sizes="152x152" href="/favicon-152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/favicon-180.png">
<meta name="msapplication-TileColor" content="#FFFFFF">
<meta name="msapplication-TileImage" content="/favicon-144.png">
<meta name="msapplication-config" content="/browserconfig.xml">
<!-- <meta name="theme-color" content="#FFFFFF"> -->
<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!-- Google Tag Manager --> 
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': 
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], 
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src= 
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); 
})(window,document,'script','dataLayer','GTM-MTRXJR7');</script> 
<!-- End Google Tag Manager -->
<!-- <script src="//code.jivo.ru/widget/Y8na3hoKtP" async></script> -->
<script>window.pipedriveLeadboosterConfig = {base: 'leadbooster-chat.pipedrive.com',companyId: 14341702,playbookUuid: 'd7e3bbe5-523d-4fa9-aec7-0cd081c67eb9',version: 2};(function () {var w = window;if (w.LeadBooster) {console.warn('LeadBooster already exists');} else {w.LeadBooster = {q: [],on: function (n, h) {this.q.push({ t: 'o', n: n, h: h });},trigger: function (n) {this.q.push({ t: 't', n: n });},};}})();</script><script src="https://leadbooster-chat.pipedrive.com/assets/loader.js" async></script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header itemscope itemtype="http://schema.org/WPHeader">
  <div class="container">
    <div class="row align-items-center justify-content-between">

      <a class="logo" href="<?php echo get_home_url(); ?>">
<img src="<?php echo get_template_directory_uri(); ?>/img/logo.svg" width="150" height="45" alt="Legal Address in Estonia" title="Legal Address in Estonia">      </a>

      <nav class="primary-menu d-none d-sm-none d-md-none d-lg-block" itemscope itemtype="https://schema.org/SiteNavigationElement">
        <?php
          wp_nav_menu( array(
            'theme_location'  => 'primary-menu',
            'depth'           => 2,
            'container'       => 'div',
            'container_id'    => 'navbarsExampleDefault',
            'container_class' => 'collapse navbar-collapse',
            'menu_class'      => 'nav navbar-nav',
            'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
            'walker'          => new WP_Bootstrap_Navwalker()
          ) );
        ?>
      </nav>
      <div class="mob_ready">
        <nav class="navbar navbar-expand-md">
          <?php
            wp_nav_menu( array(
              'theme_location'  => 'lan-menu',
              'depth'           => 2,
              'container'       => 'div',
              'container_id'    => 'navbarsExampleDefault',
              'container_class' => 'collapse navbar-collapse',
              'menu_class'      => 'nav navbar-nav',
              'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
              'walker'          => new WP_Bootstrap_Navwalker()
            ) );
          ?>
        </nav>
        <div class="menu-wrap">
          <a href="javascript:void(0);" id="btnContact" class="contact_btn mob_btn"
             data-is-active="false"
             data-text1="&#9776;"
             data-text2="&#10005;"
             title="Menu">&#9776;</a>
          <nav class="menu" itemscope itemtype="https://schema.org/SiteNavigationElement">
              <?php
              wp_nav_menu([
                  'theme_location' => 'mobile_menu',
                  'container'      => false,
                  'menu_id'        => 'mob-menu',
                  'walker'         => new Mobile_Menu_Walker(),
                  'fallback_cb'    => false,
              ]);
              ?>
          </nav>
        </div>
      </div>
    </div>
  </div>
</header>