<?php if (have_posts()) : ?>
    <?php 
        $geral = get_option('geral');
        $artigos = get_option('artigos'); 
    ?>
    <?php
    // Start the Loop.
    while (have_posts()) : the_post();

        /*
         * Include the post format-specific template for the content. If you want to
         * use this in a child theme, then include a file called called content-___.php
         * (where ___ is the post format) and that will be used instead.
         */
        ?>
        <article id="post-<?php the_ID(); ?>" class="artigo">
            <?php if ($geral['imagem_post'] == '1') { ?>
                <div class="col-md-12"><h2><a href="<?php echo esc_url(get_permalink()); ?>" rel="bookmark"> <?php the_title(); ?></a></h2></div>
            <?php } ?>
            <div class="col-md-5 <?php if ($geral['imagem_post'] == '0') echo 'thumb-img alpha' ?>">
                <div class="thumbnail-artigo">
                <?php if (has_post_thumbnail()) { // check if the post has a Post Thumbnail assigned to it. ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>" rel="bookmark"> 
                        <?php
                        if ($geral['imagem_post'] == '0')
                            the_post_thumbnail('thumb-artigo');
                        elseif ($geral['imagem_post'] == '1')
                            the_post_thumbnail('thumb-artigo_300_200');
                        ?>
                    </a>
                    <?php }else {
                    ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>" rel="bookmark">
                        <?php echo get_first_post_image($geral['imagem_post']); ?>
                    </a>
                    <?php
                }
                ?>
                </div>
            </div>
            <div class="col-md-7">
                <?php if ($geral['imagem_post'] == '0') { ?>
                    <h2><a href="<?php echo esc_url(get_permalink()); ?>" rel="bookmark"> <?php the_title(); ?></a></h2>
                <?php } ?>
                <?php 
                    echo strip_tags(excerpt(60)); 
                    if ($artigos['texto_link_artigo'] <> '')
                        $leiamais = $artigos['texto_link_artigo'];
                    else
                        $leiamais = 'Leia mais >>';

                ?>
                <div class="read-more"><a href="<?php echo esc_url(get_permalink()); ?>" class="btn readmore"><?php echo $leiamais; ?></a></div>
            </div>

        </article><!-- #post-## -->
        <div class="clearfix"></div>
        <?php
    endwhile;

    // Page navigation.
    odin_paging_nav();

else :
    // If no content, include the "No posts found" template.
    get_template_part('content', 'none');

endif;
?>