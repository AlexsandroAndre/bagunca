<?php
// code for comment
if ( ! function_exists( 'bhumi_comment' ) ) :
function bhumi_comment( $comment, $args, $depth )
{
	$GLOBALS['comment'] = $comment;
	//get theme data
	global $comment_data;
	//translations
	$leave_reply = $comment_data['translation_reply_to_coment'] ? $comment_data['translation_reply_to_coment'] :
	__('Reply','bhumi'); ?>
    <div class="media bhumi_comment_box">
			<a class="pull_left_comment">
            <?php echo get_avatar($comment,$size = '60'); ?>
            </a>
           <div class="media-body">
			    <div class="bhumi_comment_detail">
				<h4 class="bhumi_comment_detail_title"><?php comment_author();?></h4>

				<span class="bhumi_comment_date">
				<?php
				 comment_date();
				 esc_html_e(' at','bhumi');?>&nbsp;<?php comment_time(); ?>
				</span>
				<?php comment_text() ; ?>
				<div class="reply">
				<a href=""><i class="fa fa-mail-reply"></i><?php comment_reply_link(array_merge( $args, array('reply_text' => $leave_reply,'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
				</a>
				</div>

				<?php if ( $comment->comment_approved == '0' ) : ?>
				<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'bhumi' ); ?></em>
				<br/>
				<?php endif; ?>
				</div>
			</div>
	</div>
<?php
}
endif;
?>