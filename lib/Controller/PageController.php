<?php

declare(strict_types=1);

namespace OCA\WebRdp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        private IConfig $config,
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        Util::addScript('web-rdp', 'web-rdp-main');
        Util::addStyle('web-rdp', 'style');

        $response = new TemplateResponse('web-rdp', 'main');

        $wsUrl = $this->config->getAppValue('web-rdp', 'guaclite_url', 'ws://localhost:8080');
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedConnectDomain($wsUrl);
        $response->setContentSecurityPolicy($csp);

        return $response;
    }
}
