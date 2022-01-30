<?php

namespace Devl0pr\RequestManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Devl0prRequestManagerBundle extends Bundle
{
	//Testwwwwwwwwwee
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}