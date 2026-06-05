@props(['event', 'rounded' => ''])

@php
    // Photos événementielles (Unsplash CDN) choisies de façon déterministe.
    // Un dégradé sert de fond de repli si l'image ne charge pas (hors-ligne).
    $photos = [
        '1470229722913-7c0e2dbbafd3', // concert foule
        '1505373877841-8d25f7d46678', // conférence salle
        '1533174072545-7a4b6ad7a6c3', // festival
        '1492684223066-81342ee5ff30', // scène lumières
        '1459749411175-04bf5292ceea', // mains foule
        '1501281668745-f7f57925c3b4', // concert
        '1540575467063-178a50c2df87', // conférence tech
        '1516450360452-9312f5e86fc7', // DJ / soirée
    ];
    $gradients = [
        'from-violet-600 to-fuchsia-600', 'from-rose-600 to-orange-500',
        'from-sky-600 to-indigo-700', 'from-emerald-600 to-teal-600',
        'from-amber-500 to-pink-600', 'from-indigo-700 to-purple-800',
        'from-fuchsia-700 to-rose-600', 'from-cyan-600 to-blue-700',
    ];

    $seed  = abs(crc32($event->slug ?? $event->title));
    $photo = \App\Support\Media::url($event->image_path)
        ?: 'https://images.unsplash.com/photo-'.$photos[$seed % count($photos)].'?auto=format&fit=crop&w=900&q=70';
    $grad  = $gradients[$seed % count($gradients)];
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden bg-gradient-to-br {$grad} {$rounded}"]) }}>
    <img src="{{ $photo }}" alt="{{ $event->title }}" loading="lazy"
         class="absolute inset-0 w-full h-full object-cover"
         onerror="this.style.display='none'">
    {{ $slot ?? '' }}
</div>
