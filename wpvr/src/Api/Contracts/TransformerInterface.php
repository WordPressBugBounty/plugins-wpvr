<?php

namespace RexTheme\WPVR\Api\Contracts;

interface TransformerInterface {
    public function toApi( array $raw ): array;
    public function fromApi( array $data ): array;
}
