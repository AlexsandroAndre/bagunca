<div id="post-<?php the_ID(); ?>" <?php post_class('bhumi_blog_full'); ?>>
	<div class="post-content-wrap">
	  <?php if( is_single() ){
	  	 if(has_post_thumbnail()):
					$img = array('class' => 'bhumi_img_responsive'); ?>
					<div class="bhumi_blog_thumb_wrapper_showcase">
						<div class="bhumi_blog-img">
							<?php the_post_thumbnail('bhumi_blog_2c_thumb',$img); ?>
						</div>
						<?php if (is_home()) : ?>
						<div class="bhumi_blog_thumb_wrapper_showcase_overlay">
							<div class="bhumi_blog_thumb_wrapper_showcase_overlay_inner ">
								<div class="bhumi_blog_thumb_wrapper_showcase_icons">
									<a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><i class="fa fa-link"></i></a>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
		<?php endif; }
		elseif(is_search() || is_archive() ){
				  $gallery = get_post_gallery_images();
				   $post_thumbnail_id = get_post_thumbnail_id();
    			   $attachment =  get_post($post_thumbnail_id);
    			   ?>
			        	<?php foreach ( $gallery as $key => $images ) { ?>
			                <div class="post-gallery-item">
			                    <a href="<?php the_permalink();?>" class="fulla">
			                        <div class="gallery-item fulla" style="background-image: url('<?php echo esc_url( $images); ?>');" title="<?php echo  esc_attr( $attachment->post_title ); ?>" alt= "<?php echo esc_attr( $attachment->post_excerpt );?>">
			                        </div>
			                    </a>
			                </div>
			            <?php } ?>
                 <?php
			}
			?>

		<div class="bhumi_full_blog_detail_padding">

			<h2><?php if(!is_single()) {?>
				<a href="<?php the_permalink(); ?>"><?php } ?><?php
				if(is_archive()){
					the_archive_title();
				}else{
					the_title();
				}
				?>
				</a>
			</h2>

			<div class="row">

				<div class="col-md-6 col-sm-3 padl0">
					<?php if(get_the_tag_list() != '') { ?>
					<p class="bhumi_tags"><?php the_tags( __('Tags : ','bhumi'), '', '<br />'); ?></p>
					<?php } ?>
				</div>

				<div class="col-md-6 col-sm-3">
					<?php if(get_the_category_list() != '') { ?>
					<p class="bhumi_cats"><?php esc_attr_e('Category : ','bhumi');
					the_category(' , '); ?></p>
					<?php } ?>
				</div>

			</div>

			<?php if(is_search() ){
				the_excerpt(); ?>

				<a class="bhumi_blog_read_btn" href="<?php the_permalink(); ?>"><?php esc_attr_e( 'Read More', 'bhumi' );?></a>
				<?php
			}
			elseif(is_archive() ){
				echo strip_shortcodes(wp_trim_words( get_the_content(), 40, '.....'));
			}
			else
			{
				the_content( __( 'Read More' , 'bhumi' ) );
			}
			$defaults = array(
	              'before'           => '<div class="bhumi_blog_pagination"><div class="bhumi_blog_pagi">',
	              'after'            => '</div></div>',
		          'link_before'      => '',
		          'link_after'       => '',
		          'next_or_number'   => 'number',
		          'separator'        => ' ',
		          'nextpagelink'     => __( 'Next page'  ,'bhumi' ),
		          'previouspagelink' => __( 'Previous page' ,'bhumi'),
		          'pagelink'         => '%',
		          'echo'             => 1
		          );
		          wp_link_pages( $defaults ); ?>
		</div>

	</div>
</div>

<div class="push-right">
	<hr class="blog-sep header-sep">
</div>