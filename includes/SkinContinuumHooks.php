<?php
namespace ContinuumUniverses\Skins\Continuum;

class SkinContinuumHooks {
    /**
     * Manifest callback for skin registration.
     * Runs once when the skin is loaded by ExtensionRegistry.
     */
    public static function onRegistration(): void {
        // Build web path to this skin's assets
        $sp   = $GLOBALS['wgScriptPath'] ?? '';
        $base = rtrim( $sp, '/' ) . '/skins/Continuum/resources/assets';

        // Make sure group exists
        $GLOBALS['wgFooterIcons']['poweredby'] ??= [];

        // Add/override badges (edit paths as needed)
        $GLOBALS['wgFooterIcons']['poweredby']['continuum-universes'] = [
            'src' => "$base/poweredby-continuum.svg",
            'url' => 'https://continuum-universes.com/',
            'alt' => 'Powered by Continuum',
        ];
        $GLOBALS['wgFooterIcons']['poweredby']['googlefonts'] = [
            'src' => "$base/poweredby-google-fonts.svg",
            'url' => 'https://fonts.google.com/',
            'alt' => 'Powered by Google Fonts',
        ];

        $GLOBALS['wgFooterIcons']['poweredby']['mediawiki'] = [
             'src' => "$base/poweredby-mediawiki.svg",
             'url' => 'https://www.mediawiki.org/',
             'alt' => 'Powered by MediaWiki',
        ];
    }
}
