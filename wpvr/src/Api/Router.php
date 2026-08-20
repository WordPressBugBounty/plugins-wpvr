<?php

namespace RexTheme\WPVR\Api;

use RexTheme\WPVR\Api\Controllers\TourController;
use RexTheme\WPVR\Api\Services\TourService;
use RexTheme\WPVR\Api\Repositories\TourRepository;
use RexTheme\WPVR\Api\Transformers\TourTransformer;

class Router {

    public function register(): void {
        $is_pro = wpvr_is_pro_active();

        $controller = new TourController(
            new TourService(
                new TourRepository(),
                new TourTransformer( $is_pro ),
                $is_pro
            )
        );

        $controller->register_routes();

        do_action( 'wpvr_rest_routes_registered' );
    }
}
