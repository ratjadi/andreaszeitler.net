<?php /*
	Template Name: Default Template w. Comments
*/ ?>

<?php get_header(); ?>

<div class="content">
	
	<div class="primary">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	
			<div class="item2">
	
				<div class="pagetitle">
					<h2 id="post-<?php the_ID(); ?>"><?php the_title(); ?></h2>
					
				</div>
			
	
				<div class="itemtext">
					<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
		
					<?php link_pages('<p><strong>Pages:</strong> ', '</p>', 'number'); ?>
				</div>
			</div>
	<?php endwhile; endif; ?>

		
		<?php comments_template(); ?>

	</div>

	<?php get_sidebar(); ?>

</div>

<?php get_footer(); ?>