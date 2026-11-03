{{-- Session messages, shown as toasts by partials/flash-toast --}}
@foreach (['success' => 'success', 'status' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
    @if (is_string(session($key)) && session($key) !== '')
        <div class="dp-flash" data-type="{{ $type }}" hidden>{{ session($key) }}</div>
    @endif
@endforeach
@if (session('signed_out'))
    <div class="dp-flash" data-type="success" hidden>You have been signed out.</div>
@endif
