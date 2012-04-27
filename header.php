<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

<link rel="shortcut icon" href="<?php bloginfo('stylesheet_directory') ?>/images/favicon.ico" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

<?php wp_head(); ?>

</head>
<body <?php body_class(); ?>>
	<div class="l-body">&nbsp;</div>
	<!-- Wrapper -->
	<div id="wrapper">
		<!-- Header -->
		<div id="header">
			<!-- Shell -->
			<div class="shell">
				<!-- Head -->
				<div id="head">
					<h1 id="logo"><a href="<?php echo home_url('/'); ?>" class="notext"><?php echo get_bloginfo('name'); ?></a></h1>
					<p class="hello-text"><?php echo get_option('header_text'); ?></p>
					<!-- R Box -->
					<div class="r-box">
						<?php social_links(); ?>
						<div id="search">
							<form action="<?php bloginfo('url'); ?>" method="get">
								<div class="field-wrapper">
									<label class="label-hide" for="s">Search</label>
									<input type="text" class="field" id="s" name="s" value="" />
								</div>
								<div class="cl">&nbsp;</div>
							</form>
						</div>
					</div>
					<!-- End R Box -->
					<div class="cl">&nbsp;</div>	
				</div>
				<!-- End Head -->
				<!-- B Head -->
				<div class="b-head">
					<!-- Navigation -->
					<div id="navigation">
						<?php $args = array(	
							'container' => false,
							'theme_location' => 'main-menu',
							'fallback_cb' => 'wp_page_menu',
							);
						wp_nav_menu( $args ); ?> 
					</div>
					<!-- End Navigation -->
					<div class="intro">
						<?php show_intro(); ?>
					</div>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- End B Head -->
			</div>
			<!-- End Shell -->
		</div>
		<!-- End Header -->
		<!-- Main -->
		<div id="main">
			<!-- Shell -->
			<div class="shell">
