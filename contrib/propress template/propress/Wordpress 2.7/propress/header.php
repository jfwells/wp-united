<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>

<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />

<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' );

wp_head(); ?>
</head>
<body>
<div id="wrapper">
  <div id="page-header">
    <div class="headerbar">
      <div class="inner"><span class="corners-top"><span></span></span>
        <div id="site-description">
		<a href="<?php bloginfo('url'); ?>/" title="<?php bloginfo('name'); ?>" id="logo"><img src="wp-content/themes/propress/images/header.png" /></a>
		<!--<h1><a href="<?php echo get_option('home'); ?>/"><?php bloginfo('name'); ?></a></h1>
		<div class="description"><?php bloginfo('description'); ?></div>-->
		</div>
		<div id="search-box">
				<?php include ('searchform.php'); ?>
			</div>
        <span class="corners-bottom"><span></span></span></div>
    </div>
  </div>
<div class="clear"></div>