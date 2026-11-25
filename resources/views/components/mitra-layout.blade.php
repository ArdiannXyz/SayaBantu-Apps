@props(['title' => null])

@include('layouts.mitra', [
    'slot' => $slot,
    'title' => $title ?? null,
])
