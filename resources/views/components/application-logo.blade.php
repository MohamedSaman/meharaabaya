<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <img src="{{ asset('images/logo.png') }}" alt="{{ config('shop.name') }}" class="h-12 w-auto rounded-lg shadow-sm">
    <div class="leading-tight">
        <p class="text-base font-bold text-slate-900">{{ config('shop.name') }}</p>
        <p class="text-xs text-slate-500">{{ config('shop.tagline') }}</p>
    </div>
</div>
