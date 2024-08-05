<?php
namespace Depicter\DataSources;

use Averta\Core\Utility\Arr;
use Averta\Core\Utility\Data;
use Averta\Core\Utility\Str;
use Averta\WordPress\Utility\JSON;
use Averta\WordPress\Utility\Post;

class Posts extends DataSourceBase implements DataSourceInterface
{
	/**
	 * DataSource name
	 *
	 * @var string
	 */
	protected $type = 'wpPost';

	/**
	 * DataSource properties
	 *
	 * @var array
	 */
	protected $properties = [
		'type'     => 'wpPost',
		'postType' => 'post'
	];

	/**
	 * Default input params for retrieving dataSource records
	 *
	 * @var array
	 */
	protected $defaultInputParams = [
		'postType' => 'post',
		'perpage' => 5,
		'excerptLength' => 55,
		'offset' => 0,
		'linkSlides' => true,
		'orderBy' => 'post__in',
		'order' => 'DESC',
		'imageSource' => 'featured',
		'excludedIds' => '',
		'includedIds' => '',
		'excludeNonThumbnail' => false,
		'taxonomies' => '',
		'page' 		 => 1,
		's'			 => ''
	];

	/**
	 * Asset groups of this DataSource
	 *
	 * @var array
	 */
	protected $assetGroupNames = [ 'post', 'taxonomy', 'acf', 'metaboxio' ];

	/**
	 * Retrieves the list of records based on query params
	 *
	 * @param $args
	 *
	 * @return \WP_Query
	 */
	protected function getRecords( $args ){

		$queryArgs = [
		    'post_type'       => $args['postType'],
		    'posts_per_page'  => $args['perpage'],
		    'order'           => $args['order'],
		    'orderby'         => $args['orderBy'],
		    'offset'          => $args['offset'],
			'paged'           => $args['page'],
		    'post__in'        => $args['includedIds'],
		    'post__not_in'    => $args['excludedIds'],
		    'tax_query'       => [],
			'meta_query'      => []
	    ];

		if( !empty( $args['s'] ) ){
			$queryArgs['s'] = $args['s'];
		}

	    if ( !empty( $args['taxonomies'] ) ) {
			$taxonomies = $args['taxonomies'];

			if( JSON::isJson( $args['taxonomies'] ) ){
				$taxonomies = JSON::decode( $args['taxonomies'] );
			}

			if ( !empty( $taxonomies ) ) {
				foreach( $taxonomies as $taxonomySlug => $value ) {
					if ( !empty( $value ) ) {
						$queryArgs['tax_query'][] = [
							'taxonomy'  => $taxonomySlug,
							'field'     => 'slug',
							'terms'     => $value
						];
					}
				}
			}
	    }

		if( Data::isTrue( $args['excludeNonThumbnail'] ) ){
			$queryArgs['meta_query'][] = [
	    		'key'     => '_thumbnail_id',
                'compare' => 'EXISTS'
		    ];
		}

		return new \WP_Query( $queryArgs );
	}

