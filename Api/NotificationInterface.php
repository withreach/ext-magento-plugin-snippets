<?php

namespace Reach\Payment\Api;

use Reach\Payment\Api\Data\ResponseInterface;

/**
 * @api
 */
interface NotificationInterface
{
    /**
     * @return ResponseInterface
     */
    public function handleNotification();
}
