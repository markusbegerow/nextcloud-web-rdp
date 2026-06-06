<?php

declare(strict_types=1);

namespace OCA\WebRdp\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getHost()
 * @method void setHost(string $host)
 * @method int getPort()
 * @method void setPort(int $port)
 * @method string getUsername()
 * @method void setUsername(string $username)
 * @method string getPassword()
 * @method void setPassword(string $password)
 * @method string|null getDomain()
 * @method void setDomain(?string $domain)
 * @method int getWidth()
 * @method void setWidth(int $width)
 * @method int getHeight()
 * @method void setHeight(int $height)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 */
class RdpConnection extends Entity {
    protected string $userId = '';
    protected string $name = '';
    protected string $host = '';
    protected int $port = 3389;
    protected string $username = '';
    protected string $password = '';
    protected ?string $domain = null;
    protected int $width = 1920;
    protected int $height = 1080;
    protected string $createdAt = '';

    public function __construct() {
        $this->addType('port', 'integer');
        $this->addType('width', 'integer');
        $this->addType('height', 'integer');
    }

    public function toArray(): array {
        return [
            'id'        => $this->getId(),
            'name'      => $this->getName(),
            'host'      => $this->getHost(),
            'port'      => $this->getPort(),
            'username'  => $this->getUsername(),
            'domain'    => $this->getDomain(),
            'width'     => $this->getWidth(),
            'height'    => $this->getHeight(),
            'createdAt' => $this->getCreatedAt(),
        ];
    }
}
