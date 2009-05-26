<?php get_header(); ?>

<div id="contentwrap">
  <div class="clear"></div>
  <div id="content">
    <div class="contentbody bg2">
      <div class="inner"><span class="corners-top"><span></span></span>
        <?php if (have_posts()) : ?>
        <h2 class="pagetitle">Search Results</h2>
        <div class="navigation">
          <div class="alignleft">
            <?php next_posts_link('&laquo; Previous Entries') ?>
          </div>
          <div class="alignright">
            <?php previous_posts_link('Next Entries &raquo;') ?>
          </div>
        </div>
        <?php while (have_posts()) : the_post(); ?>
        <div class="poswp">
          <h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
            <?php the_title(); ?>
            </a></h3>
          <small>
          <?php the_time('l, F jS, Y') ?>
          </small>
          <p class="postmetadata">Posted in
            <?php the_category(', ') ?>
            |
            <?php edit_post_link('Edit', '', ' | '); ?>
            <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
          </p>
        </div>
        <?php endwhile; ?>
        <div class="navigation">
          <div class="alignleft">
            <?php next_posts_link('&laquo; Previous Entries') ?>
          </div>
          <div class="alignright">
            <?php previous_posts_link('Next Entries &raquo;') ?>
          </div>
        </div>
        <?php else : ?>
        <h2 class="center">No posts found. Try a different search?</h2>
        <?php include (TEMPLATEPATH . '/searchform.php'); ?>
        <?php endif; ?>
        <span class="corners-bottom"><span></span></span> </div>
    </div>
  </div>
  <div id="sidebar">
    <?php get_sidebar(); ?>
  </div>
</div>
<?php get_footer(); ?>
