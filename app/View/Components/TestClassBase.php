<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TestClassBase extends Component
{
    //クラスベースの属性の定義
    public $classBaseMessage;
    //初期値の定義
    public $defaultMessage;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($classBaseMessage, $defaultMessage="初期値です。")
    {
        $this->classBaseMessage = $classBaseMessage;

        $this->defaultMessage = $defaultMessage;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.tests.test-class-base-component');
    }
}
