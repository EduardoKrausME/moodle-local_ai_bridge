<?php
namespace local_ai_bridge\local\bridge;

defined('MOODLE_INTERNAL') || die();

final class request {
    public function __construct(
        public readonly array $messages,
        public readonly ?float $temperature = null,
        public readonly ?int $maxoutputtokens = null,
        public readonly array $options = [],
    ) {
    }

    public function with_system_instruction(string $instruction): self {
        if (trim($instruction) === '') {
            return $this;
        }
        $messages = $this->messages;
        array_unshift($messages, ['role' => 'system', 'content' => $instruction]);
        return new self($messages, $this->temperature, $this->maxoutputtokens, $this->options);
    }
}
