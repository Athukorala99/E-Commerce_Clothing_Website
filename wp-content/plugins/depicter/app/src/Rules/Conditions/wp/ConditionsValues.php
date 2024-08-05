<?php

namespace Depicter\Rules\Conditions\wp;

class ConditionsValues
{

	public function get( $query ): ?array{
		$query = explode( ':', $query );
		if ( post_type_exists( $query[1] ) ) {
			return $this->getPosts( $query[1] );
		} else if ( taxonomy_exists( $query[1] ) ) {
			return $this->getTermsList( $query[1] );
		} else if ( $query[1] == 'authors' ) {
			return $this->getAuthorsList();
		}

		return [];
	}

	/**
	 * List available posts for specified postType
	 *
	 * @param $postType
	 *
	 * @return array
	 */
	public function getPosts( $postType ): array{
		$posts = get_posts([
			'post_type' => $postType,
			'numberposts' => -1,
			'fields' => [ 'ids', 'post_title']
		]);

		$items = [];
		if ( !empty( $posts ) ) {
			foreach( $posts as $post ) {
				$items[] = [
					'label' => $post->post_title,
					'value' => $post->ID
				];
			}
		}

		return $items;
	}

	/**
	 * List available terms for specified taxonomy
	 * @param $taxonomy
	 *
	 * @return array
	 */
	public function getTermsList( $taxonomy ): array{
		$items = [];
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);

		if ( !empty( $terms ) ) {
			foreach( $terms as $term ) {
				$items[] = [
					'label' => $term->name,
					'value' => $term->term_id
				];
			}
		}

		return $items;
	}

	/**
	 * Get list of authors who has published post
	 *
	 * @return array
	 */
	public function getAuthorsList(): array{
		$authors = get_users([
			'has_published_posts' => true,
			'fields' => [ 'ID', 'display_name' ]
		]);

		$items = [];
		if ( ! empty( $authors ) ) {
			foreach( $authors as $author ) {
				$items[] = [
					'label' => $author->display_name,
					'value' => $author->ID
				];
			}
		}

		return $items;
	}
}