	public function previewRecords( array $args = [] ) {
		$args = $this->prepare( $args );
		$availableRecords = $this->getRecords( $args );

		$records = [];

		if ( $availableRecords && $availableRecords->have_posts() ) {
			$acfModule = \Depicter::resolve( 'depicter.dataSources.tags.acf' );
			$acfModuleAvailable = $acfModule->isAvailable();
			$acfFields = $acfModule->getFieldGroups( $args );

			foreach( $availableRecords->posts as $post ) {
				$featuredImage = null;
				if( has_post_thumbnail( $post->ID ) ){
					$featuredImageId = get_post_thumbnail_id( $post->ID );
					$imageInfo = wp_get_attachment_image_src( $featuredImageId, 'full' );
					$featuredImage = [
						'id'     => $featuredImageId,
						'src'    => $imageInfo[0]  ?: '',
						'width'  => $imageInfo[1] ?: '',
						'height' => $imageInfo[2] ?: '',
					];
				}

				$content =  get_the_content(null, false, $post->ID );
				$excerpt = Post::getExcerptTrimmedByWords( $post->ID );
				if ( ! empty( $args['excerptLength'] ) ) {
					$excerpt = Str::trimByChars( $excerpt, $args['excerptLength'] );
				}

				$postInfo = [
					'id'        => $post->ID,
					'title'     => get_the_title( $post->ID ),
					'url'       => get_permalink( $post->ID ),
					'featuredImage' => $featuredImage,
					'date'      => get_the_date('Y-m-d h:m:s', $post->ID ),
					'excerpt'   => $excerpt,
					'author' => [
						'name' => get_the_author_meta( 'display_name', $post->post_author ),
						'page' => get_author_posts_url( $post->post_author ),
					],
					'content'   => $content,
					'taxonomy'=> []
				];

				// append taxonomy data
				$taxonomies = get_object_taxonomies( $args['postType'], 'objects' );

				if ( !empty( $taxonomies ) ) {
					foreach( $taxonomies as $taxonomySlug => $taxonomy ) {
						$taxonomyInfo = [
							"id"    => $taxonomySlug,
							"label" => $taxonomy->label,
							"terms" => []
						];

						if ( $terms = wp_get_post_terms( $post->ID, $taxonomySlug ) ) {
							foreach( $terms as $term ) {
								$taxonomyInfo[ "terms" ][] = [
									'id' => $term->term_id,
							        'value' => $term->slug,
							        'label' => $term->name,
									'link' => get_term_link( $term->term_id )
								];
							}
						} else {
							$taxonomyInfo[ "terms" ] = null;
						}

						$postInfo['taxonomy'][ $taxonomySlug ] = $taxonomyInfo;
					}
				}

				// append acf data
				if( $acfModuleAvailable ){
					$postInfo['acf'] = [];

					if ( !empty( $acfFields ) ) {
						foreach( $acfFields as $fieldId => $field ) {
							$postInfo['acf'][ $fieldId ] = $acfModule->getSlugValue( $fieldId, ['post' => $post->ID ] );
							if( $field['type'] === 'image' ){
								$imageInfo = wp_get_attachment_image_src( $postInfo['acf'][ $fieldId ], 'full' );
								$postInfo['acf'][ $fieldId ] = [
									'id'     => $postInfo['acf'][ $fieldId ],
									'src'    => $imageInfo[0]  ?? '',
									'width'  => $imageInfo[1] ?? '',
									'height' => $imageInfo[2] ?? '',
								];
							}
						}
					}
				}

				$records[] = $postInfo;
			}
		}

		return $records;
    }

	public function previewRecords2( array $args = [] ) {
		$args = $this->prepare( $args );
		$availableRecords = $this->getRecords( $args );

		$records = [];

		if ( $availableRecords && $availableRecords->have_posts() ) {
			foreach( $availableRecords->posts as $post ) {
				$item = [];
				$args['post'] = $post;

				$assetGroups = $this->getAssetGroupNames();
				foreach( $assetGroups as $assetGroup ){
					if( $module = \Depicter::dataSource()->tagsManager()->getModule( $assetGroup ) ){
						$item[ $assetGroup ] = $module->getValuesForRecord( $args );
					}
				}

				$records[] = $item;
			}
		}

		return $records;
    }

	/**
	 * search records by title
	 *
	 * @param array $args
	 *
	 * @return array
	 */
    public function searchRecordsByTitle( array $args = [] ) {
		$args = $this->prepare( $args );

		add_filter( 'posts_search', [ $this, 'searchByTitleOnly' ], 500, 2 );
		$availableRecords = $this->getRecords( $args );
		remove_filter( 'posts_search', [ $this, 'searchByTitleOnly' ], 500 );

		$records = [];

		if ( $availableRecords && $availableRecords->have_posts() ) {

			foreach( $availableRecords->posts as $post ) {
				$postInfo = [
					'id'        => $post->ID,
					'title'     => get_the_title( $post->ID ),
					'url'       => get_permalink( $post->ID )
				];

				$records[] = $postInfo;
			}
		}

		return $records;
    }

