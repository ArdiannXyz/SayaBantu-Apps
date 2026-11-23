<?php

namespace App\Livewire\Home;

use App\Models\Category;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public function selectBantuan($categoryId = null)
    {
        if (! auth()->check()) {
            return redirect()->route('register', array_filter([
                'category' => $categoryId,
            ]));
        }

        if (auth()->user()->isCustomer()) {
            return redirect()->route('customer.helps.create', array_filter([
                'category_id' => $categoryId,
            ]));
        }

        if (auth()->user()->isMitra()) {
            return redirect()->route('mitra.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function render()
    {
        $categories = Category::where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->get();

        return view('livewire.home.index', [
            'categories' => $categories,
        ]);
    }
}

