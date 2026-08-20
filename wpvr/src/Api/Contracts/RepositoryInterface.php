<?php

namespace RexTheme\WPVR\Api\Contracts;

interface RepositoryInterface {
    public function find( int $id ): array;
    public function save( int $id, array $data ): bool;
}
