<?php

namespace App\View\Components\layout;

use App\Helpers\LandingPageHelper;
use App\Models\Koperasi;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HeaderGuest extends Component
{
    public array $landingPage;

    public function __construct(?array $landingPage = null)
    {
        $this->landingPage = $landingPage ?? LandingPageHelper::build(Koperasi::query()->first());
    }

    public function render(): View|Closure|string
    {
        return view('components.layout.header-guest');
    }
}
