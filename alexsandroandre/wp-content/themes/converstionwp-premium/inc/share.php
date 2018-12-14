<?php
function exibir_share($share) {
    
    if ($share){
    ?>


    <div class="div-share">
        <div class="share-div">
        <a class="share-popup" href="http://www.facebook.com/sharer.php?u=<?php echo get_permalink(); ?>" title="Compartilhar via Facebook" target="_blank">
            <span><i class="fa fa-facebook-square"></i> Facebook</span>
        </a>
        </div>
        <div class="share-div">
        <a class="share-popup twitter" href="http://twitter.com/share?url=<?php echo get_permalink(); ?>&text=<?php the_title(); ?>" title="Compartilhar no Twitter" target="_blank">
            <span><i class="fa fa-twitter"></i>Twitter</span>
        </a>
        </div>
        <div class="share-div">
        <a class="share-popup google-plus" href="https://plus.google.com/share?url=<?php echo get_permalink(); ?>" title="Compartilhar no Google Plus" target="_blank">
            <span><i class="fa fa-google-plus-square"></i><span>Google Plus</span>
        </a>
        </div>
        <div class="share-div">
        <a class="share-popup whatsapp" href="whatsapp://send?text=<?php the_title(); ?> - <?php echo get_permalink(); ?>" title="Compartilhar no Whatsapp" target="_blank">
            <span><i class="fa fa-whatsapp"></i><span>WhatsApp</span>
        </a>
        </div>


    </div>

    <?php }} ?>