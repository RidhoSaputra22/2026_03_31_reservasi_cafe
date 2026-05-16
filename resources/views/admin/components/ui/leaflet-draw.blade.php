{{--
    Reusable Leaflet Map with Drawing Capabilities

    @param string $id - Map container ID (default: 'draw-map')
    @param string $height - Map height CSS (default: '500px')
    @param array $center - Map center [lat, lng] (default: [-5.155, 119.466])
    @param int $zoom - Initial zoom level (default: 15)
    @param bool $drawControl - Show drawing controls (default: true)
    @param bool $editable - Allow editing existing shapes (default: true)
    @param string $color - Default drawing color (default: '#3b82f6')
    @param float $fillOpacity - Default fill opacity (default: 0.3)

    Usage:
    <x-ui.leaflet-draw
        id="my-map"
        height="600px"
        :center="[-5.155, 119.466]"
        :zoom="15"
        color="#ef4444"
    />

    Alpine.js integration: wrap in x-data with leafletDraw() helper.
--}}

@props([
'id' => 'draw-map',
'height' => '500px',
'center' => [-5.155, 119.466],
'zoom' => 15,
'drawControl' => true,
'editable' => true,
'color' => '#3b82f6',
'fillOpacity' => 0.3,
])

@once
@push('styles')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
{{-- Leaflet Draw CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />

@endpush

@push('scripts')
{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
{{-- Leaflet Draw JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
@endpush
@endonce

<div id="{{ $id }}" class="leaflet-draw-map w-full" style="height: {{ $height }};"></div>
