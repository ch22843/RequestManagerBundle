<?php

namespace Devl0pr\RequestManagerBundle\Request;

/**
 * @author Cavid Huseynov <dev22843@gmail.com>
 */
abstract class AbstractRequestRule implements RequestRuleInterface
{
    /**
     * @inheritdoc
     */
    public function onValidationStart(RequestManager $smartRequest)
    {
    }

    /**
     * @inheritdoc
     */
    public function onValidationEnd(RequestManager $smartRequest)
    {
    }
}