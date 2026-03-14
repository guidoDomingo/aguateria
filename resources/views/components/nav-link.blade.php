@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'group flex items-center px-2 py-2 text-sm font-medium text-blue-600 bg-blue-50 border-r-2 border-blue-600 mb-1'
            : 'group flex items-center px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 mb-1';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
