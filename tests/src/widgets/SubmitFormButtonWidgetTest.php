<?php

namespace luya\payment\tests\widgets;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\widgets\SubmitFormButtonWidget;

class SubmitFormButtonWidgetTest extends BasePaymentTestCase
{
    public function testRun()
    {
        $this->assertSame('<button type="submit" class="btn" onclick="this.disabled=true; this.innerHTML=\'barfoo\';">foo</button>', SubmitFormButtonWidget::widget(['label' => 'foo', 'pushed' => 'barfoo', 'options' => ['class' => 'btn']]));
    }
}
