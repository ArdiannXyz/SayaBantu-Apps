<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Livewire\Component;

class NotificationSettings extends Component
{
    public $generalNotification = true;

    public $sound = true;

    public $soundCall = false;

    public $vibrate = true;

    public $transactionUpdate = true;

    public function mount()
    {
        $user = auth()->user();
        $settings = $user ? $user->getNotificationSettings() : User::defaultNotificationSettings();

        $this->generalNotification = $settings['generalNotification'];
        $this->sound = $settings['sound'];
        $this->soundCall = $settings['soundCall'];
        $this->vibrate = $settings['vibrate'];
        $this->transactionUpdate = $settings['transactionUpdate'];
    }

    public function updateSetting($setting)
    {
        $allowed = array_keys(User::defaultNotificationSettings());
        if (! in_array($setting, $allowed, true) || ! property_exists($this, $setting)) {
            return;
        }

        $this->$setting = ! $this->$setting;

        $payload = [
            'generalNotification' => (bool) $this->generalNotification,
            'sound' => (bool) $this->sound,
            'soundCall' => (bool) $this->soundCall,
            'vibrate' => (bool) $this->vibrate,
            'transactionUpdate' => (bool) $this->transactionUpdate,
        ];

        $user = auth()->user();
        if ($user) {
            $user->forceFill(['notification_settings' => $payload])->save();
        }

        session()->flash('message', 'Pengaturan notifikasi berhasil diperbarui.');
        $this->dispatch('notification-settings-updated', settings: $payload);

        if ($this->$setting) {
            $this->dispatch('play-preview', setting: $setting);
        }
    }

    public function render()
    {
        return view('livewire.profile.notification-settings');
    }
}
