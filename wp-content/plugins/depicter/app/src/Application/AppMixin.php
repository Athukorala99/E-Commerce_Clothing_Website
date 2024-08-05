<?php

namespace Depicter\Application;

use Averta\WordPress\Cache\WPCache;
use Averta\WordPress\Event\Action;
use Averta\WordPress\Event\Filter;
use Averta\WordPress\Models\WPOptions;
use Depicter;
use Depicter\Database\Repository\DocumentRepository;
use Depicter\Database\Repository\MetaRepository;
use Depicter\Document\Manager as DocumentManager;
use Depicter\DataSources\Manager as DataSources;
use Depicter\Rules\Conditions\Manager as ConditionsManager;
use Depicter\Editor\Editor;
use Depicter\Editor\EditorData;
use Depicter\Front\Front;
use Depicter\Front\Symbols;
use Depicter\Modules\Modules;
use Depicter\Services\ClientService;
use Depicter\Services\ExportService;
use Depicter\Services\ImportService;
use Depicter\Services\MediaBridge;
use Depicter\Services\RemoteAPIService;
use Depicter\Services\StorageService;
use Depicter\Services\AuthorizationService;
use Depicter\Services\AuthenticationService;
use Depicter\WordPress\DeactivationFeedbackService;
use Depicter\WordPress\SchedulingService;
use Depicter\WordPress\FileUploaderService;
use Depicter\Services\AIWizardService;
use WPEmergeAppCore\AppCore\AppCore;
use Depicter\Services\GoogleFontsService;


/**
 * "@mixin" annotation for better IDE support.
 * This class is not meant to be used in any other capacity.
 *
 * @codeCoverageIgnore
 */
final class AppMixin
{

	/**
	 * Get the Application instance.
	 *
	 * @codeCoverageIgnore
	 * @return Depicter
	 */
	public static function app() {}

	/**
	 * Get the Theme service instance.
	 *
	 * @return AppCore
	 */
	public static function core(): AppCore {}

	/**
	 * @return WPOptions
	 */
	public static function options(): WPOptions {}

	/**
	 * @return DocumentManager
	 */
	public static function document(): DocumentManager {}

	/**
	 * @return Action
	 */
	public static function action(): Action {}

	/**
	 * @return Filter
	 */
	public static function filter(): Filter {}

	/**
	 * @return Editor
	 */
	public static function editor(): Editor {}

	/**
	 * @return MediaBridge
	 */
	public static function media(): MediaBridge {}

	/**
	 * @return RemoteAPIService
	 */
	public static function remote(): RemoteAPIService {}

	/**
	 * @return DocumentRepository
	 */
	public static function documentRepository():DocumentRepository {}

	/**
	 * @return Front
	 */
	public static function front(): Front{}

	/**
	 * @return StorageService
	 */
	public static function storage(): StorageService {}

	/**
	 * Retrieves the cache module
	 *
	 * @param string $module
	 *
	 * @return WPCache
	 */
	public static function cache( $module = 'api' ): WPCache{}

	/**
	 * @return DeactivationFeedbackService
	 */
	public static function deactivationFeedback(): DeactivationFeedbackService{}

	/**
	 * @return ClientService
	 */
	public static function client(): ClientService{}

	/**
	 * @return FileUploaderService
	 */
	public static function fileUploader(): FileUploaderService{}

	/**
	 * @return DataSources
	 */
	public static function dataSource(): DataSources{}

	/**
	 * @return ExportService
	 */
	public static function exportService(): ExportService{}

	/**
	 * @return ImportService
	 */
	public static function importService(): ImportService{}

	/**
	 * @return Symbols
	 */
	public static function symbolsProvider(): Symbols{}

	/**
	 * @return AuthorizationService
	 */
	public static function authorization(): AuthorizationService{}

	/**
	 * @return AuthenticationService
	 */
	public static function auth(): AuthenticationService {}

	/**
	 * @return Modules
	 */
	public static function modules(): Modules{}

	/**
	 * @return AIWizardService
	 */
	public static function AIWizard(): AIWizardService{}

	/**
	 * @return EditorData
	 */
	public static function editorData(): EditorData{}

	/**
	 * @return GoogleFontsService
	 */
	public static function googleFontsService() {}

	/**
	 * @return MetaRepository
	 */
	public static function metaRepository(): MetaRepository {}

	/**
	 * @return SchedulingService
	 */
	public static function schedule(): SchedulingService{}

	/**
	 * @return ConditionsManager
	 */
	public static function conditionsManager(): ConditionsManager {}
}
