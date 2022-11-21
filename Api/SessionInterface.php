<?php

namespace Reach\Payment\Api;

/**
 * @api
 */
interface SessionInterface
{
    const DATA_SESSION_ID = 'SessionId';

    /**
     * @param string $cartId
     * @param string $guestEmail
     * @return \Reach\Payment\Api\Data\SessionResponseInterface
     */
    public function generateSessionId($cartId, $guestEmail);
}
