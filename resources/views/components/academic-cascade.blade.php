@props([
    'faculties',
    'optionsJson',
    'promotions' => null,
    'selectedFaculty' => null,
    'selectedOption' => null,
    'selectedPromotion' => null,
    'lockFaculty' => false,
    'requirePromotion' => true,
    'requireOption' => true,
])

@php
    $field = 'mb-4 min-h-12 w-full rounded-xl border px-3 py-3';
@endphp

<label class="mb-2 block text-sm">Faculté</label>
<select name="faculty_id" id="faculty_id" @required(true) @disabled($lockFaculty)
        class="{{ $field }}">
    <option value="">Choisir…</option>
    @foreach ($faculties as $faculty)
        <option value="{{ $faculty->id }}" @selected((string) old('faculty_id', $selectedFaculty) === (string) $faculty->id)>
            {{ $faculty->name }}
        </option>
    @endforeach
</select>
@if ($lockFaculty)
    <input type="hidden" name="faculty_id" value="{{ $selectedFaculty }}">
@endif

<label class="mb-2 block text-sm">Option</label>
<select name="option_id" id="option_id" @required($requireOption) class="{{ $field }}">
    <option value="">Choisir…</option>
</select>

@if ($promotions)
    <label class="mb-2 block text-sm">Promotion</label>
    <select name="promotion_id" id="promotion_id" @required($requirePromotion) class="{{ $field }}">
        <option value="">Choisir…</option>
        @foreach ($promotions as $promotion)
            <option value="{{ $promotion->id }}" @selected((string) old('promotion_id', $selectedPromotion) === (string) $promotion->id)>
                {{ $promotion->name }}
            </option>
        @endforeach
    </select>
@endif

<script>
    (function () {
        const optionsByFaculty = @json($optionsJson);
        const faculty = document.getElementById('faculty_id');
        const option = document.getElementById('option_id');
        const selected = @json((string) old('option_id', $selectedOption ?? ''));

        function fillOptions() {
            const list = optionsByFaculty[faculty.value] || [];
            option.innerHTML = '<option value="">Choisir…</option>';
            list.forEach(function (item) {
                const el = document.createElement('option');
                el.value = item.id;
                el.textContent = item.name;
                if (String(item.id) === String(selected)) {
                    el.selected = true;
                }
                option.appendChild(el);
            });
        }

        faculty.addEventListener('change', fillOptions);
        fillOptions();
    })();
</script>
