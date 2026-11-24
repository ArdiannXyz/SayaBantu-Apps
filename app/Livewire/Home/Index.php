<?php

namespace App\Livewire\Home;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\City;
use App\Models\Help;
use App\Models\UserBalance;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public $userLocation = 'Kabupaten Bantul';

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->city) {
            $this->userLocation = $user->city->name;
        } elseif (session()->has('visitor_location')) {
            $this->userLocation = session('visitor_location');
        } elseif (request()->header('CF-IPCity')) {
            $city = request()->header('CF-IPCity');
            $this->userLocation = str_contains(strtolower($city), 'jakarta') ? 'Kota Jakarta Pusat' : $city;
        } else {
            $this->userLocation = 'Kabupaten Bantul';
        }
    }

    public function setLocation($location)
    {
        if (! empty($location)) {
            $clean = trim($location);
            $matchedCity = City::where('is_active', true)
                ->where('name', 'like', '%'.$clean.'%')
                ->first();

            $this->userLocation = $matchedCity ? $matchedCity->name : $clean;
            session(['visitor_location' => $this->userLocation]);
        }
    }

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

        $user = auth()->user();
        $balance = 0;
        if ($user) {
            $userBalance = UserBalance::where('user_id', $user->id)->first();
            $balance = $userBalance ? (float) $userBalance->balance : 0;
        }

        $radius = (int) AppSetting::get('mitra_search_radius', 5);

        // Order terakhir
        if ($user) {
            $recentOrders = Help::with(['category', 'city', 'mitra'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(3)
                ->get();
            if ($recentOrders->isEmpty()) {
                $recentOrders = Help::with(['category', 'city', 'mitra'])
                    ->latest()
                    ->take(3)
                    ->get();
            }
        } else {
            $recentOrders = Help::with(['category', 'city', 'mitra'])
                ->latest()
                ->take(3)
                ->get();
        }

        $availableCities = City::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.home.index', [
            'categories' => $categories,
            'balance' => $balance,
            'userLocation' => $this->userLocation,
            'radius' => $radius,
            'recentOrders' => $recentOrders,
            'availableCities' => $availableCities,
        ]);
    }
}

