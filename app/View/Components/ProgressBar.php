<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProgressBar extends Component
{
    public float $progress;
    public float $currentValue;
    public float $targetValue;
    public ?string $size;

    /**
     * Create a new component instance.
     */
    public function __construct(float $progress, float $currentValue, float $targetValue, ?string $size = 'default')
    {
        $this->progress = min(100, max(0, $progress));
        $this->currentValue = $currentValue;
        $this->targetValue = $targetValue;
        $this->size = $size;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.progress-bar');
    }

    /**
     * Get the color class based on progress
     */
    public function getColorClass(): string
    {
        if ($this->progress >= 100) {
            return 'bg-green-500';
        } elseif ($this->progress >= 50) {
            return 'bg-yellow-500';
        }
        return 'bg-blue-500';
    }

    /**
     * Get the height class based on size
     */
    public function getHeightClass(): string
    {
        return match($this->size) {
            'sm' => 'h-1.5',
            'lg' => 'h-4',
            default => 'h-2.5',
        };
    }
}
