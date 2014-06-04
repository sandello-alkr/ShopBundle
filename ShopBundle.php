<?php

namespace alkr\ShopBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopBundle extends Bundle
{
	public function getParent()
    {
        return 'ExtendCMSBundle';
    }
}
