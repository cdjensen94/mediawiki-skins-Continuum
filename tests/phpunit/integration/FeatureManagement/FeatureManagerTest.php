<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Skins\Continuum\Constants;
use MediaWiki\Skins\Continuum\FeatureManagement\FeatureManager;
use MediaWiki\Title\Title;

/**
 * @coversDefaultClass \MediaWiki\Skins\Continuum\FeatureManagement\FeatureManager
 */
class FeatureManagerTest extends \MediaWikiIntegrationTestCase {
	private function newFeatureManager(): FeatureManager {
		return new FeatureManager(
			$this->getServiceContainer()->getUserOptionsLookup(),
			RequestContext::getMain()
		);
	}

	public static function provideGetFeatureBodyClass() {
		return [
			[ CONSTANTS::FEATURE_LIMITED_WIDTH, false, 'continuum-feature-limited-width-clientpref-0' ],
			[ CONSTANTS::FEATURE_LIMITED_WIDTH, true, 'continuum-feature-limited-width-clientpref-1' ],
			[ CONSTANTS::FEATURE_TOC_PINNED, false, 'continuum-feature-toc-pinned-clientpref-0' ],
			[ CONSTANTS::FEATURE_TOC_PINNED, true, 'continuum-feature-toc-pinned-clientpref-1' ],
			[ CONSTANTS::FEATURE_NIGHT_MODE, false, 'continuum-feature-night-mode-disabled' ],
			[ CONSTANTS::FEATURE_NIGHT_MODE, true, 'continuum-feature-night-mode-enabled' ],
		];
	}

	/**
	 * @dataProvider provideGetFeatureBodyClass
	 * @covers ::getFeatureBodyClass basic usage
	 */
	public function testGetFeatureBodyClass( $feature, $enabled, $expected ) {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerSimpleRequirement( 'requirement', $enabled );
		$featureManager->registerFeature( $feature, [ 'requirement' ] );
		// Title is required for checking whether or not the feature is excluded
		// based on page title.
		$context = RequestContext::getMain();
		$context->setTitle( Title::makeTitle( NS_MAIN, 'test' ) );
		$this->assertSame( [ $expected ], $featureManager->getFeatureBodyClass() );
	}

	/**
	 * @covers ::getFeatureBodyClass returning multiple feature classes
	 */
	public function testGetFeatureBodyClassMultiple() {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerSimpleRequirement( 'requirement', true );
		$featureManager->registerSimpleRequirement( 'disabled', false );
		$featureManager->registerFeature( Constants::FEATURE_STICKY_HEADER, [ 'requirement' ] );
		$featureManager->registerFeature( Constants::FEATURE_NIGHT_MODE, [ 'requirement' ] );
		$featureManager->registerFeature( Constants::FEATURE_LIMITED_WIDTH_CONTENT, [ 'disabled' ] );
		$this->assertEquals(
			[
				'continuum-feature-sticky-header-enabled',
				'continuum-feature-night-mode-enabled',
				'continuum-feature-limited-width-content-disabled'
			],
			$featureManager->getFeatureBodyClass()
		);
	}

	public static function provideGetFeatureBodyClassNightModeQueryParam() {
		return [
			[ 0, 'skin-theme-clientpref-day' ],
			[ 1, 'skin-theme-clientpref-night' ],
			[ 2, 'skin-theme-clientpref-os' ],
			[ 'day', 'skin-theme-clientpref-day' ],
			[ 'night', 'skin-theme-clientpref-night' ],
			[ 'os', 'skin-theme-clientpref-os' ],
			[ 'invalid', 'skin-theme-clientpref-day' ]
		];
	}

	/**
	 * @dataProvider provideGetFeatureBodyClassNightModeQueryParam
	 * @covers ::getFeatureBodyClass pref night mode specifics - disabled pages
	 */
	public function testGetFeatureBodyClassNightModeQueryParam( $value, $expected ) {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerFeature( CONSTANTS::PREF_NIGHT_MODE, [] );

		$context = RequestContext::getMain();
		$context->setTitle( Title::makeTitle( NS_MAIN, 'test' ) );

		$request = $context->getRequest();
		$request->setVal( 'continuumnightmode', $value );

		$this->assertEquals( [ $expected ], $featureManager->getFeatureBodyClass() );
	}

	/** ensure the class is present when disabled and absent when not */
	public static function provideGetFeatureBodyClassNightModeDisabled() {
		return [
			[ true ], [ false ]
		];
	}

	/**
	 * @dataProvider provideGetFeatureBodyClassNightModeDisabled
	 * @covers ::getFeatureBodyClass pref night mode specifics - disabled pages
	 */
	public function testGetFeatureBodyClassNightModeDisabled( $disabled ) {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerFeature( CONSTANTS::PREF_NIGHT_MODE, [] );

		$context = RequestContext::getMain();
		$context->setTitle( Title::makeTitle( NS_MAIN, 'Main Page' ) );

		$this->overrideConfigValues( [ 'ContinuumNightModeOptions' => [ 'exclude' => [ 'mainpage' => $disabled ] ] ] );
		$this->assertEquals(
			in_array( 'skin-theme-clientpref--excluded', $featureManager->getFeatureBodyClass() ),
			$disabled
		);
	}
}
