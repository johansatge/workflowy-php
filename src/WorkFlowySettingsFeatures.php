<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy;

class WorkFlowySettingsFeatures
{
    private bool $useSignalWebsocket;

    /**
     * @param array<mixed> $array
     */
    public function __construct(array $array)
    {
        $this->useSignalWebsocket = $array['use_signal_websocket'] ?? false;
    }

    public function getUseSignalWebsocket(): bool
    {
        return $this->useSignalWebsocket;
    }
}
