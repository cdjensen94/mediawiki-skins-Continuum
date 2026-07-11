<?php
namespace ContinuumUniverses\Skins\Continuum;

use FatalError;

/**
 * A namespace for Continuum constants for internal Continuum usage only. **Do not rely on this file as an
 * API as it may change without warning at any time.**
 * @package Continuum
 * @internal
 */
final class Constants {
	/**
	 * This is tightly coupled to the ValidSkinNames field in skin.json.
	 * @var string
	 */
	public const SKIN_NAME_MODERN = 'continuum';
	/**
	 * @var string
	 */
	public const SKIN_VERSION_LATEST = '2.2.0';

	// These are tightly coupled to skin.json's configs. See skin.json for documentation.
	/**
	 * @var string
	 */
	public const CONFIG_KEY_DEFAULT_SKIN_VERSION_FOR_NEW_ACCOUNTS =
		'ContinuumDefaultSkinVersionForNewAccounts';

	/**
	 * @var string
	 */
	public const PREF_KEY_SKIN = 'skin';

	// These are used in the Feature Management System.
	/**
	 * Also known as `$wgFullyInitialised`. Set to true in core/includes/Setup.php.
	 * @var string
	 */
	public const CONFIG_KEY_FULLY_INITIALISED = 'FullyInitialised';

	/**
	 * @var string
	 */
	public const REQUIREMENT_FULLY_INITIALISED = 'FullyInitialised';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LOGGED_IN = 'LoggedIn';

	/**
	 * @var string
	 */
	public const FEATURE_LANGUAGE_IN_HEADER = 'LanguageInHeader';

	/**
	 * @var string
	 */
	public const CONFIG_KEY_LANGUAGE_IN_HEADER = 'ContinuumLanguageInHeader';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LANGUAGE_IN_HEADER = 'LanguageInHeader';

	/**
	 * @var string
	 */
	public const CONFIG_STICKY_HEADER = 'ContinuumStickyHeader';

	/**
	 * @var string
	 */
	public const REQUIREMENT_STICKY_HEADER = 'StickyHeader';

	/**
	 * @var string
	 */
	public const FEATURE_STICKY_HEADER = 'StickyHeader';

	/**
	 * Defines whether an A/B test is running.
	 *
	 * @var string
	 */
	public const CONFIG_WEB_AB_TEST_ENROLLMENT = 'ContinuumWebABTestEnrollment';

	/**
	 * The `mediawiki.searchSuggest` protocol piece of the SearchSatisfaction instrumention reads
	 * the value of an element with the "data-search-loc" attribute and set the event's
	 * `inputLocation` property accordingly.
	 *
	 * When the search widget is moved as part of the "Search 1: Search widget move" feature, the
	 * "data-search-loc" attribute is set to this value.
	 *
	 * See also:
	 * - https://www.mediawiki.org/wiki/Reading/Web/Desktop_Improvements/Features#Search_1:_Search_widget_move
	 * - https://phabricator.wikimedia.org/T261636 and https://phabricator.wikimedia.org/T256100
	 * - https://gerrit.wikimedia.org/g/mediawiki/core/+/61d36def2d7adc15c88929c824b444f434a0511a/resources/src/mediawiki.searchSuggest/searchSuggest.js#106
	 *
	 * @var string
	 */
	public const SEARCH_BOX_INPUT_LOCATION_MOVED = 'header-moved';

	/**
	 * Similar to `Constants::SEARCH_BOX_INPUT_LOCATION_MOVED`, when the search widget hasn't been
	 * moved, the "data-search-loc" attribute is set to this value.
	 *
	 * @var string
	 */
	public const SEARCH_BOX_INPUT_LOCATION_DEFAULT = 'header-navigation';

	/**
	 * @var string
	 */
	public const REQUIREMENT_IS_MAIN_PAGE = 'IsMainPage';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LANGUAGE_IN_MAIN_PAGE_HEADER = 'LanguageInMainPageHeader';

	/**
	 * @var string
	 */
	public const CONFIG_LANGUAGE_IN_MAIN_PAGE_HEADER = 'ContinuumLanguageInMainPageHeader';

	/**
	 * @var string
	 */
	public const FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER = 'LanguageInMainPageHeader';

	/**
	 * @var string
	 */
	public const WEB_AB_TEST_ARTICLE_ID_FACTORY_SERVICE = 'WikimediaEvents.WebABTestArticleIdFactory';

	/**
	 * @var string
	 */
	public const FEATURE_PAGE_TOOLS_PINNED = 'PageToolsPinned';

	/**
	 * @var string
	 */
	public const REQUIREMENT_PAGE_TOOLS_PINNED = 'PageToolsPinned';

	/**
	 * @var string
	 */
	public const PREF_KEY_PAGE_TOOLS_PINNED = 'continuum-page-tools-pinned';

	/**
	 * @var string
	 */
	public const REQUIREMENT_TOC_PINNED = 'TOCPinned';

	/**
	 * @var string
	 */
	public const PREF_KEY_TOC_PINNED = 'continuum-toc-pinned';

	/**
	 * @var string
	 */
	public const FEATURE_TOC_PINNED = 'TOCPinned';

