<?php

namespace MediaWiki\Skins\Continuum;

use MediaWiki\Html\Html;
use MediaWiki\Languages\LanguageConverterFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentAppearance;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentButton;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentDropdown;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentLanguageDropdown;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentMainMenu;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentPageTools;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentPinnableContainer;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentSearchBox;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentStickyHeader;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentTableOfContents;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentUserLinks;
use MediaWiki\Skins\Continuum\Components\ContinuumComponentVariants;
use MediaWiki\Skins\Continuum\FeatureManagement\FeatureManager;
use MediaWiki\Skins\Continuum\FeatureManagement\FeatureManagerFactory;
use MediaWiki\Output\OutputPage;
use RuntimeException;
use SkinMustache;
use SkinTemplate;

/**
 * @ingroup Skins
 * @package Continuum
 * @internal
 */
class SkinContinuumDark extends SkinMustache {
	public $skinname = 'continuum-dark';
    public $stylename = 'continuum-dark';
    public $template = 'skin-dark';

    public function initPage(OutputPage $out) {
        parent::initPage($out);
        $out->addModuleStyles('skins.continuumdark.styles');
    }
	private const STICKY_HEADER_ENABLED_CLASS = 'Continuum-sticky-header-enabled';
	/** @var null|array for caching purposes */
	private $languages;

	private LanguageConverterFactory $languageConverterFactory;
	private FeatureManagerFactory $featureManagerFactory;
	private ?FeatureManager $featureManager = null;

	public function __construct(
		LanguageConverterFactory $languageConverterFactory,
		FeatureManagerFactory $featureManagerFactory,
		array $options
	) {
		parent::__construct( $options );
		$this->languageConverterFactory = $languageConverterFactory;
		// Cannot use the context in the constructor, setContext is called after construction
		$this->featureManagerFactory = $featureManagerFactory;
	}

	/**
	 * @inheritDoc
	 */
	protected function runOnSkinTemplateNavigationHooks( SkinTemplate $skin, &$content_navigation ) {
		parent::runOnSkinTemplateNavigationHooks( $skin, $content_navigation );
		Hooks::onSkinTemplateNavigation( $skin, $content_navigation );
	}

