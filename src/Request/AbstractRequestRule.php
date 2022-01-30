<?php

namespace Devl0pr\RequestManagerBundle\Request;

/**
 * @author Cavid Huseynov <dev22843@gmail.com>
 */
abstract class AbstractRequestRule implements RequestRuleInterface
{
    public function setValidationMap($key, $value)
    {
        // TODO: Implement setValidationMap() method.
    }

    public function onValidationStart(RequestManager $requestManager)
    {
    }

    public function onValidationEnd(RequestManager $requestManager)
    {
    }
}