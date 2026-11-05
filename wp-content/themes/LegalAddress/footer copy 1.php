<footer itemscope="http://schema.org/WPFooter">
  
  <div class="container">
    <div class="row justify-content-between">
      <div class="col-12 col-sm-12 col-md-12 col-lg-3">
        <a class="d-block footer-logo mt-5" href="<?php echo get_home_url(); ?>" rel="nofollow">
          <img src="<?php echo get_template_directory_uri(); ?>/img/logo-w.svg" width="150" height="45" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>">
        </a>
        <a class="d-block mt-4" href="https://www.trustpilot.com/review/legaladdressinestonia.com" target="_blank" rel="noopener noreferrer">
          <img src="<?php echo get_template_directory_uri(); ?>/img/trustpilot.svg" width="200" height="45" alt="Trustpilot" title="Trustpilot reviews">
        </a>
      </div>
      <div class="col-12 col-sm-6 col-md-6 col-lg-3 mt-5">
        <nav class="menu-footer" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
          <?php
            wp_nav_menu( array(
              'theme_location'  => 'menu-footer',
              'depth'           => 2,
              'container'       => 'div',
              'container_id'    => 'menu-f',
              'container_class' => 'mf_container',
              'menu_class'      => 'menu-footer',
              'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
              'walker'          => new WP_Bootstrap_Navwalker()
            ) );
          ?>
        </nav>
      </div>
      <div class="col-12 col-sm-6 col-md-6 col-lg-3 mt-5">
        <nav class="menu-footer" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
          <?php
            wp_nav_menu( array(
              'theme_location'  => 'menu-footer-2',
              'depth'           => 2,
              'container'       => 'div',
              'container_id'    => 'menu-f',
              'container_class' => 'mf_container',
              'menu_class'      => 'menu-footer',
              'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
              'walker'          => new WP_Bootstrap_Navwalker()
            ) );
          ?>
        </nav>
      </div>
      <div class="col-12 col-sm-12 col-md-12 col-lg-3 mt-5">
        <nav class="menu-social mb-4">
          <?php
            wp_nav_menu( array(
              'theme_location'  => 'menu-soc',
              'depth'           => 2,
              'container'       => 'div',
              'container_id'    => 'menu-fc',
              'container_class' => 'mfc_container',
              'menu_class'      => 'menu-social',
              'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
              'walker'          => new WP_Bootstrap_Navwalker()
            ) );
          ?>
        </nav>

        <?php 
          $licence_link   = get_field('licence-link', 1199);
          $licence_number = get_field('licence-number', 1199);
        ?>
        <?php if ($licence_link && $licence_number): ?>
          <p class="mb-0 txt-white">eBusiness Solutions OÜ · Registry code 16618432 · VAT EE102672239 · Licence <a class="underlined" href="<?php echo esc_url($licence_link); ?>" rel="noopener nofollow" target="_blank"><?php echo esc_html($licence_number); ?></a> · Tornimäe tn 7-169, Tallinn, Estonia, 10145</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="container footer-buttom">
    <div class="row justify-content-center">
      <noindex>
        <div class="col-12 dsg-ai-footer-container mb-5">
            <div id="lai-ai-status-text" class="lai-ai-status mb-4">
              AI Analysis Active: legaladdressinestonia.com
            </div>
            <div class="dsg-ai-footer-row">
                <!-- ChatGPT -->
                <a href="https://chatgpt.com/?q=Company+registration+in+Estonia+guide+https%3A%2F%2Flegaladdressinestonia.com%2F" 
                   onclick="window.open(this.href, '_blank'); return false;"
                   target="_blank" class="dsg-ai-btn" rel="noopener nofollow">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M11.2 18.3c-1.1 0-2.1-.4-2.9-1.2-.4.1-.9.2-1.3.2-1.5 0-2.8-.8-3.6-2.1-.5-.9-.6-1.9-.3-2.8C2.3 11.7 1.7 10.4 1.7 9c0-1.5.8-2.9 2.1-3.6.2-.9.6-1.7 1.4-2.3C5.9 2.4 6.8 2 7.8 1.8c.3-.1.6-.1.9-.1 1.1 0 2.1.4 2.9 1.2.4-.1.9-.2 1.3-.2 1.5 0 2.8.8 3.5 2.1.5.9.6 1.9.4 2.8.9.7 1.5 2 1.5 3.4 0 1.5-.8 2.9-2.1 3.6-.2.9-.6 1.7-1.4 2.3-.7.6-1.6 1-2.6 1.2-.3.1-.6.2-.9.2zm-4-2.1c.9 0 1.6-.4 1.9-1L12.2 14c.2-.1.3-.3.3-.5v-1.2l-4.6 2.4c-.5.2-1 .1-1.3-.2L4 12.8v.3c0 1.1.6 2 1.5 2.5.5.3 1 .4 1.5.4zm.2-2.7l1.2-.7-4-2.3c-.2-.1-.4-.4-.4-.7V6.3c-.8.3-1.5.9-1.9 1.7-.3.6-.4 1.3-.2 1.9.2.7.6 1.3 1.2 1.7l3.1 1.8zm4 3.7c1 0 2-.5 2.6-1.4.4-.6.5-1.3.4-1.9v-3.6c0-.2-.1-.3-.2-.4l-1.3-.7v4.6c0 .3-.1.5-.4.6l-3.1 1.8c.6.4 1.3.7 2 .7zm.6-6L10 7.8 8.1 8.9v2.2l1.9 1.1 1.9-1.1V8.9zM7.1 5.9c0-.3.1-.5.4-.7l3.1-1.8c-.8-.6-1.9-.6-2.8-.1-.7.4-1.2 1.1-1.4 1.9-.1.4-.1.8 0 1.2v3.5c0 .2.1.3.2.4l1.3.7V5.9zm8.3 7.8c.8-.3 1.5-.9 1.9-1.7.3-.6.4-1.3.2-1.9-.2-.7-.6-1.3-1.2-1.7l-3.1-1.8c-.2-.1-.4-.1-.5 0l-1.2.7 4 2.3c.2.1.4.4.4.7v3.5zm-3.3-8.4c.5-.2 1-.1 1.3.2L16 7.2v-.3c0-1.1-.6-2-1.5-2.5-.9-.5-2-.3-2.7.3L8.3 5.9c-.2.1-.2.3-.2.5v1.3l4-2.4z"></path></svg>
                    <span>ChatGPT</span>
                </a>
                <!-- Perplexity -->
                <a href="https://www.perplexity.ai/search?q=Estonia+virtual+office+reviews+https%3A%2F%2Flegaladdressinestonia.com%2F" 
                   onclick="window.open(this.href, '_blank'); return false;"
                   target="_blank" class="dsg-ai-btn" rel="noopener nofollow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M19.3 2.5L12 8.9h7.3V2.5Z"></path><path d="M12 8.9L4.7 2.5v6.4H12Z"></path><path d="M12 1v22"></path><path d="M18.6 14.5L12 8.9v7.8l6.6 6v-8.2Z"></path><path d="M4.7 14.5L12 8.9v7.6l-7.3 6v-8Z"></path><path d="M2 8.9v7.7h2.7v-2.1L12 8.9H2Z"></path><path d="M12 8.9l7.3 5.6v2.1H22V8.9H12Z"></path></svg>
                    <span>Perplexity</span>
                </a>
                <!-- Gemini -->
                <a href="https://gemini.google.com/app?q=Legal+address+in+Estonia+price+https%3A%2F%2Flegaladdressinestonia.com%2F" 
                   onclick="window.open(this.href, '_blank'); return false;"
                   target="_blank" class="dsg-ai-btn" rel="noopener nofollow">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.35 11.1h-9.17v2.73h6.51c-.33 3.81-3.5 5.44-6.5 5.44C8.36 19.27 5 16.25 5 12c0-4.1 3.2-7.27 7.2-7.27 3.09 0 4.9 1.97 4.9 1.97L19 4.72S16.56 2 12.1 2C6.42 2 2.03 6.8 2.03 12c0 5.05 4.13 10 10.22 10 5.35 0 9.25-3.67 9.25-9.09 0-1.15-.15-1.81-.15-1.81Z"></path></svg>
                    <span>Gemini</span>
                </a>
                <!-- Grok -->
                <a href="https://x.com/i/grok?text=Taxes+in+Estonia+for+non-residents+https%3A%2F%2Flegaladdressinestonia.com%2F" 
                   onclick="window.open(this.href, '_blank'); return false;"
                   target="_blank" class="dsg-ai-btn" rel="noopener nofollow">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"></path></svg>
                    <span>Grok</span>
                </a>
                <!-- Claude -->
                <a href="https://claude.ai/" 
                   onclick="window.open(this.href, '_blank'); return false;"
                   target="_blank" class="dsg-ai-btn" rel="noopener nofollow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="3" x2="12" y2="21"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="5.5" y1="5.5" x2="18.5" y2="18.5"></line><line x1="18.5" y1="5.5" x2="5.5" y2="18.5"></line></svg>
                    <span>Claude</span>
                </a>
            </div>
        </div>
      </noindex>
      <div class="col-12 text-center">
        <meta itemprop="copyrightYear" content="?php echo date('Y'); ?>">
        <meta itemprop="copyrightHolder" content="eBusiness Solutions OÜ">
        <p class="mb-0 txt-white">© 2022–<?php echo date('Y'); ?> eBusiness Solutions OÜ</p>
      </div>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
<script src="https://webforms.pipedrive.com/f/loader"></script>
</body>
</html>