<?php
/**
 * Loop Name: Theme Post Loop
 */
?>

<?php if ( have_posts() ) : ?>

  <div class='blog-post-loop basil-post-wrap'>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'content' ); ?>

	<?php endwhile; ?>

</div>

<?php else : ?>

	<?php get_template_part( 'no-results' ); ?>

<?php endif; ?>
