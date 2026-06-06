<?php

declare(strict_types=1);

namespace OCA\WebRdp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        private IConfig $config,
        private IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }

    public function getSettings(): DataResponse {
        return new DataResponse([
            'guacliteUrl' => $this->config->getAppValue('web-rdp', 'guaclite_url', 'ws://localhost:8080'),
            'guacSecret'  => $this->config->getAppValue('web-rdp', 'guac_secret', ''),
        ]);
    }

    public function saveSettings(): DataResponse {
        $data = json_decode($this->request->getContent(), true) ?? [];

        if (isset($data['guacliteUrl'])) {
            $this->config->setAppValue('web-rdp', 'guaclite_url', trim($data['guacliteUrl']));
        }
        if (isset($data['guacSecret'])) {
            $this->config->setAppValue('web-rdp', 'guac_secret', trim($data['guacSecret']));
        }

        return new DataResponse(['status' => 'ok']);
    }

    /**
     * @NoAdminRequired
     */
    public function getUserSettings(): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        return new DataResponse([
            'defaultResolution' => $this->config->getUserValue($userId, 'web-rdp', 'default_resolution', '1920x1080'),
            'autoReconnect'     => $this->config->getUserValue($userId, 'web-rdp', 'auto_reconnect', 'false') === 'true',
        ]);
    }

    /**
     * @NoAdminRequired
     */
    public function saveUserSettings(): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        $params = $this->request->getParams();

        if (array_key_exists('defaultResolution', $params)) {
            $allowed = ['1280x720', '1920x1080', '2560x1440'];
            if (in_array($params['defaultResolution'], $allowed, true)) {
                $this->config->setUserValue($userId, 'web-rdp', 'default_resolution', $params['defaultResolution']);
            }
        }
        if (array_key_exists('autoReconnect', $params)) {
            $this->config->setUserValue($userId, 'web-rdp', 'auto_reconnect', $params['autoReconnect'] ? 'true' : 'false');
        }

        return new DataResponse(['status' => 'ok']);
    }
}
