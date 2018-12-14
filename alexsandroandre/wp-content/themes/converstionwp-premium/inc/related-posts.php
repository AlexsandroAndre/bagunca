<div class="relatedposts">
<h3>Artigos Relacionados</h3>
<?php
    $orig_post = $post;
    global $post;
    $categories = get_the_category($post->ID);
    if ($categories) {
        $category_ids = array();
        foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
        $args=array(
            'category__in' => $category_ids,
            'post__not_in' => array($post->ID),
            'posts_per_page'=> 4, // Number of related posts that will be shown.
            'ignore_sticky_posts'=>1
        );
     
    $my_query = new wp_query( $args );
 
    while( $my_query->have_posts() ) {
    $my_query->the_post();
    ?>
     
    <div class="relatedthumb">
        <a rel="external" href="<?php the_permalink()?>"><?php the_post_thumbnail(array(150,100)); ?><br />
        <?php the_title(); ?>
        </a>
    </div>
     
    <?php }
    }
    $post = $orig_post;
    wp_reset_query();
    ?>
</div>