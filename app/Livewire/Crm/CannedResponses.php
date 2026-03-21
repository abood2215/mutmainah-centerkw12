<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\CannedResponse;

class CannedResponses extends Component
{
    public array $responses = [];
    public string $title    = '';
    public string $content  = '';
    public ?int $editingId  = null;
    public string $search   = '';

    public function mount(): void
    {
        $this->loadResponses();
    }

    public function loadResponses(): void
    {
        $this->responses = CannedResponse::orderBy('title')
            ->get()
            ->map(fn($r) => [
                'id'      => $r->id,
                'title'   => $r->title,
                'content' => $r->content,
            ])->values()->all();
    }

    public function saveResponse(): void
    {
        $this->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ], [
            'title.required'   => 'العنوان مطلوب',
            'content.required' => 'النص مطلوب',
        ]);

        if ($this->editingId) {
            $response = CannedResponse::find($this->editingId);
            if ($response) {
                $response->update([
                    'title'   => $this->title,
                    'content' => $this->content,
                ]);
            }
        } else {
            CannedResponse::create([
                'user_id' => auth()->id(),
                'title'   => $this->title,
                'content' => $this->content,
            ]);
        }

        $this->cancelEdit();
        $this->loadResponses();
    }

    public function editResponse(int $id): void
    {
        $response = CannedResponse::find($id);
        if (!$response) return;

        $this->editingId = $id;
        $this->title     = $response->title;
        $this->content   = $response->content;
    }

    public function deleteResponse(int $id): void
    {
        CannedResponse::where('id', $id)->delete();
        $this->loadResponses();

        if ($this->editingId === $id) {
            $this->cancelEdit();
        }
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->title     = '';
        $this->content   = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $filteredResponses = collect($this->responses)
            ->when($this->search, function ($col) {
                $q = mb_strtolower($this->search);
                return $col->filter(fn($r) =>
                    str_contains(mb_strtolower($r['title']), $q) ||
                    str_contains(mb_strtolower($r['content']), $q)
                );
            })->values()->all();

        return view('livewire.crm.canned-responses', compact('filteredResponses'))
            ->layout('layouts.app');
    }
}
