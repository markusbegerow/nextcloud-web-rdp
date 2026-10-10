<?php

declare(strict_types=1);

namespace OCA\WebRdp\Controller;

use OCA\WebRdp\Service\ConnectionService;
use OCA\WebRdp\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
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
            $locale = $this->l10nFactory->findLocale() ?? $this->l10nFactory->findLanguage();
            $layout = $this->tokenService->layoutFromLocale((string)$locale);
            $token = $this->tokenService->generateToken($connectionData, $layout);
            $wsUrl = $this->tokenService->getWsUrl();
            return new DataResponse(['token' => $token, 'wsUrl' => $wsUrl]);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Connection not found'], 404);
        }
    }
}
