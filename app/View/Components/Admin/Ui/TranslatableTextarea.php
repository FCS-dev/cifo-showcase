<?php

namespace App\View\Components\Admin\Ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TranslatableTextarea extends Component
{
    public function __construct(
        public string $name,
        public ?string $label = null,
        public int $rows = 4,
        public ?string $placeholder = null,
        public mixed $valueEs = null,
        public mixed $valueCa = null,
        public ?string $help = null,
        public bool $required = false,
        public bool $disabled = false,
        public ?string $formVar = null,
        public ?int $maxlength = null,
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.ui.translatable-textarea');
    }
}