	/**
	 * Get list of datasheets and corresponding required arguments
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function getDataSheetArgs( array $args = [] ){
		$args = $this->prepare( $args );
		$availableRecords = $this->getRecords( $args );

		$dataSheetArgs = Arr::merge( $args, $this->properties );
		$records = [];

		if ( $availableRecords && $availableRecords->have_posts() ) {

			foreach( $availableRecords->posts as $post ) {
				$postArgs = $dataSheetArgs;
				$postArgs['post'] = $post;
				$records[] = $postArgs;
			}
		}

		return $records;
	}

	/**
	 * Get All Post Types info
	 *
	 * @param string $postType
	 *
	 * @return array
	 */
    public function getTypes( string $postType = 'all' ) {
    	if ( $postType == 'all' ) {
		    $postTypes = get_post_types(
			    array(
				    'public'    => true,
				    '_builtin'  => false
			    ),
			    'objects'
		    );

		// $postTypes = array_merge( $availablePostType, $postTypes );
	    } else {
		    if ( !post_type_exists( $postType ) ) {
			    return [];
		    }
    		$postTypes = [ $postType => get_post_type_object( $postType ) ];
	    }

        $result = [];
        foreach( $postTypes as $id => $providedPostType ) {

	        $postTypeInfo = [
		        'id' => $id,
		        'label' => $providedPostType->label,
		        'taxonomies' => []
	        ];

	        $taxonomies = get_object_taxonomies( $id, 'objects' );

			foreach( $taxonomies as $taxonomySlug => $taxonomy ) {
				$taxonomyInfo = [
					"id"    => $taxonomySlug,
					"label" => $taxonomy->label,
					"terms" => []
				];

				$terms = get_terms([
	        		'taxonomy' => $taxonomySlug,
			        'hide_empty' => false
		        ]);

	        	if ( !empty( $terms ) ) {
	        		foreach( $terms as $term ) {
	        			$taxonomyInfo[ "terms" ][] = [
	        				'id' => $term->term_id,
					        'value' => $term->slug,
					        'label' => $term->name
				        ];
			        }
		        }

				$postTypeInfo["taxonomies"][] = $taxonomyInfo;
	        }

	        $result[] = $postTypeInfo;
        }

        return $result;
    }

	/**
	 * Search by title in wp query
	 *
	 * @param string $search
	 * @param object $wp_query
	 * @return string $search
	 */
	public function searchByTitleOnly( $search, $wp_query ) {
		global $wpdb;

		if ( empty( $search ) ) {
			return $search;
		}

		$queryVars = $wp_query->query_vars;
		$n = !empty( $queryVars['exact'] ) ? '' : '%';
		$search = '';
		$searchAnd = '';
		foreach ( (array) $queryVars['search_terms'] as $term ) {
			$term = esc_sql( $wpdb->esc_like( $term ) );
			$search .= " {$searchAnd} ( $wpdb->posts.post_title LIKE '{$n}{$term}{$n}' )";
			$searchAnd = ' AND ';
		}
		if ( !empty( $search ) ) {
			$search = " AND ( {$search} ) ";
			if ( !is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		return $search;
	}

	/**
	 * Get taxonomy terms string
	 *
	 * @param int $postID
	 * @param string $taxonomy
	 * @return string
	 */
	protected function getTaxonomyTermsStr( $postID, $taxonomy ) {
		$terms = get_the_terms( $postID, $taxonomy );

		if ( !empty( $terms ) ) {
			return join( ', ', wp_list_pluck($terms, 'name') );
		}

		return '';
	}

	/**
	 * Get list of asset groups for this dataSource
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function getAssets( $args ){
		$assetGroupNames = $this->getAssetGroupNames();

		$groups = [];
		foreach( $assetGroupNames as $assetGroupName ){
			$groups[ $assetGroupName ] = $args;
		}

		return \Depicter::dataSource()->tagsManager()->getAssetsInGroups( $groups );
	}

}
