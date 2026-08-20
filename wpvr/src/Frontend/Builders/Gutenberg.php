<?php

namespace RexTheme\WPVR\Frontend\Builders;

/**
 * Wrapper for the Gutenberg block registration.
 *
 * In Phase 1 the wpvr/wpvr-block registration lives as inline code inside
 * legacy/wpvr.php and runs when Bootstrap loads that file via require_once.
 * This class exists as the PSR-4 home for the block integration; Phase 2
 * will migrate the registration logic here.
 */
class Gutenberg {

    public function __construct() {
        // Block registration handled by legacy/wpvr.php inline code in Phase 1.
    }
}
