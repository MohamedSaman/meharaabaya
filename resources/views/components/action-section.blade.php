<div {{ $attributes->merge(['class' => 'row']) }}>
    <div class="col-md-4 mb-3">
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                {{ $content }}
            </div>
        </div>
    </div>
</div>
