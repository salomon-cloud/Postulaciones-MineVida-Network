@props(['label' => null, 'required' => false, 'name'])

<div>
    @if ($label)
        <label class="lumoryx-label" for="{{ $name }}">{{ $label }} @if($required)<span class="text-rose-300">*</span>@endif</label>
    @endif
    <textarea id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'lumoryx-input mt-2']) }} @required($required)>{{ $slot }}</textarea>
    @error($name)<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
</div>
