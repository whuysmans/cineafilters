<?php
namespace Util;

class Params {

	static function getAllowedFilters() {
		//first get the taxonomies terms
		$params = array( 'thema', 'artikeltype', 'regisseur', 'auteur', 'lang' );
		$result = array();
		foreach( $params as $param ) {
			$taxonomy = get_taxonomy( $param );
			$terms = get_terms( array(
				'taxonomy' => $param,
				'hide_empty' => true,
				'orderby' => 'name',
				'order' => 'ASC',
				'hierarchical' => false,
			) );
			$result[ $param ][ 'meta' ] = array( 'is_tax' => true, 'is_cpt' => false, 
				'name' => $taxonomy->label, 'slug' => $param);
			$result[ $param ][ 'filters' ] = $terms;
		}
		
		//now get the events slugs (custom post type)
		$query = new \WP_Query( array (
			'post_type' => 'event'
		) );
		$events = $query->posts;
		$result[ 'event' ] = array();
		//make the result look like a wordpress object
		foreach( $events as $event ) {
			//we need the event ids for meta query later on
			$result[ 'event' ][ 'meta' ] = array( 'is_tax' => false, 'is_cpt' => true, 'name' => 'Events',
				'slug' => 'event' );
			$result[ 'event' ][ 'filters' ][] = (object) array( 'name' => $event->post_title,
													'id' => $event->ID, 'slug' => $event->post_name );
		}
		// write_log($result);
		return $result;
	}

}

class Queries {

	static function getAllowedPosts( $paramsArray ) {

		write_log($paramsArray);

		if( $paramsArray ) {
			//we need all those arrays
			$args = array( 'posts_per_page' => -1 );
			$taxQuery = array( );
			$metaQuery = array();
			$eventsArray = array();
			$taxArray = array();
			//split the incoming array into two arrays: one for CTs en one for CPTs
			if( isset( $paramsArray[ 'event' ] ) )
				$eventsArray = $paramsArray[ 'event' ];

			foreach( $paramsArray as $key => $value) {
				if( $key !== 'event' ) {
					$taxArray[ $key ] = $value;
				}
			}
			//define tax_query for CTs
			if( count( $taxArray ) > 0 ) {
				//if multiple filters, make it an AND query
				if( count( $taxArray ) > 1 ) {
					$taxQuery['relation'] = 'AND';
				}
				//push the filters into the tax_query array
				foreach( $taxArray as $key => $value ) {
					foreach ( $value as $v ) {
						// $term = preg_replace( '/\s+/', '', $v[ 'slug' ] );
						array_push( $taxQuery, array(
							'taxonomy' => $key,
							'field' => 'term_id',
							'terms' => $v['id']
						) );
					}
				}
				$args[ 'tax_query' ] = $taxQuery;
			}

			//define meta_query for CPTs
			if( count( $eventsArray ) > 0 ) {
				//if multiple filters, create an AND query
				if ( count( $eventsArray > 1 ) ) {
					$metaQuery[ 'relation' ] = 'AND';
				}
				//push filters into the meta_query array
				foreach( $eventsArray as $eventArray ) {
					$id = $eventArray[ 'id' ];
					array_push( $metaQuery, array( 
						'key' => 'filter_event',
						'value' => '"' . htmlspecialchars( $id ) . '"',
						'compare' => 'LIKE' 
					) );
				}
				$args[ 'meta_query' ] = $metaQuery;
			}
			//do the query
			$query = new \WP_Query( $args );
		} else {
			$query = new \WP_Query( array( 
				'post_type' => 'post'
			) );
		}

		//get the resulting html
		ob_start();
		if( $query->have_posts() ) :
			while( $query->have_posts() ) : $query->the_post();

				get_template_part( 'template-parts/article', 'preview' );

			endwhile;

		endif;

		wp_reset_postdata();
		
		$content = ob_get_clean();
		//return the html
		return $content;
	}
}
