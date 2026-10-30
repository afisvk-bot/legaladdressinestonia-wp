<?php get_header(); ?>

<section class="container home-1">
  <div class="row align-items-center justify-content-between">
    <div class="col-md-12 col-lg-7 pl-lg-5">
      <div class="text">
        <h1 class="mt-0 mb-4"><?php the_field('title'); ?></h1>
        <p class="mb-0 txt-grey"><?php the_field('text'); ?></p>
      </div>
    </div>
  </div>
</section>

<section class="container home-2">
  <div class="row justify-content-between mb-5">
    <div class="col-md-12 col-lg-4 mb-4 mb-md-0">
      <div class="packags package-one">
        <div class="h-100">
          <div class="card-body">
            <svg fill="none" height="180" viewBox="0 0 86 180" width="86" xmlns="http://www.w3.org/2000/svg"><path d="m15.4 50.8v-31.8h55.4v146h-35.6v-114.2z" fill="#31d44b"/><path d="m5.4 40.8h-1v1h1zm0-31.8v-1h-1v1zm55.4 0h1v-1h-1zm0 146v1h1v-1zm-35.6 0h-1v1h1zm0-114.2h1v-1h-1zm-18.8 0v-31.8h-2v31.8zm-1-30.8h55.4v-2h-55.4zm54.4-1v146h2v-146zm1 145h-35.6v2h35.6zm-34.6 1v-114.2h-2v114.2zm-1-115.2h-19.8v2h19.8z" fill="#000"/></svg>
            <h3 class="mt-0 mb-3"><?php the_field('name_1'); ?></h3>
            <p class="txt-grey mb-0"><?php the_field('desc_1'); ?></p>
            <div class="mt-5 mb-4 price"><span class="from"><?php the_field('price_1_from'); ?></span> <?php the_field('price_1'); ?>&nbsp;&#8364;</div>
            <ul class="list-checked">
              <?php the_field('package_items_1'); ?>
            </ul>
            <div class="txt-grey mb-5 fs-xs">
              <?php the_field('*1'); ?>
            </div>
            <a href="#" data-toggle="modal" data-target="<?php the_field('form_id_class'); ?>" class="btn"><?php the_field('anchor_links_9'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12 col-lg-4 mb-4 mb-md-0">
      <div class="packags package-two">
        <div class="h-100">
          <div class="card-body">
            <div class="top">Top</div>
            <svg fill="none" height="180" viewBox="0 0 125 180" width="125" xmlns="http://www.w3.org/2000/svg"><path d="m17.8 137.6c4.5333-3.6 6.6-5.267 6.2-5 13.0667-10.8 23.3333-19.667 30.8-26.6 7.6-6.9333 14-14.2 19.2-21.8s7.8-15 7.8-22.2c0-5.4667-1.2667-9.7333-3.8-12.8s-6.3333-4.6-11.4-4.6-9.0667 1.9333-12 5.8c-2.8 3.7333-4.2 9.0667-4.2 16h-33c.2667-11.3333 2.6667-20.8 7.2-28.4 4.6667-7.6 10.7333-13.2 18.2-16.8 7.6-3.6 16-5.4 25.2-5.4 15.8667 0 27.8 4.0667 35.8 12.2 8.133 8.1333 12.2 18.7333 12.2 31.8 0 14.2667-4.867 27.5333-14.6 39.8-9.7333 12.133-22.1333 24-37.2 35.6h54v27.8h-100.4z" fill="#31d44b"/><path d="m7.8 127.6-.62188-.783-.37812.3v.483zm6.2-5-.6371-.771 1.1918 1.603zm30.8-26.6-.674-.7388-.0064.006zm19.2-21.8-.8253-.5647zm4-35-.771.6369zm-23.4 1.2-.7967-.6044-.0033.0044zm-4.2 16v1h1v-1zm-33 0-.99972-.0235-.02409 1.0235h1.02381zm7.2-28.4-.8522-.5233-.0066.011zm18.2-16.8-.4281-.9038-.0062.003zm61 6.8-.7129.7013.0058.0058zm-2.4 71.6.78.6257.0034-.0041zm-37.2 35.6-.61-.792-2.3281 1.792h2.9381zm54 0h1v-1h-1zm0 27.8v1h1v-1zm-100.4 0h-1v1h1zm.62188-24.617c2.26692-1.8 3.91942-3.119 4.95592-3.955.5166-.416.8861-.718 1.1029-.9.0945-.079.1967-.166.2608-.23.0005-.001.077-.074.1436-.176.0193-.029.0653-.102.1027-.205.0226-.062.1331-.366-.0145-.725-.1945-.472-.6242-.595-.8279-.615-.1814-.018-.3212.02-.3624.031-.1028.028-.1786.067-.1946.075-.0298.015-.0539.029-.0683.038-.0295.017-.0561.034-.0748.047l1.1094 1.664c.0063-.004-.0001 0-.0142.009-.0067.004-.0244.014-.0489.026-.0107.006-.0824.043-.1824.07-.0397.011-.1785.048-.359.031-.2029-.02-.6321-.143-.8263-.614-.1474-.358-.037-.662-.0148-.723.0371-.101.0824-.173.1007-.201.0405-.062.0785-.105.0872-.114.0145-.017.0258-.028.0303-.032.0099-.01-.0191.017-.133.113-.1998.168-.5553.458-1.0721.875-1.0302.83-2.67765 2.145-4.94408 3.945zm6.21522-5.012c13.0732-10.806 23.3565-19.686 30.8434-26.6382l-1.3609-1.4656c-7.4465 6.9148-17.6965 15.7678-30.7567 26.5618zm30.8369-26.6322c7.6504-6.9794 14.103-14.3035 19.3513-21.9741l-1.6506-1.1294c-5.1517 7.5294-11.4991 14.7386-19.0487 21.6259zm19.3513-21.9741c5.2822-7.7201 7.9747-15.3136 7.9747-22.7647h-2c0 6.9489-2.5075 14.1554-7.6253 21.6353zm7.9747-22.7647c0-5.6126-1.3006-10.134-4.029-13.4369l-1.542 1.2738c2.3383 2.8305 3.571 6.8424 3.571 12.1631zm-4.029-13.4369c-2.7733-3.3571-6.8945-4.9631-12.171-4.9631v2c4.8569 0 8.3356 1.4606 10.629 4.2369zm-12.171-4.9631c-5.3634 0-9.6648 2.0672-12.7967 6.1956l1.5934 1.2088c2.7348-3.6049 6.4334-5.4044 11.2033-5.4044zm-12.8 6.2c-2.9793 3.9724-4.4 9.5506-4.4 16.6h2c0-6.8172 1.3793-11.9057 4-15.4zm-3.4 15.6h-33v2h33zm-32.00028 1.0235c.26373-11.2084 2.63558-20.4953 7.05908-27.9112l-1.7176-1.0246c-4.64313 7.7841-7.07132 17.4305-7.34092 28.8888zm7.05248-27.9002c4.5743-7.4497 10.5012-12.9121 17.7821-16.4225l-.8686-1.8016c-7.6524 3.6896-13.8589 9.4272-18.6179 17.1775zm17.7759-16.4196c7.454-3.53082 15.7059-5.30371 24.7719-5.30371v-2c-9.334 0-17.8821 1.82711-25.6281 5.49631zm24.7719-5.30371c15.7054 0 27.3387 4.02371 35.0871 11.90121l1.4258-1.4025c-8.2516-8.38909-20.485-12.49871-36.5129-12.49871zm35.0929 11.90711c7.9201 7.9197 11.9071 18.2522 11.9071 31.0929h2c0-13.2926-4.146-24.1602-12.4929-32.5071zm11.9071 31.0929c0 14.0186-4.776 27.0702-14.3834 39.1784l1.5668 1.2432c9.8586-12.4251 14.8166-25.9069 14.8166-40.4216zm-14.38 39.1743c-9.674 12.0597-22.0131 23.8717-37.03 35.4337l1.2201 1.584c15.1163-11.638 27.5772-23.559 37.3699-35.7663zm-36.42 37.2257h54v-2h-54zm53-1v27.8h2v-27.8zm1 26.8h-100.4v2h100.4zm-99.4 1v-25.4h-2v25.4z" fill="#000"/></svg>
            <h3 class="mt-0 mb-3"><?php the_field('name_2'); ?></h3>
            <p class="txt-green mb-2"><?php the_field('green_desc'); ?></p>
            <p class="txt-grey mb-0"><?php the_field('desc_2'); ?></p>
            <div class="mt-5 mb-4 price"><span class="from"><?php the_field('price_2_from'); ?></span> <?php the_field('price_2'); ?>&nbsp;&#8364;</div>
            <ul class="list-checked">
              <?php the_field('package_items_2'); ?>
            </ul>
            <div class="txt-grey mb-5 fs-xs">
              <?php the_field('*2'); ?>
            </div>
            <a href="#" data-toggle="modal" data-target="<?php the_field('form_id_class'); ?>" class="btn green"><?php the_field('anchor_links_9'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12 col-lg-4 mb-4 mb-md-0">
      <div class="packags package-tree">
        <div class="h-100">
          <div class="card-body">
            <svg fill="none" height="180" viewBox="0 0 132 180" width="132" xmlns="http://www.w3.org/2000/svg"><path d="m20.2 60.2c.5333-14.2667 5.2-25.2667 14-33s20.7333-11.6 35.8-11.6c10 0 18.5333 1.7333 25.6 5.2 7.2 3.4667 12.6 8.2 16.2 14.2 3.733 6 5.6 12.7333 5.6 20.2 0 8.8-2.2 16-6.6 21.6-4.4 5.4667-9.533 9.2-15.4 11.2v.8c7.6 2.5333 13.6 6.7333 18 12.6 4.4 5.867 6.6 13.4 6.6 22.6 0 8.267-1.933 15.6-5.8 22-3.733 6.267-9.267 11.2-16.6 14.8-7.2 3.6-15.8 5.4-25.8 5.4-16 0-28.8-3.933-38.4-11.8-9.4667-7.867-14.4667-19.733-15-35.6h33.2c.1333 5.867 1.8 10.533 5 14 3.2 3.333 7.8667 5 14 5 5.2 0 9.2-1.467 12-4.4 2.9333-3.067 4.4-7.067 4.4-12 0-6.4-2.0667-11-6.2-13.8-4-2.933-10.4667-4.4-19.4-4.4h-6.4v-27.8h6.4c6.8 0 12.2667-1.1333 16.4-3.4 4.2667-2.4 6.4-6.6 6.4-12.6 0-4.8-1.3333-8.5333-4-11.2s-6.3333-4-11-4c-5.0667 0-8.8667 1.5333-11.4 4.6-2.4 3.0667-3.8 6.8667-4.2 11.4z" fill="#31d44b"/><path d="m10.2 50.2-.9993-.0374-.03878 1.0374h1.03808zm14-33 .6601.7512zm61.4-6.4-.4404.8978.0066.0032zm16.2 14.2-.857.5145.004.0069.004.0069zm-1 41.8.779.627.007-.0092zm-15.4 11.2-.3227-.9465-.6773.2309v.7156zm0 .8h-1v.7208l.6838.2279zm18 12.6.8-.6zm.8 44.6-.856-.517-.003.005zm-16.6 14.8-.4407-.898-.0065.004zm-64.2-6.4-.6391.769.0053.004zm-15-35.6v-1h-1.03418l.03475 1.034zm33.2 0 .9997-.023-.0222-.977h-.9775zm5 14-.7348.678.0066.007.0068.008zm26 .6-.7226-.691-.0008.001zm-1.8-25.8-.5914.8064.0151.011.0155.0105zm-25.8-4.4h-1v1h1zm0-27.8v-1h-1v1zm22.8-3.4.4809.8769.0094-.0053zm2.4-23.8.7071-.7071zm-22.4.6-.771-.6369-.0084.0102-.0081.0104zm-4.2 11.4v1h.9157l.0804-.9121zm-32.4007.0373c.5255-14.0567 5.111-24.7727 13.6608-32.2861l-1.3202-1.5024c-9.0502 7.9532-13.79802 19.2372-14.3392 33.7138zm13.6608-32.2861c8.5702-7.5314 20.2449-11.35121 35.1399-11.35121v-2c-15.2383 0-27.4303 3.91357-36.4601 11.84881zm35.1399-11.35121c9.8874 0 18.2619 1.71404 25.1596 5.09781l.8808-1.79559c-7.2357-3.54959-15.9278-5.30222-26.0404-5.30222zm25.1662 5.10101c7.0498 3.3943 12.2905 8.0038 15.7768 13.8135l1.714-1.029c-3.7137-6.1903-9.273-11.0475-16.6232-14.58651zm15.7848 13.8273c3.629 5.8323 5.449 12.3807 5.449 19.6717h2c0-7.6424-1.913-14.5606-5.751-20.7283zm5.449 19.6717c0 8.63-2.155 15.5966-6.386 20.9822l1.572 1.2356c4.569-5.8145 6.814-13.2478 6.814-22.2178zm-6.379 20.973c-4.2974 5.3391-9.2792 8.9494-14.9437 10.8805l.6454 1.893c6.0688-2.0689 11.3537-5.9253 15.8563-11.5195zm-15.621 11.827v.8h2v-.8zm.6838 1.7487c7.4257 2.4752 13.2496 6.5624 17.5162 12.2513l1.6-1.2c-4.5333-6.0445-10.7095-10.3573-18.4838-12.9487zm17.5162 12.2513c4.24 5.6537 6.4 12.961 6.4 22h2c0-9.361-2.24-17.1203-6.8-23.2zm6.4 22c0 8.104-1.893 15.255-5.656 21.483l1.712 1.034c3.971-6.572 5.944-14.088 5.944-22.517zm-5.659 21.488c-3.6227 6.081-9.0007 10.889-16.1817 14.414l.8814 1.796c7.4857-3.675 13.1743-8.734 17.0183-15.186zm-16.1882 14.418c-7.0316 3.515-15.4712 5.294-25.3528 5.294v2c10.1184 0 18.8788-1.821 26.2472-5.506zm-25.3528 5.294c-15.8368 0-28.3912-3.891-37.7662-11.573l-1.2676 1.546c9.825 8.052 22.8706 12.027 39.0338 12.027zm-37.7609-11.569c-9.1873-7.635-14.11296-19.195-14.63966-34.865l-1.99887.068c.53996 16.064 5.61433 28.236 15.36033 36.335zm-15.6391-33.831h33.2v-2h-33.2zm32.2003-.977c.1374 6.048 1.8622 10.969 5.2649 14.655l1.4696-1.356c-2.9973-3.247-4.6059-7.66-4.7351-13.345zm5.2783 14.67c3.4444 3.587 8.4086 5.307 14.7214 5.307v-2c-5.9539 0-10.323-1.614-13.2786-4.693zm14.7214 5.307c5.3919 0 9.685-1.526 12.7234-4.71l-1.4468-1.38c-2.5616 2.683-6.2685 4.09-11.2766 4.09zm12.7226-4.709c3.1402-3.283 4.6774-7.545 4.6774-12.691h-2c0 4.721-1.3961 8.458-4.1226 11.309zm4.6774-12.691c0-6.627-2.1545-11.5899-6.6392-14.6279l-1.1216 1.6558c3.782 2.5621 5.7608 6.7991 5.7608 12.9721zm-6.6086-14.6064c-4.262-3.1254-10.9948-4.5936-19.9914-4.5936v2c8.8701 0 15.0706 1.4651 18.8086 4.2064zm-19.9914-4.5936h-6.4v2h6.4zm-5.4 1v-27.8h-2v27.8zm-1-26.8h6.4v-2h-6.4zm6.4 0c6.8985 0 12.5492-1.1478 16.8808-3.5232l-.9616-1.7536c-3.9351 2.1579-9.2177 3.2768-15.9192 3.2768zm16.8903-3.5284c4.6572-2.6197 6.9097-7.2064 6.9097-13.4716h-2c0 5.7348-2.0141 9.5481-5.8903 11.7284zm6.9097-13.4716c0-4.9893-1.3911-9.0053-4.2929-11.9071l-1.4142 1.4142c2.4315 2.4315 3.7071 5.8822 3.7071 10.4929zm-4.2929-11.9071c-2.8966-2.8967-6.842-4.2929-11.7071-4.2929v2c4.4682 0 7.8562 1.2704 10.2929 3.7071zm-11.7071-4.2929c-5.2765 0-9.3977 1.606-12.171 4.9631l1.542 1.2738c2.2934-2.7763 5.7722-4.2369 10.629-4.2369zm-12.1875 4.9837c-2.5381 3.2432-3.9946 7.2356-4.4086 11.9284l1.9922.1758c.386-4.3739 1.7295-7.9814 3.9914-10.8716zm-3.4125 11.0163h-33.4v2h33.4z" fill="#000"/></svg>
            <h3 class="mt-0 mb-3"><?php the_field('name_3'); ?></h3>
            <p class="txt-grey mb-0"><?php the_field('desc_3'); ?></p>
            <div class="mt-5 mb-4 price"><span class="from"><?php the_field('price_3_from'); ?></span> <?php the_field('price_3'); ?>&nbsp;&#8364;</div>
            <ul class="list-checked">
              <?php the_field('package_items_3'); ?>
            </ul>
            <div class="txt-grey mb-5 fs-xs"><?php the_field('*3'); ?></div>
            <a href="#" data-toggle="modal" data-target="<?php the_field('form_id_class'); ?>" class="btn"><?php the_field('anchor_links_9'); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container home-3 mb-0">
  <div class="row align-items-center justify-content-center w-100 mb-5">
    <div class="col-md-12 col-lg-9">
      <h2 class="h1 mt-0 mb-5 text-center"><?php the_field('title_3'); ?></h2>
    </div>
    <div class="col-md-12 col-lg-5 order-2 order-lg-1">
      <div class="steps mt-3 mt-md-5">
        <div class="step-number">
          <div class="step-number-inner">1</div>
          <div class="step-txt"><?php the_field('point_1'); ?></div>
        </div>
        <div class="step-number">
          <div class="step-number-inner">2</div>
          <div class="step-txt"><?php the_field('point_2'); ?></div>
        </div>
        <div class="step-number">
          <div class="step-number-inner">3</div>
          <div class="step-txt"><?php the_field('point_3'); ?></div>
        </div>
        <div class="step-number">
          <div class="step-number-inner">4</div>
          <div class="step-txt"><?php the_field('point_4'); ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-12 col-lg-5 order-1 order-lg-2">
      <img width="656" height="540" src="<?php echo get_template_directory_uri(); ?>/img/home-3.webp" class="lazyload" alt="><?php the_field('title_3'); ?>">
    </div>
  </div>
  <div class="row align-items-center justify-content-center">
    <div class="col-md-12 col-lg-9 mt-3 mt-lg-5">
      <div class="txt-grey mb-5 mb-sm-4 mb-md-4 fs-xs text-center">
        <?php the_field('help'); ?>
      </div>
    </div>
  </div>
</section>

<!-- Calendly inline widget begin -->
<?php get_template_part('template-parts/content', 'consultation'); ?>
<!-- Calendly inline widget end -->


<div class="container">
  <div class="row justify-content-center mb-4 ">
    <div class="col-md-12 col-lg-9">
      <h2 class="h1 mt-0 mb-0 mb-md-5 text-center">
        <?php $l = substr(get_locale(), 0, 2);
          if ($l =='en') {
            echo "Company Registration Packages";
          } elseif ($l =='ru') {
            echo "Пакеты регистрации компании";
          } elseif ($l =='uk') {
            echo "Пакети реєстрації компанії";
          } elseif ($l =='de') {
            echo "Firmenregistrierungspakete";
          } elseif ($l =='fr') {
            echo "Paquets d'enregistrement d'entreprise";
          } elseif ($l =='es') {
            echo "Paquetes de registro de empresas";
          } elseif ($l =='zh') {
            echo "公司注册套餐";
          } elseif ($l =='pt') {
            echo "Pacotes de Registo de Empresa";
          } elseif ($l =='tr') {
            echo "Şirket Kayıt Paketleri";
          }
        ?>
      </h2>
    </div>
  </div>
</div>

<section class="container home-3-2">
  <div class="row justify-content-between mb-5">
    <div class="col-md-12 col-lg-4 mb-4 mb-md-0">
      <div class="packags package-one">
        <div class="h-100">
          <div class="card-body">
            <svg fill="none" height="180" viewBox="0 0 86 180" width="86" xmlns="http://www.w3.org/2000/svg"><path d="m15.4 50.8v-31.8h55.4v146h-35.6v-114.2z" fill="#31d44b"/><path d="m5.4 40.8h-1v1h1zm0-31.8v-1h-1v1zm55.4 0h1v-1h-1zm0 146v1h1v-1zm-35.6 0h-1v1h1zm0-114.2h1v-1h-1zm-18.8 0v-31.8h-2v31.8zm-1-30.8h55.4v-2h-55.4zm54.4-1v146h2v-146zm1 145h-35.6v2h35.6zm-34.6 1v-114.2h-2v114.2zm-1-115.2h-19.8v2h19.8z" fill="#000"/></svg>
            <h3 class="mt-0 mb-3"><?php the_field('desc_other_1'); ?></h3>
            <div class="mt-5 mb-4 price"><span class="from"><?php the_field('price_1_from'); ?></span> <?php the_field('price_other_1'); ?>&nbsp;&#8364;</div>
            <ul class="list-checked">
              <?php the_field('package_items_other_1'); ?>
            </ul>
            <div class="txt-grey mb-5 fs-xs">
              <?php the_field('*1'); ?>
            </div>
            <a href="#" data-toggle="modal" data-target="<?php the_field('form_id_class'); ?>" class="btn"><?php the_field('anchor_links_9'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12 col-lg-4 mb-4 mb-md-0">
      <div class="packags package-two">
        <div class="h-100">
          <div class="card-body">
            <div class="top">Top</div>
            <svg fill="none" height="180" viewBox="0 0 125 180" width="125" xmlns="http://www.w3.org/2000/svg"><path d="m17.8 137.6c4.5333-3.6 6.6-5.267 6.2-5 13.0667-10.8 23.3333-19.667 30.8-26.6 7.6-6.9333 14-14.2 19.2-21.8s7.8-15 7.8-22.2c0-5.4667-1.2667-9.7333-3.8-12.8s-6.3333-4.6-11.4-4.6-9.0667 1.9333-12 5.8c-2.8 3.7333-4.2 9.0667-4.2 16h-33c.2667-11.3333 2.6667-20.8 7.2-28.4 4.6667-7.6 10.7333-13.2 18.2-16.8 7.6-3.6 16-5.4 25.2-5.4 15.8667 0 27.8 4.0667 35.8 12.2 8.133 8.1333 12.2 18.7333 12.2 31.8 0 14.2667-4.867 27.5333-14.6 39.8-9.7333 12.133-22.1333 24-37.2 35.6h54v27.8h-100.4z" fill="#31d44b"/><path d="m7.8 127.6-.62188-.783-.37812.3v.483zm6.2-5-.6371-.771 1.1918 1.603zm30.8-26.6-.674-.7388-.0064.006zm19.2-21.8-.8253-.5647zm4-35-.771.6369zm-23.4 1.2-.7967-.6044-.0033.0044zm-4.2 16v1h1v-1zm-33 0-.99972-.0235-.02409 1.0235h1.02381zm7.2-28.4-.8522-.5233-.0066.011zm18.2-16.8-.4281-.9038-.0062.003zm61 6.8-.7129.7013.0058.0058zm-2.4 71.6.78.6257.0034-.0041zm-37.2 35.6-.61-.792-2.3281 1.792h2.9381zm54 0h1v-1h-1zm0 27.8v1h1v-1zm-100.4 0h-1v1h1zm.62188-24.617c2.26692-1.8 3.91942-3.119 4.95592-3.955.5166-.416.8861-.718 1.1029-.9.0945-.079.1967-.166.2608-.23.0005-.001.077-.074.1436-.176.0193-.029.0653-.102.1027-.205.0226-.062.1331-.366-.0145-.725-.1945-.472-.6242-.595-.8279-.615-.1814-.018-.3212.02-.3624.031-.1028.028-.1786.067-.1946.075-.0298.015-.0539.029-.0683.038-.0295.017-.0561.034-.0748.047l1.1094 1.664c.0063-.004-.0001 0-.0142.009-.0067.004-.0244.014-.0489.026-.0107.006-.0824.043-.1824.07-.0397.011-.1785.048-.359.031-.2029-.02-.6321-.143-.8263-.614-.1474-.358-.037-.662-.0148-.723.0371-.101.0824-.173.1007-.201.0405-.062.0785-.105.0872-.114.0145-.017.0258-.028.0303-.032.0099-.01-.0191.017-.133.113-.1998.168-.5553.458-1.0721.875-1.0302.83-2.67765 2.145-4.94408 3.945zm6.21522-5.012c13.0732-10.806 23.3565-19.686 30.8434-26.6382l-1.3609-1.4656c-7.4465 6.9148-17.6965 15.7678-30.7567 26.5618zm30.8369-26.6322c7.6504-6.9794 14.103-14.3035 19.3513-21.9741l-1.6506-1.1294c-5.1517 7.5294-11.4991 14.7386-19.0487 21.6259zm19.3513-21.9741c5.2822-7.7201 7.9747-15.3136 7.9747-22.7647h-2c0 6.9489-2.5075 14.1554-7.6253 21.6353zm7.9747-22.7647c0-5.6126-1.3006-10.134-4.029-13.4369l-1.542 1.2738c2.3383 2.8305 3.571 6.8424 3.571 12.1631zm-4.029-13.4369c-2.7733-3.3571-6.8945-4.9631-12.171-4.9631v2c4.8569 0 8.3356 1.4606 10.629 4.2369zm-12.171-4.9631c-5.3634 0-9.6648 2.0672-12.7967 6.1956l1.5934 1.2088c2.7348-3.6049 6.4334-5.4044 11.2033-5.4044zm-12.8 6.2c-2.9793 3.9724-4.4 9.5506-4.4 16.6h2c0-6.8172 1.3793-11.9057 4-15.4zm-3.4 15.6h-33v2h33zm-32.00028 1.0235c.26373-11.2084 2.63558-20.4953 7.05908-27.9112l-1.7176-1.0246c-4.64313 7.7841-7.07132 17.4305-7.34092 28.8888zm7.05248-27.9002c4.5743-7.4497 10.5012-12.9121 17.7821-16.4225l-.8686-1.8016c-7.6524 3.6896-13.8589 9.4272-18.6179 17.1775zm17.7759-16.4196c7.454-3.53082 15.7059-5.30371 24.7719-5.30371v-2c-9.334 0-17.8821 1.82711-25.6281 5.49631zm24.7719-5.30371c15.7054 0 27.3387 4.02371 35.0871 11.90121l1.4258-1.4025c-8.2516-8.38909-20.485-12.49871-36.5129-12.49871zm35.0929 11.90711c7.9201 7.9197 11.9071 18.2522 11.9071 31.0929h2c0-13.2926-4.146-24.1602-12.4929-32.5071zm11.9071 31.0929c0 14.0186-4.776 27.0702-14.3834 39.1784l1.5668 1.2432c9.8586-12.4251 14.8166-25.9069 14.8166-40.4216zm-14.38 39.1743c-9.674 12.0597-22.0131 23.8717-37.03 35.4337l1.2201 1.584c15.1163-11.638 27.5772-23.559 37.3699-35.7663zm-36.42 37.2257h54v-2h-54zm53-1v27.8h2v-27.8zm1 26.8h-100.4v2h100.4zm-99.4 1v-25.4h-2v25.4z" fill="#000"/></svg>
            <h3 class="mt-0 mb-3"><?php the_field('desc_other_2'); ?></h3>
            <div class="mt-5 mb-4 price"><span class="from"><?php the_field('price_2_from'); ?></span> <?php the_field('price_other_2'); ?>&nbsp;&#8364;</div>
            <ul class="list-checked">
              <?php the_field('package_items_other_2'); ?>
            </ul>
            <div class="txt-grey mb-5 fs-xs">
              <?php the_field('*2'); ?>
            </div>
            <a href="#" data-toggle="modal" data-target="<?php the_field('form_id_class'); ?>" class="btn green"><?php the_field('anchor_links_9'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12 col-lg-4 mb-4 mb-md-0">
      <div class="packags package-tree">
        <div class="h-100">
          <div class="card-body">
            <svg fill="none" height="180" viewBox="0 0 132 180" width="132" xmlns="http://www.w3.org/2000/svg"><path d="m20.2 60.2c.5333-14.2667 5.2-25.2667 14-33s20.7333-11.6 35.8-11.6c10 0 18.5333 1.7333 25.6 5.2 7.2 3.4667 12.6 8.2 16.2 14.2 3.733 6 5.6 12.7333 5.6 20.2 0 8.8-2.2 16-6.6 21.6-4.4 5.4667-9.533 9.2-15.4 11.2v.8c7.6 2.5333 13.6 6.7333 18 12.6 4.4 5.867 6.6 13.4 6.6 22.6 0 8.267-1.933 15.6-5.8 22-3.733 6.267-9.267 11.2-16.6 14.8-7.2 3.6-15.8 5.4-25.8 5.4-16 0-28.8-3.933-38.4-11.8-9.4667-7.867-14.4667-19.733-15-35.6h33.2c.1333 5.867 1.8 10.533 5 14 3.2 3.333 7.8667 5 14 5 5.2 0 9.2-1.467 12-4.4 2.9333-3.067 4.4-7.067 4.4-12 0-6.4-2.0667-11-6.2-13.8-4-2.933-10.4667-4.4-19.4-4.4h-6.4v-27.8h6.4c6.8 0 12.2667-1.1333 16.4-3.4 4.2667-2.4 6.4-6.6 6.4-12.6 0-4.8-1.3333-8.5333-4-11.2s-6.3333-4-11-4c-5.0667 0-8.8667 1.5333-11.4 4.6-2.4 3.0667-3.8 6.8667-4.2 11.4z" fill="#31d44b"/><path d="m10.2 50.2-.9993-.0374-.03878 1.0374h1.03808zm14-33 .6601.7512zm61.4-6.4-.4404.8978.0066.0032zm16.2 14.2-.857.5145.004.0069.004.0069zm-1 41.8.779.627.007-.0092zm-15.4 11.2-.3227-.9465-.6773.2309v.7156zm0 .8h-1v.7208l.6838.2279zm18 12.6.8-.6zm.8 44.6-.856-.517-.003.005zm-16.6 14.8-.4407-.898-.0065.004zm-64.2-6.4-.6391.769.0053.004zm-15-35.6v-1h-1.03418l.03475 1.034zm33.2 0 .9997-.023-.0222-.977h-.9775zm5 14-.7348.678.0066.007.0068.008zm26 .6-.7226-.691-.0008.001zm-1.8-25.8-.5914.8064.0151.011.0155.0105zm-25.8-4.4h-1v1h1zm0-27.8v-1h-1v1zm22.8-3.4.4809.8769.0094-.0053zm2.4-23.8.7071-.7071zm-22.4.6-.771-.6369-.0084.0102-.0081.0104zm-4.2 11.4v1h.9157l.0804-.9121zm-32.4007.0373c.5255-14.0567 5.111-24.7727 13.6608-32.2861l-1.3202-1.5024c-9.0502 7.9532-13.79802 19.2372-14.3392 33.7138zm13.6608-32.2861c8.5702-7.5314 20.2449-11.35121 35.1399-11.35121v-2c-15.2383 0-27.4303 3.91357-36.4601 11.84881zm35.1399-11.35121c9.8874 0 18.2619 1.71404 25.1596 5.09781l.8808-1.79559c-7.2357-3.54959-15.9278-5.30222-26.0404-5.30222zm25.1662 5.10101c7.0498 3.3943 12.2905 8.0038 15.7768 13.8135l1.714-1.029c-3.7137-6.1903-9.273-11.0475-16.6232-14.58651zm15.7848 13.8273c3.629 5.8323 5.449 12.3807 5.449 19.6717h2c0-7.6424-1.913-14.5606-5.751-20.7283zm5.449 19.6717c0 8.63-2.155 15.5966-6.386 20.9822l1.572 1.2356c4.569-5.8145 6.814-13.2478 6.814-22.2178zm-6.379 20.973c-4.2974 5.3391-9.2792 8.9494-14.9437 10.8805l.6454 1.893c6.0688-2.0689 11.3537-5.9253 15.8563-11.5195zm-15.621 11.827v.8h2v-.8zm.6838 1.7487c7.4257 2.4752 13.2496 6.5624 17.5162 12.2513l1.6-1.2c-4.5333-6.0445-10.7095-10.3573-18.4838-12.9487zm17.5162 12.2513c4.24 5.6537 6.4 12.961 6.4 22h2c0-9.361-2.24-17.1203-6.8-23.2zm6.4 22c0 8.104-1.893 15.255-5.656 21.483l1.712 1.034c3.971-6.572 5.944-14.088 5.944-22.517zm-5.659 21.488c-3.6227 6.081-9.0007 10.889-16.1817 14.414l.8814 1.796c7.4857-3.675 13.1743-8.734 17.0183-15.186zm-16.1882 14.418c-7.0316 3.515-15.4712 5.294-25.3528 5.294v2c10.1184 0 18.8788-1.821 26.2472-5.506zm-25.3528 5.294c-15.8368 0-28.3912-3.891-37.7662-11.573l-1.2676 1.546c9.825 8.052 22.8706 12.027 39.0338 12.027zm-37.7609-11.569c-9.1873-7.635-14.11296-19.195-14.63966-34.865l-1.99887.068c.53996 16.064 5.61433 28.236 15.36033 36.335zm-15.6391-33.831h33.2v-2h-33.2zm32.2003-.977c.1374 6.048 1.8622 10.969 5.2649 14.655l1.4696-1.356c-2.9973-3.247-4.6059-7.66-4.7351-13.345zm5.2783 14.67c3.4444 3.587 8.4086 5.307 14.7214 5.307v-2c-5.9539 0-10.323-1.614-13.2786-4.693zm14.7214 5.307c5.3919 0 9.685-1.526 12.7234-4.71l-1.4468-1.38c-2.5616 2.683-6.2685 4.09-11.2766 4.09zm12.7226-4.709c3.1402-3.283 4.6774-7.545 4.6774-12.691h-2c0 4.721-1.3961 8.458-4.1226 11.309zm4.6774-12.691c0-6.627-2.1545-11.5899-6.6392-14.6279l-1.1216 1.6558c3.782 2.5621 5.7608 6.7991 5.7608 12.9721zm-6.6086-14.6064c-4.262-3.1254-10.9948-4.5936-19.9914-4.5936v2c8.8701 0 15.0706 1.4651 18.8086 4.2064zm-19.9914-4.5936h-6.4v2h6.4zm-5.4 1v-27.8h-2v27.8zm-1-26.8h6.4v-2h-6.4zm6.4 0c6.8985 0 12.5492-1.1478 16.8808-3.5232l-.9616-1.7536c-3.9351 2.1579-9.2177 3.2768-15.9192 3.2768zm16.8903-3.5284c4.6572-2.6197 6.9097-7.2064 6.9097-13.4716h-2c0 5.7348-2.0141 9.5481-5.8903 11.7284zm6.9097-13.4716c0-4.9893-1.3911-9.0053-4.2929-11.9071l-1.4142 1.4142c2.4315 2.4315 3.7071 5.8822 3.7071 10.4929zm-4.2929-11.9071c-2.8966-2.8967-6.842-4.2929-11.7071-4.2929v2c4.4682 0 7.8562 1.2704 10.2929 3.7071zm-11.7071-4.2929c-5.2765 0-9.3977 1.606-12.171 4.9631l1.542 1.2738c2.2934-2.7763 5.7722-4.2369 10.629-4.2369zm-12.1875 4.9837c-2.5381 3.2432-3.9946 7.2356-4.4086 11.9284l1.9922.1758c.386-4.3739 1.7295-7.9814 3.9914-10.8716zm-3.4125 11.0163h-33.4v2h33.4z" fill="#000"/></svg>
            <h3 class="mt-0 mb-3"><?php the_field('desc_other_3'); ?></h3>
            <div class="mt-5 mb-4 price"><span class="from"><?php the_field('price_3_from'); ?></span> <?php the_field('price_other_3'); ?>&nbsp;&#8364;</div>
            <ul class="list-checked">
              <?php the_field('package_items_other_3'); ?>
            </ul>
            <div class="txt-grey mb-5 fs-xs"><?php the_field('*3'); ?></div>
            <a href="#" data-toggle="modal" data-target="<?php the_field('form_id_class'); ?>" class="btn"><?php the_field('anchor_links_9'); ?></a>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<section class="container home-4">
  <div class="row align-items-center justify-content-center">
    <div class="col-md-12 col-lg-12 col-xl-10">
      <h2 class="h1 mt-0 mb-4 text-center"><?php the_field('title_4'); ?></h2>
    </div>
    <div class="col-md-12 col-lg-12 col-xl-10">
      <p class="txt-grey mb-4 text-center"><?php the_field('sub_title_4'); ?></p>
    </div>
    <div class="col-md-12 col-lg-10 mt-5">
      <div class="ser-logos w-100">
        <img width="212" height="58" src="<?php echo get_template_directory_uri(); ?>/img/ser-logo-1.svg" class="lazyload" alt="Partner service">
        <img width="144" height="58" src="<?php echo get_template_directory_uri(); ?>/img/ser-logo-2.svg" class="lazyload" alt="Partner service">
        <img width="45" height="58" src="<?php echo get_template_directory_uri(); ?>/img/ser-logo-3.svg" class="lazyload" alt="Partner service">
        <img width="220" height="58" src="<?php echo get_template_directory_uri(); ?>/img/ser-logo-4.svg" class="lazyload" alt="Partner service">
        <img width="148" height="58" src="<?php echo get_template_directory_uri(); ?>/img/ser-logo-5.svg" class="lazyload" alt="Partner service">
      </div>
    </div>
  </div>
</section>


<section class="container home-5 px-0 px-md-5">
  <div class="row align-items-center justify-content-center">
    <div class="col-md-12 col-lg-4 text-center">
      <img  src="<?php echo get_template_directory_uri(); ?>/img/home-5.webp" class="lazyload" width="388" height="388" alt="<?php the_field('title_5'); ?>">
    </div>
    <div class="col-md-12 col-lg-7 px-5 px-5 px-md-5 mt-4 mt-lg-0">
      <h2 class="h1 mt-0 mb-4">
        <?php the_field('title_5'); ?>
      </h2>
      <?php the_field('text_5'); ?>
    </div>
  </div>
</section>


<section class="container home-6 px-0 px-md-5">
  <div class="row align-items-center justify-content-center">
    <div class="col-md-12 col-lg-4 text-center order-1 order-lg-2">
      <img src="<?php echo get_template_directory_uri(); ?>/img/home-6.webp" class="lazyload" width="388" height="388" alt="<?php the_field('title_6'); ?>">
    </div>
    <div class="col-md-12 col-lg-7 order-2 order-md-1 px-5 px-md-5 mt-4 mt-lg-0">
      <h2 class="h1 mt-0 mb-4"><?php the_field('title_6'); ?></h2>
      <?php the_field('text_6'); ?>
    </div>
  </div>
</section>  


<section class="container home-7 px-0 px-md-5">
  <div class="row align-items-center justify-content-center">
    <div class="col-md-12 col-lg-4 text-center">
      <img src="<?php echo get_template_directory_uri(); ?>/img/home-7.webp" class="lazyload" width="388" height="388" alt="<?php the_field('title_7'); ?>">
    </div>
    <div class="col-md-12 col-lg-7 px-5 px-md-5 mt-4 mt-lg-0">
      <h2 class="h1 mt-0 mb-4"><?php the_field('title_7'); ?></h2>
      <?php the_field('text_7'); ?>
    </div>
  </div>
</section>


<section class="container home-8 px-0 px-md-5">
  <div class="row align-items-center justify-content-center">
    <div class="col-md-12 col-lg-4 text-center order-1 order-lg-2">
      <img src="<?php echo get_template_directory_uri(); ?>/img/home-8.webp" class="lazyload" width="388" height="388" alt="<?php the_field('title_8'); ?>">
    </div>
    <div class="col-md-12 col-lg-7 order-2 order-md-1 px-5 px-md-5 mt-4 mt-lg-0">
      <h2 class="h1 mt-0 mb-4"><?php the_field('title_8'); ?></h2>
      <ul class="list-checked">
        <?php the_field('text_8'); ?>
      </ul>
    </div>
  </div>
</section>


<section class="container home_accordion px-0 px-md-5">
  <div class="row justify-content-center">
    <div class="col-md-12 col-lg-10 px-5 px-md-5 px-lg-0">

      <h2 class="h1 mt-0 mb-4"><?php the_field('faqs_title'); ?></h2>

      <?php if ( have_rows( 'faqs_items' ) ) :
        $item       = 0;
        $faq_schema = array(
          '@context'   => 'https://schema.org',
          '@type'      => 'FAQPage',
          'mainEntity' => array(),
        );
      ?>

      <div class="myaccordion" id="accordionExample">

        <?php while ( have_rows( 'faqs_items' ) ) : the_row();

          $faqs_question = get_sub_field( 'faqs_question' );
          $faqs_answer   = get_sub_field( 'faqs_answer' );

          $aria           = $item === 0 ? 'aria-expanded="true"'  : 'aria-expanded="false"';
          $collapse_class = $item === 0 ? ' show' : '';
          ?>
          <div class="myaccard">
            <div class="card-header myacheader" id="heading<?php echo esc_attr( $item ); ?>">
              <a class="collapsed" data-toggle="collapse"
                 data-target="#collapse<?php echo esc_attr( $item ); ?>"
                 aria-controls="collapse<?php echo esc_attr( $item ); ?>"
                 data-parent="#accordionExample" <?php echo $aria; ?>>
                  <span><?php echo esc_html( $faqs_question ); ?></span>
                  <i class="fa fa-angle-down"></i>
              </a>
            </div>
            <div id="collapse<?php echo esc_attr( $item ); ?>"
                 class="collapse<?php echo esc_attr( $collapse_class ); ?>"
                 aria-labelledby="heading<?php echo esc_attr( $item ); ?>"
                 data-parent="#accordionExample">
                <div class="card-body">
                    <?php echo wp_kses_post( $faqs_answer ); ?>
                </div>
            </div>
          </div>
          <?php

          $faq_schema['mainEntity'][] = array(
              '@type'          => 'Question',
              'name'           => wp_strip_all_tags( $faqs_question ),
              'acceptedAnswer' => array(
                  '@type' => 'Answer',
                  'text'  => wp_strip_all_tags( $faqs_answer ),
              ),
          );
          $item++;
        endwhile; ?>

      </div>

      <script type="application/ld+json">
        <?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
      </script>

      <?php endif;  ?>
    </div>
  </div>
</section>


<div class="container px-0 px-md-5 py-5">
  <div class="row justify-content-center">
    <div class="col-md-12 col-lg-12 px-5 px-md-5 px-lg-0">
      <?php echo do_shortcode('[trustindex no-registration=google]'); ?>
    </div>
  </div>
</div>


<section class="container home-9">
  <div class="row align-items-center justify-content-center">
    <div class="col-md-5 col-lg-6 pl-lg-5">
      <h2 class="h1 mt-0 mb-4"><?php the_field('title_9'); ?></h2>
      <p class="txt-grey mb-5"><?php bloginfo('description'); ?></p>
      <button type="button" class="btn green" data-toggle="modal" data-target="<?php the_field('form_id_class'); ?>">
        <?php the_field('anchor_links_9'); ?>
      </button>
    </div>
    <div class="col-md-7 col-lg-6 text-center">
      <img src="<?php echo get_template_directory_uri(); ?>/img/home-9.webp" class="lazyload" width="493" height="637" alt="<?php the_field('title_9'); ?>">
    </div>
  </div>
</section>


<!-- BLOG -->
<section class="blog">
  <div class="container px-3 px-md-5">
    <div class="row">
      <div class="col-md-12">
        <h2 class="h1 mt-0 mb-4 ml-4 ml-md-4">
          <?php $l = substr(get_locale(), 0, 2);
            if ($l =='en') {
              echo "Blog";
            } elseif ($l =='de') {
              echo "Blog";
            } elseif ($l =='uk') {
              echo "Блог";
            } elseif ($l =='fr') {
              echo "Blogue";
            } elseif ($l =='ru') {
              echo "Блог";
            } elseif ($l =='es') {
              echo "Blog";
            } elseif ($l =='zh') {
              echo "部落格";
            } elseif ($l =='pt') {
              echo "Blogue";
            } elseif ($l =='tr') {
              echo "Blog";
            }
          ?>
        </h2>
      </div>
    </div>
    <div class="row">
      <!-- Blog_En -->
      <?php IF ((is_page('2'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=106&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blog/">Show all</a>
        </div>
      <?php } ?>

      <!-- Blog_Ru -->
      <?php IF ((is_page('143'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=112&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blog-ru/">Показать все</a>
        </div>
      <?php } ?>


      <!-- Blog_UA -->
      <?php IF ((is_page('1297'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=142&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-md-4 col-lg-4 px-5 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blog-ua/">Показати все</a>
        </div>
      <?php } ?>



      <!-- Blog_De -->
      <?php IF ((is_page('141'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=108&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blog-de/">Alle anzeigen</a>
        </div>
      <?php } ?>

      

      <!-- Blog_Tr -->
      <?php IF ((is_page('147'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=122&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blog-tr/">Tümünü göster</a>
        </div>
      <?php } ?>


      <!-- Blog_Pt -->
      <?php IF ((is_page('146'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=118&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blogue-pt/">Mostrar tudo</a>
        </div>
      <?php } ?>

      <!-- Blog_Es -->
      <?php IF ((is_page('144'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=114&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blogue-pt/">Mostrar tudo</a>
        </div>
      <?php } ?>

      <!-- Blog_Ch -->
      <?php IF ((is_page('145'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=116&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blog-cn/">显示全部</a>
        </div>
      <?php } ?>

      <!-- Blog_Fr -->
      <?php IF ((is_page('142'))) { ?> 
        <?php
          if ( have_posts() ) :
            query_posts('cat=110&showposts=3');
          while (have_posts()) : the_post();
        ?>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 px-3 px-md-4 mt-4 mt-lg-0">
          <article itemscope itemtype="http://schema.org/Article">
            <a href="<?php the_permalink() ?>">
              <span class="date"><?php echo get_the_date( 'F j, Y' ); ?></span>
              <?php echo get_the_post_thumbnail(null, null, ['class'=>'mt-2']); ?>
              <h3 class="h5 mt-3" itemprop="headline"><?php the_title(); ?></h3>
              <span class="txt-grey"><?php the_excerpt(); ?></span>
            </a>
          </article>
        </div>
        <?php 
          endwhile;
            endif;
          wp_reset_query();
        ?>
        <div class="col-md-12 text-center pt-5">
          <a class="btn-all" href="/blogue-fr/">Tout afficher</a>
        </div>
      <?php } ?>

    </div>

  </div>
</section>



<div class="modal fade <?php the_field('form_id'); ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
    <div class="modal-content">
      <?php the_field('shortcode'); ?>
    </div>
  </div>
</div>


<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "eBusiness Solutions OÜ",
  "url": "https://legaladdressinestonia.com/",
  "logo": "https://legaladdressinestonia.com/wp-content/uploads/2026/07/logo.png",
  "description": "Licensed corporate service provider in Estonia (FIU000421): legal address, contact person, company registration, accounting.",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Tallinn",
    "addressCountry": "EE"
  },
  "sameAs": [
    "https://www.linkedin.com/company/legal-address-in-estonia/",
    "https://www.facebook.com/legaladdressinestonia/",
    "https://www.youtube.com/@LegalAddressInEstonia_RU"
  ]
}
</script>

<?php get_footer(); ?>