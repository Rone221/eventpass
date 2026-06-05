@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-ink-200 focus:border-brand-400 focus:ring-brand-400 rounded-lg shadow-sm']) }}>
