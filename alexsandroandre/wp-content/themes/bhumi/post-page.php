<?php if ( have_posts()) : while ( have_posts() ) : the_post(); ?>
<div class="bhumi_blog_full">
<?php  if(has_post_thumbnail()):
			$default_arg = array('class' => "bhumi_img_responsive");
			?>
			<div class="bhumi_blog_thumb_wrapper_showcase">
				<div class="bhumi_blog-img">
				<?php the_post_thumbnail('bhumi_cpm_page_thumb',$default_arg); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="bhumi_blog_post_content">
			<?php the_content( __( 'Read More' , 'bhumi' ) ); ?>
		</div>
</div>
<div class="push-right">
	<hr class="blog-sep header-sep">
</div>
<?php comments_template( '', true ); ?>
<?php
endwhile;
endif; ?>