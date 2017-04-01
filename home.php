<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * e.g., it puts together the home page when no home.php file exists.
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();

?>


		<section class="magazine w-full" id="main-content">


			<header class="magazine-header ">

				<div class="row">
					<div class="one-of one-of-two">
						<h1><?php echo cipt_get_translated_ACF_option('blog_title_magazine'); ?></h1>
					</div>
				</div>

<div class="row">
	<div class="filter-reveal">
		
		<input type="search" id="search-filter" class="search-field" placeholder="<?php echo esc_attr_x( 'Search a filter &hellip;', 'placeholder', 'twentyseventeen' ); ?>" value="" name="search-filter" />

		<div class="filterlist">
			
		</div>

	</div>
</div>

				<div class="row">
					<div class="one-of one-of-two header-filter show-for-medium">
						<ul id="magazine-header-buttons" class="">
							<li class="menu-item button-list-li"><a href="#" id="switch-to-grid" class="active"><svg class="svgicon svgicon-view-grid"><use xlink:href="#svgicon-view-grid"></use></svg></a></li>
							<li class="menu-item button-list-li"><a href="#" id="switch-to-list" class=""><svg class="svgicon svgicon-view-list"><use xlink:href="#svgicon-view-list"></use></svg></a></li>
							<li class="menu-item search-button-li"  style="display: inline-block;"><a class="button" href="#"><?php echo cipt_get_translated_ACF_option('filter_search_btn'); ?>&nbsp;<svg class="svgicon svgicon-search-arrow"><use xlink:href="#svgicon-search-arrow"></use></svg></a></li>
						</ul>
					</div>

					<div class="one-of one-of-two header-intro">

						<?php
						$blog_intro = false;
						if ( cipt_get_lang() === 'en' ) {
							$blog_intro = get_field('en_content', 29);
						} else {
							$blog_intro = cipt_get_the_content_by_id(29);
						}
						if ( $blog_intro && !empty($blog_intro) ) {
						?>
						<div class="header-intro-copy"><?php echo $blog_intro; ?></div>
						<?php } ?>

						<div class="one-of one-of-two header-filter hide-for-medium">
							<ul id="magazine-header-buttons" class="">
								<li class="menu-item search-button-li" style="display: inline-block;"><a class="button" href="#"><?php echo cipt_get_translated_ACF_option('filter_search_btn'); ?>&nbsp;<svg class="svgicon svgicon-search-arrow"><use xlink:href="#svgicon-search-arrow"></use></svg></a></li>
							</ul>
						</div>

						<p class="result-count"><?php echo wp_count_posts()->publish; ?>&nbsp;<?php echo cipt_get_translated_ACF_option('filter_results_txt'); ?></p>
					</div>



				</div>

			</header>


			<section id="article-list" class="grid-view">

			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part('template-parts/article-preview'); ?>

				<?php endwhile; ?>
			<?php endif; // End have_posts() check. ?>

			</section>


			<footer class="magazine-footer">
				
				 <!-- <a href="#" class="button">show more <svg class="svgicon svgicon-archive-arrow-down"><use xlink:href="#svgicon-archive-arrow-down"></use></svg></a> -->

			</footer>


		</section>


		<?php get_template_part('template-parts/newsletter'); ?>




<?php 

get_footer();