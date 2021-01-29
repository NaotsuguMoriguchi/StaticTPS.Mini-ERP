@php
    $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp

@if(!empty($contact->custom_field1))
    <strong>{{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field1 }}
    </p>
@endif

@if(!empty($contact->custom_field2))
    <strong>{{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field2 }}
    </p>
@endif

@if(!empty($contact->custom_field3))
    <strong>{{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field3 }}
    </p>
@endif

@if(!empty($contact->custom_field4))
    <strong>{{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field4 }}
    </p>
@endif