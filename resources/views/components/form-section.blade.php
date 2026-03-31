@props(['submit'])

<div {{ $attributes->merge(['class' => 'row']) }}>
    <div class="col-md-4 mb-3">
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>
    </div>

    <div class="col-md-8">
        <form wire:submit="{{ $submit }}">
            <div class="card border-0 shadow-sm mb-0">
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{ $form }}
                    </div>
                </div>

                @if (isset($actions))
                    <div class="card-footer bg-light d-flex justify-content-end align-items-center py-3 border-top">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>
