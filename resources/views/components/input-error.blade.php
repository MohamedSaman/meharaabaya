@props(['for'])

@error($for)
    <div {{ $attributes->merge(['class' => 'invalid-feedback d-block']) }}>{{ $message }}</div>
@enderror
