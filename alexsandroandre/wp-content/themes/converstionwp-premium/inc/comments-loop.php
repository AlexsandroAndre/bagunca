<?php
if (!function_exists('odin_comment_loop')) {

    /**
     * Custom comments loop.
     *
     * @since 2.2.0
     *
     * @param  object $comment Comment object.
     * @param  array  $args    Comment arguments.
     * @param  int    $depth   Comment depth.
     *
     * @return void
     */
    function odin_comments_loop($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;

        switch ($comment->comment_type) {
            case 'pingback' :
            case 'trackback' :
                ?>
                <li class="post pingback">
                    <p><?php _e('Pingback:', 'odin'); ?> <?php comment_author_link(); ?><?php edit_comment_link(__('Edit', 'odin'), '<span class="edit-link">', '</span>'); ?></p>
                    <?php
                    break;
                default :
                    ?>
                <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
                    <article id="comment-<?php comment_ID(); ?>" class="comment">
                        <footer class="comment-meta">
                            <div class="comment-author vcard">
                                <div class="avatar-comment"><?php echo get_avatar($comment, 80);?></div>
                                <div class="content-comment">
                                    <h4 class="fn"><?php echo get_comment_author_link();?></h4>
                                    <i class="fa fa-calendar"></i><time datetime="<?php echo get_comment_time('d/m/Y'); ?>"><?php echo get_comment_time('d/m/Y'); ?></time>
                                <?php edit_comment_link(__('Edit', 'odin'), '<span class="edit-link"> | ', '</span>'); ?>
                            </div><!-- .comment-author .vcard -->
                            <?php if ($comment->comment_approved == '0') : ?>
                                <div class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'odin'); ?></div>
                            <?php endif; ?>
                        </footer>
                        <div class="clearfix"></div>
                        <div class="comment-content"><?php comment_text(); ?></div>
                        <div class="reply">
                            <?php comment_reply_link(array_merge($args, array('reply_text' => '<i class="fa fa-reply"></i> Responder', 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
                        </div><!-- .reply -->
                    </article><!-- #comment-## -->

                    <?php
                    break;
            }
        }

    }
