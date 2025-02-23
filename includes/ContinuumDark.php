<?php

use MediaWiki\Skins\Continuum\SkinContinuumDark;

class ContinuumDark extends SkinContinuumDark {
    public $skinname = 'continuum-dark';
    public $stylename = 'continuum-dark';
    public $template = 'ContinuumTemplate'; // Or your custom template if you have one

    public function getDefaultModules() {
        return [ 'skins.continuumdark.styles' ];
    }
}
