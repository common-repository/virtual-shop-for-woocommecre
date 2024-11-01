<?php
/**
* Template Name: VRShop - Page Template
*
* A blank custom page template.
*
* The "Template Name:" bit above allows this to be selectable
* from a dropdown menu on the edit page screen.
*
*/

global $isVrShopSession;

if(!$isVrShopSession) {
	if(file_exists(get_template_directory()."/full-width.php")) {
		include(get_template_directory()."/full-width.php");
	}
	else if(file_exists(get_template_directory()."/page.php")){
		include(get_template_directory()."/page.php");
	}
	else if(file_exists(get_template_directory()."/single.php")){
		include(get_template_directory()."/single.php");
	}
	else{
		include(get_template_directory()."/index.php");
	}
} else {
?>
<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>
<?php the_content(); ?>
<?php endwhile; ?>
<?php endif; ?>
<?php } ?>