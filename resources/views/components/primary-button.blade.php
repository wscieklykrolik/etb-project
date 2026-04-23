<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-black border-2 border-black rounded-md font-semibold text-xs text-yellow-300 uppercase tracking-widest hover:bg-zinc-800 focus:bg-zinc-800 active:bg-black focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 focus:ring-offset-yellow-400 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