	/**
	 * @var string
	 */
	public const FEATURE_MAIN_MENU_PINNED = 'MainMenuPinned';

	/**
	 * @var string
	 */
	public const REQUIREMENT_MAIN_MENU_PINNED = 'MainMenuPinned';

	/**
	 * @var string
	 */
	public const PREF_KEY_MAIN_MENU_PINNED = 'continuum-main-menu-pinned';

	/**
	 * @var string
	 */
	public const FEATURE_LIMITED_WIDTH = 'LimitedWidth';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LIMITED_WIDTH = 'LimitedWidth';

	/**
	 * @var string
	 */
	public const PREF_KEY_LIMITED_WIDTH = 'continuum-limited-width';

	/**
	 * @var string
	 */
	public const FEATURE_LIMITED_WIDTH_CONTENT = 'LimitedWidthContent';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LIMITED_WIDTH_CONTENT = 'LimitedWidthContent';

	/**
	 * @var bool
	 */
	public const CONFIG_DEFAULT_LIMITED_WIDTH = 1;

	/**
	 * @var string
	 */
	public const PREF_KEY_FONT_SIZE = 'continuum-font-size';

	/**
	 * @var string
	 */
	public const FEATURE_FONT_SIZE = 'CustomFontSize';

	/**
	 * @var string
	 */
	public const REQUIREMENT_FONT_SIZE = 'CustomFontSize';

	/**
	 * @var string
	 */
	public const FEATURE_APPEARANCE_PINNED = 'AppearancePinned';

	/**
	 * @var string
	 */
	public const REQUIREMENT_APPEARANCE_PINNED = 'AppearancePinned';

	/**
	 * @var string
	 */
	public const PREF_KEY_APPEARANCE_PINNED = 'continuum-appearance-pinned';

	/**
	 * @var string
	 */
	public const CONFIG_KEY_NIGHT_MODE = 'ContinuumNightMode';

	/**
	 * @var string
	 */
	public const FEATURE_NIGHT_MODE = 'NightMode';

	/**
	 * @var string
	 */
	public const REQUIREMENT_NIGHT_MODE = 'NightMode';

	/**
	 * @var string
	 */
	public const PREF_KEY_NIGHT_MODE = 'continuum-theme';

	/**
	 * @var string
	 */
	public const REQUIREMENT_PREF_NIGHT_MODE = 'PrefNightMode';

	/**
	 * @var string
	 */
	public const PREF_NIGHT_MODE = 'PrefNightMode';

	/**
	 * @var string
	 */
	public const CONTINUUM_2022_BETA_KEY = 'continuum-beta-feature';

	/**
	 * @var array
	 */
	public const CONTINUUM_BETA_FEATURES = [
		self::CONFIG_KEY_NIGHT_MODE,
	];

	/**
	 * @var string
	 */
	public const THEME_IMPERIAL_NIGHT = 'imperial-night';
	/**
	 * @var string
	 */
	public const THEME_UBLA_DAY = 'ubla-day';
	/**
	 * @var string
	 */
	public const THEME_UBLA_NIGHT = 'ubla-night';
	/**
	 * @var string
	 */
	public const THEME_VERDANT = 'verdant';
	/**
	 * @var string
	 */
	public const THEME_ADAMS_CHAOS = 'adams-chaos';
	/**
	 * @var string
	 */
	public const THEME_ECTOPLASM_PURPLE = 'ectoplasm-purple';
	/**
	 * @var string
	 */
	public const THEME_ECTOPLASM_GREEN = 'ectoplasm-green';
	/**
	 * @var string
	 */
	public const THEME_KRISTENS_CURATIONS = 'kristens-curations';
	/**
	 * @var string
	 */
	public const THEME_SODAHAN = 'sodahan';
	/**
	 * @var string
	 */
	public const THEME_BALORIAN = 'balorian';
	/**
	 * @var string
	 */
	public const THEME_SLUGGO = 'sluggo';
	/**
	 * @var string
	 */
	public const THEME_WIKIPEDIA_DEFAULT = 'wikipedia-default';
	/**
	 * @var string
	 */
	public const THEME_WIKIPEDIA_DARKMODE = 'wikipedia-darkmode';
	
	// Font Schemes
	/**
	 * @var string
	 */
	public const FONT_SCHEME_DEFAULT = 'default';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_SERIF = 'serif';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_SANS = 'sans-serif';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_MONOSPACE = 'monospace';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_ANTIQUE = 'antiqua';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_CELTICA = 'celtica';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_GERMANICA = 'germanica';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_METAMORPHOUS = 'metamorphous';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_PHOSPHORUS = 'phosphorus';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_MEDIEVAL = 'medieval';
	/**
	 * @var string
	 */
	public const FONT_SCHEME_OPENDYSLEXIC = 'opendyslexic';
	/**
	 * @var string
	 */
	public const PREF_KEY_FONT_SCHEME = 'continuum-font-scheme';
	/**
	 * This class is for namespacing constants only. Forbid construction.
	 * @throws FatalError
	 * @return never
	 */
	private function __construct() {
		throw new FatalError( "Cannot construct a utility class." );
	}
}
