@props([
    'description' => 'Choose the workspace you want to use for this session.',
    'fieldId' => 'workspace-selector',
    'label' => 'Select your workspace',
    'model' => 'userType',
    'name' => 'role',
    'placeholder' => 'Select Workspace',
    'selected' => '',
    'workspaces' => [],
])

@php
    $workspaces = collect($workspaces)->values();
@endphp

<div class="workspace-select-field">
    <label class="workspace-select-field__label" for="{{ $fieldId }}">{{ $label }}</label>

    @if ($workspaces->isNotEmpty())
        <div class="workspace-select-field__control">
            <span class="workspace-select-field__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M4 7.5h16"></path>
                    <path d="M7 7.5V6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.5"></path>
                    <path d="M5 7.5h14v10A2.5 2.5 0 0 1 16.5 20h-9A2.5 2.5 0 0 1 5 17.5z"></path>
                    <path d="M9 12h6"></path>
                </svg>
            </span>

            <select id="{{ $fieldId }}" name="{{ $name }}" x-model="{{ $model }}" required>
                <option value="" disabled @selected($selected === '')>{{ $placeholder }}</option>
                @foreach ($workspaces as $workspace)
                    <option value="{{ $workspace['value'] }}" @selected($selected === (string) $workspace['value'])>
                        {{ $workspace['label'] }}
                    </option>
                @endforeach
            </select>

            <span class="workspace-select-field__chevron" aria-hidden="true">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"></path>
                </svg>
            </span>
        </div>
    @else
        <div class="workspace-select-field__control is-disabled">
            <span class="workspace-select-field__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M4 7.5h16"></path>
                    <path d="M7 7.5V6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.5"></path>
                    <path d="M5 7.5h14v10A2.5 2.5 0 0 1 16.5 20h-9A2.5 2.5 0 0 1 5 17.5z"></path>
                </svg>
            </span>

            <select id="{{ $fieldId }}" name="{{ $name }}" disabled>
                <option selected>No workspaces available</option>
            </select>
        </div>

        <p class="workspace-select-field__empty" role="status">
            No workspaces are available right now. Please contact OmniReferral support for access.
        </p>
    @endif

    <p class="workspace-select-field__hint">{{ $description }}</p>

    @error($name)
        <p class="workspace-select-field__error">{{ $message }}</p>
    @enderror
</div>
