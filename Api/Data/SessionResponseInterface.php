<?php

namespace Reach\Payment\Api\Data;

interface SessionResponseInterface
{
    const SUCCESS       = 'success';
    const ERROR_MESSAGE = 'error_message';
    const SESSION_ID   = 'session_id';

    /**
     * @return bool
     */
    public function getSuccess();

    /**
     * @return void
     */
    public function setSuccess($text);

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return void
     */
    public function setErrorMessage($text);

    /**
     * @return string
     */
    public function getSessionId();

    /**
     * @return string
     */
    public function setSessionId($sessionId);
}
