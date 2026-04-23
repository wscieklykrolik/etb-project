@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white text-black border-2 border-black focus:border-black focus:ring-2 focus:ring-black rounded-md shadow-sm']) }}>
