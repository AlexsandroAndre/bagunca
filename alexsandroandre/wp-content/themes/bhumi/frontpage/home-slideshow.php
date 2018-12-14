<!-- Carousel ================================================== -->
	<?php $cpm_theme_options = bhumi_get_options();
    $slider_number = $cpm_theme_options['slider_number'];
    $slider_category_slug = ($cpm_theme_options['slider_category'] == 'bhumi_default'?'':$cpm_theme_options['slider_category']);
    $bhumi_slider_arg = array(
    'post_type'      => 'post',
    'posts_per_page' => $slider_number,
    'post_status'    => 'publish',
    'order'          => 'desc',
    'orderby'        => 'date',
    'ignore_sticky_posts' => 1,
    'category_name' => $slider_category_slug,
    );
    $bhumi_slider_query = new WP_Query($bhumi_slider_arg);
    if($bhumi_slider_query->have_posts()):
     ?>
    <div id="myCarousel" class="carousel slide" data-ride="carousel">
      <!-- Indicators -->
      <ol class="carousel-indicators">
      <?php
        $count = 0;
       while($bhumi_slider_query->have_posts()):
                $bhumi_slider_query->the_post();
                $active_check = 'active'; ?>
                <li data-target="#myCarousel" data-slide-to="<?php echo $count; ?>" class="<?php echo esc_attr($active_check); ?>"></li>
                <?php
                 $active_check = '';
                $count++;
        endwhile;
        wp_reset_postdata(); ?>
      </ol>
      <div class="carousel-inner">
        <?php
        $active = 'active';
        while($bhumi_slider_query->have_posts()):
                $bhumi_slider_query->the_post();
                $slider_image_id = get_post_thumbnail_id();
                $slider_image = wp_get_attachment_image_src( $slider_image_id, 'full' );
                ?>
                <div class="item <?php echo esc_html( $active );?>">
    			     <?php if($slider_image!='') {  ?>
                 		 	<img src="<?php echo esc_url($slider_image[0]); ?>" class="img-responsive" alt="<?php the_title(); ?>">
        		      <?php } ?>
            		  <div class="container">
                        <div class="carousel-caption">
              					   <div class="carousel-text">
              		            <h1 class="animated bounceInRight"><?php the_title(); ?></h1>
                              <?php if(get_the_content()):?>
                    						<ul class="list-unstyled carousel-list">
                    						  <li class="animated bounceInLeft"><?php the_excerpt(); ?></li>
                    						</ul>
                              <?php endif; ?>
            		       		     <a class="bhumi_blog_read_btn animated bounceInUp" href="<?php the_permalink() ?>" target="_blank" role="button"><?php the_title(); ?>
                               </a>
              		          </div>
                  			</div>
                      </div>
                </div>
                <?php $active = '';
         endwhile;
        wp_reset_postdata(); ?>
      </div>
      <a class="left carousel-control" href="#myCarousel" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
      <a class="right carousel-control" href="#myCarousel" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
	  <div class="bhumi_slider_shadow"></div>
    </div><!-- /.carousel -->
    <?php endif;