<?php
namespace Depicter\Front;


use Depicter\Html\Html;

class Symbols
{

    private $symbols = [];

    /**
     * Add symbol id to symbols list
     *
     * @param string $symbolID
     * @return void
     */
    public function add( $symbolID ) {
        if ( !in_array( $symbolID, $this->symbols ) ) {
	        $this->symbols[] = $symbolID;
        }
    }

	/**
	 * Render registered svg symbols
	 *
	 * @return string|\TypeRocket\Html\Html
	 */
    public function render() {
        if ( !empty( $this->symbols ) ) {
        	$symbolsContent = '';
            foreach ( $this->symbols as $key => $symbolID ) {
            	if ( file_exists( DEPICTER_PLUGIN_PATH .'/resources/scripts/svg-symbols/' . $symbolID . '.svg' ) ) {
            		$symbolsContent .= file_get_contents( DEPICTER_PLUGIN_PATH .'/resources/scripts/svg-symbols/' . $symbolID . '.svg' );
	            }
            }

            return Html::el('svg', [ 'xmlns' => "http://www.w3.org/2000/svg" ], $symbolsContent );
        }

        return '';
    }
}
