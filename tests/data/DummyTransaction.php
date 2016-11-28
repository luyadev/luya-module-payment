<?php

namespace luya\payment\tests\data;

use luya\payment\base\Transaction;
use luya\payment\base\TransactionInterface;
use luya\payment\tests\data\DummyProvider;

class DummyTransaction extends Transaction implements TransactionInterface
{
    public function create()
    {
    }
    
    public function back()
    {
    }
    
    public function notify()
    {
    }
    
    public function fail()
    {
    }
    
    public function abort()
    {
    }
    
    public function getProvider()
    {
        return new DummyProvider();
    }
}
