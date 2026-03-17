{{--
    Date Picker Component
    Props:
      $selectedDate — Carbon instance

    Mobile approach: the native <input type="date"> is stretched transparently over the
    visible display div. Tapping anywhere on the component triggers the native picker on
    ALL platforms (iOS Safari, Android Chrome, desktop). showPicker() is used as a
    desktop fallback when the input is not directly tapped.
--}}
@props(['selectedDate'])

<div class="date-picker-wrap">
    {{-- Visible display — pointer-events:none so taps fall through to the input below --}}
    <div class="date-display" aria-hidden="true">
        <svg class="date-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
        <span id="dateDisplayText">{{ $selectedDate->format('jS M Y') }}</span>
    </div>

    {{--
        The real input sits on top of the display div, invisible but fully interactive.
        This is the only cross-platform reliable way to open the native date picker on iOS.
    --}}
    <input type="date"
           name="date"
           id="ptDate"
           value="{{ $selectedDate->toDateString() }}"
           min="{{ now()->subYears(1)->startOfYear()->toDateString() }}"
           max="{{ now()->addYears(1)->endOfYear()->toDateString() }}"
           aria-label="Select date">
</div>
