<?php
namespace Depicter\Services;

class I18nService
{
	/**
	 * @var string
	 */
	public $phpFileUrl;

	/**
	 * @var string
	 */
	public $jsonUrl;

	/**
	 * @var string
	 */
	public $json;

	/**
	 * I18nService constructor.
	 *
	 * @param $phpFileUrl
	 * @param $jsonUrl
	 */
	public  function __construct( $phpFileUrl, $jsonUrl )
	{
		$this->phpFileUrl = $phpFileUrl;
		$this->jsonUrl = $jsonUrl;
		$this->readJson();
		$this->run();
	}

	/**
	 * read json file
	 *
	 * @return void
	 */
	public function readJson() {
		$this->json = file_get_contents( $this->jsonUrl );
	}

	public function run() {
		$data = json_decode( $this->json, true );

		$newTexts = "\t\t\t/** ==EditorLocalizationList==START== **/\n\t\t\t";
		foreach ( $data as $key => $value ) {

			$key = str_replace(array("\r\n", "\n", "\r"), '\n', $key);
			$key = str_replace(array("'"), "\'", $key);
			$newTexts .= "'".$key."' => __('".$key."', 'depicter'),\n\t\t\t";
		}
		$newTexts .= "/** ==EditorLocalizationList==END== **/\n";

		$this->searchAndReplacePhpFile( $newTexts );
	}

	/**
	 * search and replace php file for new texts
	 * @param $newTexts
	 */
	public function searchAndReplacePhpFile( $newTexts ) {
		$lines = file( $this->phpFileUrl );
		$startLine = 0;
		$endLine = 0;
		foreach ( $lines as $lineNumber => $line ) {
			if ( $lineNumber == 0 ) {
				continue;
			}

			if ( strpos( "'" . $line ."'", '/** ==EditorLocalizationList==START== **/' ) !== false ) {
				$startLine = $lineNumber;
			}

			if ( strpos( $line, '/** ==EditorLocalizationList==END== **/' ) !== false ) {
				$endLine = $lineNumber;
			}

			if ( $lineNumber >= $startLine && $startLine ) {
				unset( $lines[ $lineNumber ] );
			}

			if ( $lineNumber == $endLine ) {
				$lines[ $lineNumber ] = $newTexts;
				break;
			}
		}
		ksort( $lines );
		file_put_contents( $this->phpFileUrl, implode( '', $lines ) );
	}
}

$editorLocalizeFile =  'app/src/Editor/EditorLocalization.php';
$jsonFile = 'resources/scripts/i18n/en/locale.json';
$translateHandler = new I18nService( $editorLocalizeFile, $jsonFile );
