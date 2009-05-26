<?php
/*
Template Name: Links
*/
?>
<?php get_header(); ?>
<div id="contentwrap">
  <div class="clear"></div>
  <div id="full">
    <div class="contentbody bg2">
      <div class="inner"><span class="corners-top"><span></span></span>
        <h2>Links:</h2>
        <ul>
          <?php get_links_list(); ?>
        </ul>
        <span class="corners-bottom"><span></span></span> </div>
    </div>
  </div>
</div>
<?php get_footer(); ?>