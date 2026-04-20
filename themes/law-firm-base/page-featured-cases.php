<?php
/**
 * Template Name: Featured Cases
 * Template Post Type: page
 *
 * @package LawFirmBase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$featured_cases_query = new WP_Query(
	array(
		'post_type'      => 'featured_case',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
?>
<main id="primary" class="site-main">
	<header class="page-header">
		<h1><?php the_title(); ?></h1>
	</header>

	<?php if ( $featured_cases_query->have_posts() ) : ?>
		<section aria-label="<?php esc_attr_e( 'Featured Cases List', 'law-firm-base' ); ?>">
			<?php
			while ( $featured_cases_query->have_posts() ) :
				$featured_cases_query->the_post();

				$case_type_terms   = get_the_terms( get_the_ID(), 'fc_case_type' );
				$case_type         = '';
				$settlement_amount = get_post_meta( get_the_ID(), '_fc_settlement_amount', true );

				if ( ! is_wp_error( $case_type_terms ) && ! empty( $case_type_terms ) ) {
					$case_type = $case_type_terms[0]->name;
				}
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h2><?php the_title(); ?></h2>
					<p>
						<strong><?php esc_html_e( 'Case Type:', 'law-firm-base' ); ?></strong>
						<?php echo esc_html( $case_type ? $case_type : __( 'Not provided', 'law-firm-base' ) ); ?>
					</p>
					<p>
						<strong><?php esc_html_e( 'Settlement Amount:', 'law-firm-base' ); ?></strong>
						<?php echo esc_html( $settlement_amount ? $settlement_amount : __( 'Not provided', 'law-firm-base' ) ); ?>
					</p>
				</article>
				<?php
			endwhile;
			?>
		</section>
	<?php else : ?>
		<p><?php esc_html_e( 'No featured cases found.', 'law-firm-base' ); ?></p>
	<?php endif; ?>
</main>
<?php
wp_reset_postdata();
get_footer();
