<?php get_header(); ?>

<div id="contentwrap">
  <div class="clear"></div>
  <div id="content">
    <div class="contentbody bg2">
      <div class="inner"><span class="corners-top"><span></span></span>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="post" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
          <h2>
            <?php the_title(); ?>
          </h2>
          <div class="entry">
            <?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
            <?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
          </div>
        </div>
        <?php endwhile; endif; ?>
        <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
        <span class="corners-bottom"><span></span></span> </div>
    </div>
  </div>
  <div id="sidebar">
    <?php get_sidebar(); ?>
  </div>
</div>
<?php get_footer(); ?>
