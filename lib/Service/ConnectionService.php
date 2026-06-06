<?php

declare(strict_types=1);

namespace OCA\WebRdp\Service;

use OCA\WebRdp\Db\RdpConnection;
use OCA\WebRdp\Db\RdpConnectionMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Security\ICrypto;

class ConnectionService {
    public function __construct(
        private RdpConnectionMapper $mapper,
        private ICrypto $crypto,
    ) {}

    /** @return array[] */
    public function getAll(string $userId): array {
        return array_map(
            fn(RdpConnection $c) => $c->toArray(),
            $this->mapper->findAllByUser($userId)
        );
    }

    public function create(string $userId, array $data): array {
        $this->validate($data);

        $conn = new RdpConnection();
        $conn->setUserId($userId);
        $conn->setName(trim($data['name']));
        $conn->setHost(trim($data['host']));
        $conn->setPort((int)($data['port'] ?? 3389));
        $conn->setUsername($data['username'] ?? '');
        $conn->setPassword($this->crypto->encrypt($data['password'] ?? ''));
        $domain = $data['domain'] ?? null;
        $conn->setDomain($domain ? trim($domain) : null);
        $conn->setWidth((int)($data['width'] ?? 1920));
        $conn->setHeight((int)($data['height'] ?? 1080));
        $conn->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));

        return $this->mapper->insert($conn)->toArray();
    }

    public function update(int $id, string $userId, array $data): array {
        $this->validate($data);

        $conn = $this->mapper->findById($id, $userId);
        $conn->setName(trim($data['name']));
        $conn->setHost(trim($data['host']));
        $conn->setPort((int)($data['port'] ?? 3389));
        $conn->setUsername($data['username'] ?? '');
        if (!empty($data['password'])) {
            $conn->setPassword($this->crypto->encrypt($data['password']));
        }
        $domain = $data['domain'] ?? null;
        $conn->setDomain($domain ? trim($domain) : null);
        $conn->setWidth((int)($data['width'] ?? 1920));
        $conn->setHeight((int)($data['height'] ?? 1080));

        return $this->mapper->update($conn)->toArray();
    }

    public function delete(int $id, string $userId): void {
        $conn = $this->mapper->findById($id, $userId);
        $this->mapper->delete($conn);
    }

    public function getDecrypted(int $id, string $userId): array {
        $conn = $this->mapper->findById($id, $userId);
        $data = $conn->toArray();
        $data['password'] = $this->crypto->decrypt($conn->getPassword());
        return $data;
    }

    private function validate(array $data): void {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Name is required');
        }
        if (empty($data['host'])) {
            throw new \InvalidArgumentException('Host is required');
        }
        $port = (int)($data['port'] ?? 3389);
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException('Port must be between 1 and 65535');
        }
    }
}
