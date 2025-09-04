@props(['errors'])

@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'mb-4 rounded-lg border border-red-200 bg-red-50 p-3']) }}>
        <div class="flex items-center text-red-700 font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 9v4m0 4h.01M10.29 3.86l-8.48 14.7A1.7 1.7 0 0 0 3.17 21h17.66a1.7 1.7 0 0 0 1.36-2.44l-8.48-14.7a1.7 1.7 0 0 0-2.94 0z"/>
            </svg>
            <span>{{ __('Whoops! Something went wrong.') }}</span>
        </div>
        <ul class="mt-2 list-disc list-inside text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
