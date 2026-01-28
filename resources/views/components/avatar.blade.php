@props([
    'name' => '',
    'path' => null,
    'size' => 'w-10 h-10',
    'pixelSize' => 128,
    'alt' => null,
    'class' => '',
])

@php
    $displayName = $name ?? '';
    $src = $path ? asset('storage/' . $path) : \App\Support\AvatarHelper::dataUri($displayName, (int) $pixelSize);
    $altText = $alt ?? $displayName;
@endphp

<img src="{{ $src }}"
     alt="{{ $altText }}"
     class="{{ $size }} rounded-full object-cover border-2 border-slate-600 {{ $class }}"
     loading="lazy"
     decoding="async">
