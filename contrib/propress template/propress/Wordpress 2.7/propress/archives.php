<?php
/*
Template Name: Archives
*/
?>
<?php get_header(); ?>
<div id="contentwrap">
  <div class="clear"></div>
  <div id="full">
    <div class="contentbody bg2">
      <div class="inner"><span class="corners-top"><span></span></span>
        <?php get_search_form() ?>
        <h2>Archives by Month:</h2>
        <ul>
          <?php wp_get_archives('type=monthly'); ?>
        </ul>
        <h2>Archives by Subject:</h2>
        <ul>
          <?php wp_list_categories(); ?>
        </ul>
        <span class="corners-bottom"><span></span></span> </div>
    </div>
  </div>
</div>
<?php get_footer(); ?>