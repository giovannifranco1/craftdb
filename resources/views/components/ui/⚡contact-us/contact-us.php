<?php

use App\Livewire\BaseComponent;
use App\Models\Feedback;
use Livewire\Attributes\Validate;

new class extends BaseComponent {
    #[Validate('required|string|max:2000')]
    public string $message = '';

    public function submit(): void
    {
        $this->validate();

        Feedback::create([
            'user_id' => Auth::id(),
            'message' => $this->message,
        ]);

        $this->message = '';
        $this->modal('contact-us')->close();
        $this->alert(__('Thank you for your feedback! We\'ll get back to you soon.'), type: 'default');
    }

    public function cancel(): void
    {
        $this->message = '';
        $this->modal('contact-us')->close();
    }
};
