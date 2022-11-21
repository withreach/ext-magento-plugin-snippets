<?php

namespace Reach\Payment\Model\Api\Data;

use Reach\Payment\Api\Data\SessionResponseInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class SessionResponse extends AbstractExtensibleObject implements SessionResponseInterface
{

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->_get(self::SUCCESS);
    }

    /**
     * @return void
     */
    public function setSuccess($text)
    {
        $this->setData(self::SUCCESS, $text);
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->_get(self::SESSION_ID);
    }

    /**
     * @return string
     */
    public function setSessionId($sessionId)
    {
        $this->setData(self::SESSION_ID, $sessionId);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_get(self::ERROR_MESSAGE);
    }

    /**
     * @return void
     */
    public function setErrorMessage($text)
    {
        $this->setData(self::ERROR_MESSAGE, $text);
    }
}
