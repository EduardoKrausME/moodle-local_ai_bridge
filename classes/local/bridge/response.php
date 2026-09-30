<?php
namespace local_ai_bridge\local\bridge;

defined('MOODLE_INTERNAL') || die();

final class response {
    public function __construct(
        public readonly string $text,
        public readonly string $model,
        public readonly int $inputtokens = 0,
        public readonly int $outputtokens = 0,
        public readonly int $totaltokens = 0,
        public readonly float $estimatedcost = 0.0,
        public readonly array $metadata = [],
    ) {
    }
}
