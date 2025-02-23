<?php

class ContinuumTemplate extends BaseTemplate {
    public function execute() {
        $this->html( 'headelement' );
        ?>
        <div id="content">
            <h1><?php $this->text( 'title' ); ?></h1>
            <div id="bodyContent">
                <?php $this->html( 'bodycontent' ); ?>
            </div>
        </div>
        <?php
        $this->html( 'footer' );
    }
}
