<?php

use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\Interactor\Form;

class FormActionTest extends PHPUnit\Framework\TestCase
{
    public function testActionElementIsRenderedWithModalAttribute()
    {
        $action = new class extends Action
        {
        };

        $html = (new Form($action))->addElementAttr(
            '<a class="form-action">导入数据</a>',
            '.form-action'
        );

        $this->assertMatchesRegularExpression(
            '/^<a class="form-action" modal="[^"]+">导入数据<\/a>$/u',
            $html
        );
    }
}
