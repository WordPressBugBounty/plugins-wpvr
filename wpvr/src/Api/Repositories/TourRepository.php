<?php

namespace RexTheme\WPVR\Api\Repositories;

use RexTheme\WPVR\Api\Contracts\RepositoryInterface;

class TourRepository implements RepositoryInterface {

    const META_KEY = 'panodata';

    public function find( int $id ): array {
        $raw = get_post_meta( $id, self::META_KEY, true );
        return is_array( $raw ) ? $raw : [];
    }

    public function save( int $id, array $data ): bool {
        $result = update_post_meta( $id, self::META_KEY, $data );
        // update_post_meta returns false both on real failure and when value is unchanged
        if ( false === $result ) {
            return get_post_meta( $id, self::META_KEY, true ) == $data;
        }
        return true;
    }
}
