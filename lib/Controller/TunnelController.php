<?php

declare(strict_types=1);

namespace OCA\WebRdp\Controller;

use OCA\WebRdp\Service\ConnectionService;
use OCA\WebRdp\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\L10N\IFactory;
use OCP\IUserSession;

class TunnelController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        private ConnectionService $connectionService,
        private TokenService $tokenService,
        private IUserSession $userSession,
        private IFactory $l10nFactory,
        private IConfig $config,
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * @NoAdminRequired
     */
    public function getToken(int $id): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        try {
            $connectionData = $this->connectionService->getDecrypted($id, $userId);
            // findLocale() almost always returns a value (often en_US), so prefer the
            // browser-detected layout, then the UI language, then the locale.
            // Priority: layout saved on the connection (describes the target) > user setting
            // > browser-detected layout > UI language > locale.
            $layout = $this->tokenService->sanitizeLayout($connectionData['keyboardLayout'] ?? null)
                ?? $this->tokenService->sanitizeLayout($this->config->getUserValue($userId, 'web-rdp', 'keyboard_layout', ''))
                ?? $this->tokenService->sanitizeLayout($this->request->getParam('layout'));
            if ($layout === null) {
                $fromLanguage = $this->l10nFactory->findLanguage();
                $layout = $this->tokenService->layoutFromLocale((string)$fromLanguage);
                if ($layout === 'en-us-qwerty') {
                    $layout = $this->tokenService->layoutFromLocale((string)$this->l10nFactory->findLocale());
                }
            }
            $token = $this->tokenService->generateToken($connectionData, $layout);
            $wsUrl = $this->tokenService->getWsUrl();
            return new DataResponse(['token' => $token, 'wsUrl' => $wsUrl]);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Connection not found'], 404);
        }
    }
}
