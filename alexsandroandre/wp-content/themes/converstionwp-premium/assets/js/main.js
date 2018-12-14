fluidvids.init({
    selector: ['iframe', 'object'], // runs querySelectorAll()
    players: ['www.youtube.com', 'player.vimeo.com'] // players to support
});

// ===== Scroll to Top ==== 
var j = jQuery.noConflict();
j(window).scroll(function() {
    if (j(this).scrollTop() >= 50) {        // If page is scrolled more than 50px
        j('#return-to-top').fadeIn(200);    // Fade in the arrow
    } else {
        j('#return-to-top').fadeOut(200);   // Else fade out the arrow
    }
});
j('#return-to-top').click(function() {      // When arrow is clicked
    j('body,html').animate({
        scrollTop : 0                       // Scroll to top of body
    }, 500);
});

jQuery(document).ready(function($) {

	// Responsive wp_video_shortcode().
	$( '.wp-video-shortcode' ).parent( 'div' ).css( 'width', 'auto' );

	/**
	 * Odin Core shortcodes
	 */

	// Tabs.
	$( '.odin-tabs a' ).click(function(e) {
		e.preventDefault();
		$(this).tab( 'show' );
	});

	// Tooltip.
	$( '.odin-tooltip' ).tooltip();
        
        $( "#close-top-aviso" ).click(function() {
            $( "#top-aviso" ).slideUp( "slow", function() {
              // Animation complete.
            });
        });

});
