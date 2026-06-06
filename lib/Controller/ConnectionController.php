<?php

declare(strict_types=1);

namespace OCA\WebRdp\Controller;

use OCA\WebRdp\Service\ConnectionService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserSession;

class ConnectionController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        private ConnectionService $service,
        private IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * @NoAdminRequired
     */
    public function index(): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        return new DataResponse($this->service->getAll($userId));
    }

    /**
     * @NoAdminRequired
     */
    public function create(): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        try {
            $data = $this->request->getParams();
            $connection = $this->service->create($userId, $data);
            return new DataResponse($connection, 201);
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function update(int $id): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        try {
            $data = $this->request->getParams();
            return new DataResponse($this->service->update($id, $userId, $data));
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Not found'], 404);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function destroy(int $id): DataResponse {
        $userId = $this->userSession->getUser()->getUID();
        try {
            $this->service->delete($id, $userId);
            return new DataResponse(null, 204);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Not found'], 404);
        }
    }
}
