<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\BusinessHour;
use App\Models\AutoReply;

class BusinessHours extends Component
{
    // 7 days: index 0=Sun, 1=Mon, ..., 6=Sat
    public array $hours = [];

    // Auto-reply for outside_hours trigger
    public string $autoReplyMessage   = '';
    public bool   $autoReplyIsActive  = false;

    // Arabic day names indexed by day_of_week (0=Sun)
    private array $dayNames = [
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    public function mount(): void
    {
        $this->loadHours();
        $this->loadAutoReply();
    }

    public function loadHours(): void
    {
        $this->hours = [];

        for ($day = 0; $day <= 6; $day++) {
            $record = BusinessHour::firstOrCreate(
                ['day_of_week' => $day],
                [
                    'start_time' => '09:00:00',
                    'end_time'   => '17:00:00',
                    'is_active'  => !in_array($day, [5, 6]), // Fri & Sat off by default
                ]
            );

            $this->hours[$day] = [
                'id'         => $record->id,
                'day_of_week' => $day,
                'day_name'   => $this->dayNames[$day],
                'start_time' => substr($record->start_time, 0, 5), // HH:MM
                'end_time'   => substr($record->end_time, 0, 5),
                'is_active'  => (bool) $record->is_active,
            ];
        }
    }

    public function loadAutoReply(): void
    {
        $record = AutoReply::where('trigger', 'outside_hours')->first();

        if ($record) {
            $this->autoReplyMessage  = $record->message;
            $this->autoReplyIsActive = (bool) $record->is_active;
        } else {
            $this->autoReplyMessage  = '';
            $this->autoReplyIsActive = false;
        }
    }

    public function saveHours(): void
    {
        foreach ($this->hours as $day => $data) {
            BusinessHour::where('day_of_week', $day)->update([
                'start_time' => ($data['start_time'] ?? '09:00') . ':00',
                'end_time'   => ($data['end_time'] ?? '17:00') . ':00',
                'is_active'  => (bool) ($data['is_active'] ?? false),
            ]);
        }

        session()->flash('hours_saved', 'تم حفظ ساعات العمل بنجاح');
    }

    public function saveAutoReply(): void
    {
        AutoReply::updateOrCreate(
            ['trigger' => 'outside_hours'],
            [
                'message'   => $this->autoReplyMessage,
                'is_active' => $this->autoReplyIsActive,
            ]
        );

        session()->flash('reply_saved', 'تم حفظ الرد التلقائي بنجاح');
    }

    public function render()
    {
        return view('livewire.crm.business-hours')->layout('layouts.app');
    }
}
