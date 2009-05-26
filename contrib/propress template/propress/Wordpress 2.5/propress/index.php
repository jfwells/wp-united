<?php get_header(); ?>

<div id="contentwrap">
  <div id="content">
    <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
    <div class="contentbody bg2">
      <div class="inner"><span class="corners-top"><span></span></span>
        <div class="post" id="post-<?php the_ID(); ?>">
          <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
            <?php the_title(); ?>
            </a></h2>
          <small><?php the_time('F jS, Y') ?> by <?php the_author() ?> in <?php the_category(', ') ?></small>
          <div class="entry">
            <?php the_content('Read the rest of this entry &raquo;'); ?>
          </div>
          <p class="postmetadata">Posted in
            <?php the_category(', ') ?>
            |
            <?php edit_post_link('Edit', '', ' | '); ?>
            <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?><br />
			<?php the_tags('Tags: ', ', ', ''); ?>
          </p>
        </div>
        <span class="corners-bottom"><span></span></span> </div>
    </div>
    <?php endwhile; ?>
    <div class="contentbody bg2">
      <div class="inner"><span class="corners-top"><span></span></span>
        <div class="navigation">
          <div class="alignleft">
            <?php next_posts_link('&laquo; Previous Entries') ?>
          </div>
          <div class="alignright">
            <?php previous_posts_link('Next Entries &raquo;') ?>
          </div>
        </div>
        <?php else : ?>
        <h2 class="center">Not Found</h2>
        <p class="center">Sorry, but you are looking for something that isn't here.</p>
        <?php include (TEMPLATEPATH . "/searchform.php"); ?>
        <?php endif; ?>
        <span class="corners-bottom"><span></span></span> </div>
    </div>
  </div>
  <div id="sidebar">
        <?php get_sidebar(); ?>
  </div>
</div>
<?php get_footer(); ?>