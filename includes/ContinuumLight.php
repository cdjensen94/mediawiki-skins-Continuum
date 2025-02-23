<?php

use MediaWiki\Skins\Continuum\SkinContinuumLight;

class ContinuumLight extends SkinContinuumLight {
    public $skinname = 'continuum-light';
    public $stylename = 'continuum-light';
    public $template = 'ContinuumTemplate'; // Or your custom template if you have one

    public function getDefaultModules() {
        return [ 'skins.continuumlight.styles' ];
    }
}
