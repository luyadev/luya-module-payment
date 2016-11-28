<?php

namespace luya\payment\tests\data;

use luya\payment\base\Provider;

class DummyProvider extends Provider
{
    public function getId()
    {
        return 'DummyProvider';
    }
}