@extends('layouts.admin')
@section('title', $title)
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $item->exists ? 'Edit' : 'Create' }} · {{ $title }}</h1>
    <form method="POST" action="{{ $item->exists ? route('admin.cms.content.update', [$type, $item->id]) : route('admin.cms.content.store', $type) }}" class="mt-6 max-w-2xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @if($item->exists) @method('PUT') @endif
        @foreach ($fields as $name => $fieldType)
            @if($fieldType === 'checkbox')
                <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $item->{$name}))> {{ str_replace('_', ' ', $name) }}</label>
            @elseif($fieldType === 'category')
                <div>
                    <label class="label" for="{{ $name }}">Category</label>
                    <select class="field" id="{{ $name }}" name="{{ $name }}">
                        <option value="">None</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old($name, $item->{$name}) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($fieldType === 'textarea')
                <div><label class="label" for="{{ $name }}">{{ str_replace('_', ' ', $name) }}</label><textarea class="field min-h-32" id="{{ $name }}" name="{{ $name }}">{{ old($name, $item->{$name}) }}</textarea></div>
            @elseif($fieldType === 'datetime')
                <div><label class="label" for="{{ $name }}">{{ str_replace('_', ' ', $name) }}</label><input class="field" type="datetime-local" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, optional($item->{$name})->format('Y-m-d\TH:i')) }}"></div>
            @elseif($fieldType === 'number')
                <div><label class="label" for="{{ $name }}">{{ str_replace('_', ' ', $name) }}</label><input class="field" type="number" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $item->{$name}) }}"></div>
            @else
                <div><label class="label" for="{{ $name }}">{{ str_replace('_', ' ', $name) }}</label><input class="field" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $item->{$name}) }}"></div>
            @endif
        @endforeach
        <div class="flex flex-wrap gap-2">
            <button class="btn-primary" type="submit">Save</button>
            @if($item->exists)
                <button class="btn-secondary" form="delete-cms-item" type="submit">Delete</button>
            @endif
        </div>
    </form>
    @if($item->exists)
        <form id="delete-cms-item" method="POST" action="{{ route('admin.cms.content.destroy', [$type, $item->id]) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')</form>
    @endif
@endsection