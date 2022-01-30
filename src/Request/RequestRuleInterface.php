<?php

namespace Devl0pr\RequestManagerBundle\Request;

/**
 * @author Cavid Huseynov <dev22843@gmail.com>
 */
interface RequestRuleInterface
{
    /**
     * Returns an array of request body content field mapping.
     */
    public function setValidationMap($key, $value);

    /**
     * Returns an array of request body content field mapping.
     *
     * @return array
     */
    public function getValidationMap(): array;

    /**
     * Runs every time after validation succeeded.
     * Implementation of this method is optional.
     *
     * @param RequestManager $requestManager
     *
     * @return mixed
     */
    public function onValidationStart(RequestManager $requestManager);

    public function onValidationEnd(RequestManager $requestManager);
}