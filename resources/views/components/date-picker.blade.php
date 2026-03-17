{{--
    Date Picker Component
    Props:
      $selectedDate — Carbon instance
--}}
@props(['selectedDate'])

<div class="date-picker-wrap" style="position:relative;">
    <div class="date-display" id="dateDisplay"
         onclick="document.getElementById('ptDate').showPicker()"
         role="button" tabindex="0"
         onkeydown="if(event.key==='Enter'||event.key===' '){document.getElementById('ptDate').showPicker()}">
        <svg class="date-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
        <span id="dateDisplayText">{{ $selectedDate->format('jS M Y') }}</span>
    </div>
    <input type="date"
           name="date"
           id="ptDate"
           value="{{ $selectedDate->toDateString() }}"
           min="{{ now()->subYears(1)->startOfYear()->toDateString() }}"
           max="{{ now()->addYears(1)->endOfYear()->toDateString() }}"
           style="position:absolute;opacity:0;width:1px;height:1px;top:0;left:0;pointer-events:none;"
           tabindex="-1">
</div>
