<div class="bhumi_blog_area" id="bhumi_blog_section">
    <?php $cpm_theme_options = bhumi_get_options();
    $blog_show_posts = $cpm_theme_options['blog_show_posts'];
    $blog_read_more = $cpm_theme_options['blog_read_more'];
    $blog_category = ($blog_show_posts == 'catg' ? $cpm_theme_options['blog_category'] : '');
    $loop = 1;

    if ($cpm_theme_options['blog_title'] != '') { ?>
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="bhumi_heading_title">
                        <h3><?php echo esc_html($cpm_theme_options['blog_title']); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="container">
        <?php if (have_posts()) :
            $tax_query = '';
            if (!empty($blog_category) && $blog_category != 'bhumi_default') {
                $tax_query[] = array(
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $blog_category
                );
            }
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 6,
                'ignore_sticky_posts' => 1,
                'tax_query' => $tax_query,
            );
            $post_type_data = new WP_Query($args);
            while ($post_type_data->have_posts()):
                $post_type_data->the_post();
                echo(($loop % 3 == 1 || $loop == 1) ? '<div class="row">' : '');
                ?>
                <div class="col-md-4 col-sm-12 scale-in d2 pull-left in">
                    <div class="bhumi_blog_thumb_wrapper">
                        <div class="bhumi_blog_thumb_wrapper_showcase">
                            <?php $img = array('class' => 'bhumi_img_responsive');
                            if (has_post_thumbnail()):
                                the_post_thumbnail('bhumi_home_post_thumb', $img);
                            endif; ?>
                            <div class="bhumi_blog_thumb_wrapper_showcase_overlay">
                                <div class="bhumi_blog_thumb_wrapper_showcase_overlay_inner ">
                                    <div class="bhumi_blog_thumb_wrapper_showcase_icons">
                                        <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><i
                                                    class="fa fa-link"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <?php the_excerpt(__('Read More', 'bhumi')); ?>
                        <a href="<?php the_permalink(); ?>"
                           class="bhumi_blog_read_btn"><?php echo esc_html($blog_read_more); ?></a>
                        <?php if ($cpm_theme_options['show_blog_meta'] == "1") { ?>
                            <div class="bhumi_blog_thumb_footer">
                                <ul class="bhumi_blog_thumb_date">
                                    <li><i class="fa fa-user"></i><a
                                                href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><?php echo get_the_author(); ?></a>
                                    </li>
                                    <li><i class="fa fa-clock-o"></i>
                                        <?php if (('d M  y') == get_option('date_format')) : ?>
                                            <?php echo get_the_date('F d ,Y'); ?>
                                        <?php else : ?>
                                            <?php echo get_the_date(); ?>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <i class="fa fa-comments-o"></i><?php comments_popup_link('0', '1', '%', '', '-'); ?>
                                    </li>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
                echo(($loop % 3 == 0 && $loop != 1) ? '</div>' : '');
                $loop++;
            endwhile;
            wp_reset_postdata();
        endif; ?>
    </div>
</div>