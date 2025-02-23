<?php

use MediaWiki\Skins\Continuum\SkinContinuumMedium;

class ContinuumMedium extends SkinContinuumMedium {
    public $skinname = 'continuum-medium';
    public $stylename = 'continuum-medium';
    public $template = 'ContinuumTemplate'; // Or your custom template if you have one

    public function getDefaultModules() {
        return [ 'skins.continuummedium.styles' ];
    }
}
