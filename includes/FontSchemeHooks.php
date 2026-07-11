<?php
namespace ContinuumUniverses\Skins\Continuum;

use MediaWiki\MediaWikiServices;
use OutputPage;
use Skin;

class FontSchemeHooks {
    public static function onBeforePageDisplay( OutputPage $out, Skin $skin ): void {
        $uom   = MediaWikiServices::getInstance()->getUserOptionsManager();
        $user  = $out->getUser();
        $req   = $out->getRequest();

        $fontscheme = $uom->getOption( $user, 'continuum-font-scheme', 'metamorphous' );
        // anon cookie fallback
        if ( !$user->isRegistered() ) {
            $cookie = $req->getCookie( 'continuum-font-scheme' );
            if ( $cookie ) {
                $fontscheme = $cookie;
            }
        }

        $valid = [ 'metamorphous', 'opendyslexic', 'monospace', 'phosphorus', 'serif', 'sans-serif', 'antiqua', 'celtica', 'germanica', 'medieval' ];
        if ( !in_array( $fontscheme, $valid, true ) ) {
            $fontscheme = 'metamorphous';
        }

        $out->addBodyClasses( [ 'font-scheme-' . $fontscheme ] );
        $out->addHtmlClasses( [ 'continuum-font-scheme-clientpref-' . $fontscheme ] ); // <-- critical
    }
}