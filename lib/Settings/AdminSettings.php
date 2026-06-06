<?php

declare(strict_types=1);

namespace OCA\WebRdp\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {
    public function __construct(private IConfig $config) {}

    public function getForm(): TemplateResponse {
        return new TemplateResponse('web-rdp', 'admin', [
            'guacliteUrl' => $this->config->getAppValue('web-rdp', 'guaclite_url', 'ws://localhost:8080'),
            'guacSecret'  => $this->config->getAppValue('web-rdp', 'guac_secret', ''),
        ]);
    }

    public function getSection(): string {
        return 'connected-accounts';
    }

    public function getPriority(): int {
        return 50;
    }
}