	/**
	 * @inheritDoc
	 */
	public function isResponsive() {
		// Check it's enabled by user preference and configuration
		$responsive = parent::isResponsive() && $this->getConfig()->get( 'ContinuumResponsive' );
		// For historic reasons, the viewport is added when Continuum is loaded on the mobile
		// domain. This is only possible for 3rd parties or by useskin parameter as there is
		// no preference for changing mobile skin. Only need to check if $responsive is falsey.
		if ( !$responsive && ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) ) {
			$mobFrontContext = MediaWikiServices::getInstance()->getService( 'MobileFrontend.Context' );
			if ( $mobFrontContext->shouldDisplayMobileView() ) {
				return true;
			}
		}
		return $responsive;
	}

	/**
	 * Whether or not toc data is available
	 *
	 * @param array $parentData Template data
	 * @return bool
	 */
	private function isTocAvailable( array $parentData ): bool {
		return !empty( $parentData['data-toc'][ 'array-sections' ] );
	}

	/**
	 * This should be upstreamed to the Skin class in core once the logic is finalized.
	 * Returns false if the page is a special page without any languages, or if an action
	 * other than view is being used.
	 *
	 * @return bool
	 */
	private function canHaveLanguages(): bool {
		$action = $this->getActionName();

		// FIXME: This logic should be moved into the ULS extension or core given the button is hidden,
		// it should not be rendered, short term fix for T328996.
		if ( $action === 'history' ) {
			return false;
		}

		$title = $this->getTitle();
		return !$title || !$title->isSpecialPage()
			// Defensive programming - if a special page has added languages explicitly, best to show it.
			|| $this->getLanguagesCached();
	}

	/**
	 * Remove the add topic button from data-views if present
	 *
	 * @param array &$parentData Template data
	 * @return bool An add topic button was removed
	 */
	private function removeAddTopicButton( array &$parentData ): bool {
		$views = $parentData['data-portlets']['data-views']['array-items'];
		$hasAddTopicButton = false;
		$html = '';
		foreach ( $views as $i => $view ) {
			if ( $view['id'] === 'ca-addsection' ) {
				array_splice( $views, $i, 1 );
				$hasAddTopicButton = true;
				continue;
			}
			$html .= $view['html-item'];
		}
		$parentData['data-portlets']['data-views']['array-items'] = $views;
		$parentData['data-portlets']['data-views']['html-items'] = $html;
		return $hasAddTopicButton;
	}

	private function getFeatureManager(): FeatureManager {
		if ( $this->featureManager === null ) {
			$this->featureManager = $this->featureManagerFactory->createFeatureManager( $this->getContext() );
		}
		return $this->featureManager;
	}

	/**
	 * @param string $location Either 'top' or 'bottom' is accepted.
	 * @return bool
	 */
	protected function isLanguagesInContentAt( string $location ): bool {
		if ( !$this->canHaveLanguages() ) {
			return false;
		}
		$featureManager = $this->getFeatureManager();
		$inContent = $featureManager->isFeatureEnabled(
			Constants::FEATURE_LANGUAGE_IN_HEADER
		);
		$title = $this->getTitle();
		$isMainPage = $title ? $title->isMainPage() : false;

		switch ( $location ) {
			case 'top':
				return $isMainPage ? $inContent && $featureManager->isFeatureEnabled(
					Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER
				) : $inContent;
			case 'bottom':
				return $inContent && $isMainPage && !$featureManager->isFeatureEnabled(
					Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER
				);
			default:
				throw new RuntimeException( 'unknown language button location' );
		}
	}

	/**
	 * Whether or not the languages are out of the sidebar and in the content either at
	 * the top or the bottom.
	 *
	 * @return bool
	 */
	final protected function isLanguagesInContent(): bool {
		return $this->isLanguagesInContentAt( 'top' ) || $this->isLanguagesInContentAt( 'bottom' );
	}

	/**
	 * Calls getLanguages with caching.
	 *
	 * @return array
	 */
	protected function getLanguagesCached(): array {
		if ( $this->languages === null ) {
			$this->languages = $this->getLanguages();
		}
		return $this->languages;
	}

	/**
	 * Check whether ULS is enabled
	 *
	 * @return bool
	 */
	final protected function isULSExtensionEnabled(): bool {
		return ExtensionRegistry::getInstance()->isLoaded( 'UniversalLanguageSelector' );
	}

	/**
	 * Check whether Visual Editor Tab Position is first
	 *
	 * @param array $dataViews
	 * @return bool
	 */
	final protected function isVisualEditorTabPositionFirst( $dataViews ): bool {
		$names = [ 've-edit', 'edit' ];
		// find if under key 'name' 've-edit' or 'edit' is the before item in the array
		for ( $i = 0; $i < count( $dataViews[ 'array-items' ] ); $i++ ) {
			if ( in_array( $dataViews[ 'array-items' ][ $i ][ 'name' ], $names ) ) {
				return $dataViews[ 'array-items' ][ $i ][ 'name' ] === $names[ 0 ];
			}
		}
		return false;
	}

	/**
	 * Show the ULS button if it's modern Continuum, languages in header is enabled,
	 * the ULS extension is enabled, and we are on a subect page. Hide it otherwise.
	 * There is no point in showing the language button if ULS extension is unavailable
	 * as there is no ways to add languages without it.
	 * @return bool
	 */
	protected function shouldHideLanguages(): bool {
		$title = $this->getTitle();
		$isSubjectPage = $title && $title->exists() && !$title->isTalkPage();
		return !$this->isLanguagesInContent() || !$this->isULSExtensionEnabled() || !$isSubjectPage;
	}

	/**
	 * Merges the `view-overflow` menu into the `action` menu.
	 * This ensures that the previous state of the menu e.g. emptyPortlet class
	 * is preserved.
	 *
	 * @param array $data
	 * @return array
	 */
	private function mergeViewOverflowIntoActions( array $data ): array {
		$portlets = $data['data-portlets'] ?? [];
		$actions = $portlets['data-actions'] ?? ['class' => '', 'html-items' => ''];
		$overflow = $portlets['data-views-overflow'] ?? ['is-empty' => true, 'html-items' => ''];
		
		// If the views overflow menu is not empty, then signal that the more menu has collapsible items
		if (isset($overflow['is-empty']) && !$overflow['is-empty']) {
			$data['data-portlets']['data-actions']['class'] .= ' continuum-has-collapsible-items';
		}
		
		// Safely combine overflow and action items
		$data['data-portlets']['data-actions']['html-items'] = 
			($overflow['html-items'] ?? '') . ($actions['html-items'] ?? '');
		
		return $data;
		
	}

	/**
	 * @inheritDoc
	 */
	public function getHtmlElementAttributes() {
		$original = parent::getHtmlElementAttributes();
		$featureManager = $this->getFeatureManager();
		$original['class'] .= ' ' . implode( ' ', $featureManager->getFeatureBodyClass() );
		// The sticky header is now always enabled, so we apply the class unconditionally.
		$original['class'] = trim( implode( ' ', [ $original['class'] ?? '', self::STICKY_HEADER_ENABLED_CLASS ] ) );

		return $original;
	}

	/**
	 * Pulls the page tools menu out of $sidebar into $pageToolsMenu
	 *
	 * @param array &$sidebar
	 * @param array &$pageToolsMenu
	 */
	private static function extractPageToolsFromSidebar( array &$sidebar, array &$pageToolsMenu ) {
		$restPortlets = $sidebar[ 'array-portlets-rest' ] ?? [];
		$toolboxMenuIndex = array_search(
			ContinuumComponentPageTools::TOOLBOX_ID,
			array_column(
				$restPortlets,
				'id'
			)
		);

		if ( $toolboxMenuIndex !== false ) {
			// Splice removes the toolbox menu from the $restPortlets array
			// and current returns the first value of array_splice, i.e. the $toolbox menu data.
			$pageToolsMenu = array_splice( $restPortlets, $toolboxMenuIndex );
			$sidebar['array-portlets-rest'] = $restPortlets;
		}
	}

	/**
	 * Get the ULS button label, accounting for the number of available
	 * languages.
	 *
	 * @return array
	 */
	final protected function getULSLabels(): array {
		$numLanguages = count( $this->getLanguagesCached() );

		if ( $numLanguages === 0 ) {
			return [
				'label' => $this->msg( 'Continuum-no-language-button-label' )->text(),
				'aria-label' => $this->msg( 'Continuum-no-language-button-aria-label' )->text()
			];
		} else {
			return [
				'label' => $this->msg( 'Continuum-language-button-label' )->numParams( $numLanguages )->text(),
				'aria-label' => $this->msg( 'Continuum-language-button-aria-label' )->numParams( $numLanguages )->text()
			];
		}
	}

	/**
	 * @return array
	 */
	public function getTemplateData(): array {
		$parentData = parent::getTemplateData();
		$parentData = $this->mergeViewOverflowIntoActions( $parentData );
		$portlets = $parentData['data-portlets'];

		$langData = $portlets['data-languages'] ?? null;
		$config = $this->getConfig();
		$featureManager = $this->getFeatureManager();

		$sidebar = $parentData[ 'data-portlets-sidebar' ];
		$pageToolsMenu = [];
		self::extractPageToolsFromSidebar( $sidebar, $pageToolsMenu );

		$hasAddTopicButton = $config->get( 'ContinuumPromoteAddTopic' ) &&
			$this->removeAddTopicButton( $parentData );

		$langButtonClass = $langData['class'] ?? '';
		$ulsLabels = $this->getULSLabels();
		$user = $this->getUser();
		$localizer = $this->getContext();
		$title = $this->getTitle();

		// If the table of contents has no items, we won't output it.
		// empty array is interpreted by Mustache as falsey.
		$tocComponents = [];
		if ( $this->isTocAvailable( $parentData ) ) {
			// @phan-suppress-next-line SecurityCheck-XSS
			$dataToc = new ContinuumComponentTableOfContents(
				$parentData['data-toc'],
				$localizer,
				$config,
				$featureManager
			);
			$isPinned = $dataToc->isPinned();
			$tocComponents = [
				'data-toc' => $dataToc,
				'data-toc-pinnable-container' => new ContinuumComponentPinnableContainer(
					ContinuumComponentTableOfContents::ID,
					$isPinned
				),
				'data-page-titlebar-toc-dropdown' => new ContinuumComponentDropdown(
					'Continuum-page-titlebar-toc',
					// label
					$this->msg( 'Continuum-toc-collapsible-button-label' ),
					// class
					'Continuum-page-titlebar-toc Continuum-button-flush-left',
					// icon
					'listBullet',
					Html::expandAttributes( [
						'title' => $this->msg( 'Continuum-toc-menu-tooltip' )->text(),
					] )
				),
				'data-page-titlebar-toc-pinnable-container' => new ContinuumComponentPinnableContainer(
					'Continuum-page-titlebar-toc',
					$isPinned
				),
				'data-sticky-header-toc-dropdown' => new ContinuumComponentDropdown(
					'Continuum-sticky-header-toc',
					// label
					$this->msg( 'Continuum-toc-collapsible-button-label' ),
					// class
					'mw-portlet mw-portlet-sticky-header-toc Continuum-sticky-header-toc Continuum-button-flush-left',
					// icon
					'listBullet'
				),
				'data-sticky-header-toc-pinnable-container' => new ContinuumComponentPinnableContainer(
					'Continuum-sticky-header-toc',
					$isPinned
				),
			];
			$this->getOutput()->addHtmlClasses( 'Continuum-toc-available' );
		} else {
			$this->getOutput()->addHtmlClasses( 'Continuum-toc-not-available' );
		}

		$isRegistered = $user->isRegistered();
		$userPage = $isRegistered ? $this->buildPersonalPageItem() : [];

		$components = $tocComponents + [
			'data-add-topic-button' => $hasAddTopicButton ? new ContinuumComponentButton(
				$this->msg( [ 'Continuum-action-addsection', 'skin-action-addsection' ] )->text(),
				'speechBubbleAdd-progressive',
				'ca-addsection',
				'',
				[ 'data-event-name' => 'addsection-header' ],
				'quiet',
				'progressive',
				false,
				$title->getLocalURL( [ 'action' => 'edit', 'section' => 'new' ] )
			) : null,
			'data-variants' => new ContinuumComponentVariants(
				$this->languageConverterFactory,
				$portlets['data-variants'],
				$title->getPageLanguage(),
				$this->msg( 'Continuum-language-variant-switcher-label' )
			),
			'data-Continuum-user-links' => new ContinuumComponentUserLinks(
				$localizer,
				$user,
				$portlets,
				$this->getOptions()['link'],
				$userPage[ 'icon' ] ?? ''
			),
			'data-lang-dropdown' => $langData ? new ContinuumComponentLanguageDropdown(
				$ulsLabels['label'],
				$ulsLabels['aria-label'],
				$langButtonClass,
				count( $this->getLanguagesCached() ),
				$langData['html-items'] ?? '',
				$langData['html-before-portal'] ?? '',
				$langData['html-after-portal'] ?? '',
				$title
			) : null,
			'data-search-box' => new ContinuumComponentSearchBox(
				$parentData['data-search-box'],
				true,
				// is primary mode of search
				true,
				'searchform',
				true,
				$config,
				Constants::SEARCH_BOX_INPUT_LOCATION_MOVED,
				$localizer
			),
			'data-main-menu' => new ContinuumComponentMainMenu(
				$sidebar,
				$portlets['data-languages'] ?? [],
				$localizer,
				$user,
				$featureManager,
				$this,
			),
			'data-main-menu-dropdown' => new ContinuumComponentDropdown(
				ContinuumComponentMainMenu::ID . '-dropdown',
				$this->msg( ContinuumComponentMainMenu::ID . '-label' )->text(),
				ContinuumComponentMainMenu::ID . '-dropdown' . ' Continuum-button-flush-left Continuum-button-flush-right',
				'menu',
				Html::expandAttributes( [
					'title' => $this->msg( 'Continuum-main-menu-tooltip' )->text(),
				] )
			),
			'data-page-tools' => new ContinuumComponentPageTools(
				array_merge( [ $portlets['data-actions'] ?? [] ], $pageToolsMenu ),
				$localizer,
				$featureManager
			),
			'data-page-tools-dropdown' => new ContinuumComponentDropdown(
				ContinuumComponentPageTools::ID . '-dropdown',
				$this->msg( 'toolbox' )->text(),
				ContinuumComponentPageTools::ID . '-dropdown',
			),
			'data-appearance' => new ContinuumComponentAppearance( $localizer, $featureManager ),
			'data-appearance-dropdown' => new ContinuumComponentDropdown(
				'Continuum-appearance-dropdown',
				$this->msg( 'Continuum-appearance-label' )->text(),
				'',
				'appearance',
				Html::expandAttributes( [
					'title' => $this->msg( 'Continuum-appearance-tooltip' ),
				] )
			),
			'data-Continuum-sticky-header' => new ContinuumComponentStickyHeader(
				$localizer,
				new ContinuumComponentSearchBox(
					$parentData['data-search-box'],
					// Collapse inside search box is disabled.
					false,
					false,
					'Continuum-sticky-search-form',
					false,
					$config,
					Constants::SEARCH_BOX_INPUT_LOCATION_MOVED,
					$localizer
				),
				// Show sticky ULS if the ULS extension is enabled and the ULS in header is not hidden
				$this->isULSExtensionEnabled() && !$this->shouldHideLanguages() ?
					new ContinuumComponentButton(
						$ulsLabels[ 'label' ],
						'wikimedia-language',
						'p-lang-btn-sticky-header',
						'mw-interlanguage-selector',
						[
							'tabindex' => '-1',
							'data-event-name' => 'ui.dropdown-p-lang-btn-sticky-header'
						],
						'quiet'
					) : null,
				$this->isVisualEditorTabPositionFirst( $portlets[ 'data-views' ] )
			),
		];

		foreach ( $components as $key => $component ) {
			// Array of components or null values.
			if ( $component ) {
				$parentData[$key] = $component->getTemplateData();
			}
		}

		return array_merge( $parentData, [
			'is-language-in-content' => $this->isLanguagesInContent(),
			'has-buttons-in-content-top' => $this->isLanguagesInContentAt( 'top' ) || $hasAddTopicButton,
			'is-language-in-content-bottom' => $this->isLanguagesInContentAt( 'bottom' ),
			// Cast empty string to null
			'html-subtitle' => $parentData['html-subtitle'] === '' ? null : $parentData['html-subtitle'],
		] );
	}
}
