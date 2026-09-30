@php
    $r = $report ?? null;
    $isEdit = $r !== null;
@endphp

@if ($errors->any())
    <div class="alert" role="alert">
        <strong>Please fix {{ $errors->count() === 1 ? 'this problem' : 'these ' . $errors->count() . ' problems' }} and try again.</strong>
    </div>
@endif

<fieldset class="form-section">
    <legend>Who is reporting?</legend>
    <div class="form-grid">
        <div class="field">
            <label for="reporter_name">Your full name</label>
            <input id="reporter_name" name="reporter_name" type="text" maxlength="100" required
                   value="{{ old('reporter_name', $r?->reporter_name) }}" placeholder="e.g. Maria Santos">
            @error('reporter_name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="reporter_role">You are a</label>
            <select id="reporter_role" name="reporter_role" required>
                <option value="">Choose one</option>
                @foreach (\App\Models\MaintenanceReport::ROLES as $role)
                    <option value="{{ $role }}" @selected(old('reporter_role', $r?->reporter_role) === $role)>{{ $role }}</option>
                @endforeach
            </select>
            @error('reporter_role') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field field--wide">
            <label for="reporter_email">Email (optional)</label>
            <input id="reporter_email" name="reporter_email" type="email" maxlength="150"
                   value="{{ old('reporter_email', $r?->reporter_email) }}" placeholder="you@example.com">
            <p class="hint">Add it if you want us to email you when the problem is fixed.</p>
            @error('reporter_email') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
</fieldset>

<fieldset class="form-section">
    <legend>Where is the problem?</legend>
    <div class="form-grid">
        <div class="field">
            <label for="building">Building</label>
            <input id="building" name="building" type="text" list="buildingList" maxlength="100" required
                   value="{{ old('building', $r?->building) }}" placeholder="Pick or type a building">
            <datalist id="buildingList">
                @foreach (\App\Models\MaintenanceReport::BUILDINGS as $building)
                    <option value="{{ $building }}">
                @endforeach
            </datalist>
            @error('building') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="room">Room or area</label>
            <input id="room" name="room" type="text" maxlength="50" required
                   value="{{ old('room', $r?->room) }}" placeholder="e.g. Room 204">
            @error('room') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
</fieldset>

<fieldset class="form-section">
    <legend>What needs fixing?</legend>

    <div class="field">
        <label for="title">Short title</label>
        <input id="title" name="title" type="text" maxlength="120" required
               value="{{ old('title', $r?->title) }}" placeholder="e.g. Ceiling lights keep flickering">
        @error('title') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="field">
        <label for="category">Type of problem</label>
        <select id="category" name="category" required>
            <option value="">Choose a category</option>
            @foreach (\App\Models\MaintenanceReport::CATEGORIES as $category)
                <option value="{{ $category }}" @selected(old('category', $r?->category) === $category)>{{ $category }}</option>
            @endforeach
        </select>
        @error('category') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="field">
        <label for="description">Tell us what happened</label>
        <textarea id="description" name="description" rows="5" maxlength="1000" required data-counter
                  placeholder="When did it start? Is anyone at risk?">{{ old('description', $r?->description) }}</textarea>
        <p class="hint"><span data-counter-for="description">0</span> of 1000 characters</p>
        @error('description') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="field">
        <label for="photo">Photo (optional)</label>
        <input id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" data-photo-input>
        <p class="hint">JPG, PNG or WEBP, up to 2 MB. A clear photo helps the team find the problem faster.</p>
        <img class="photo-preview" data-photo-preview hidden alt="Preview of the chosen photo">

        @if ($r?->photo_url)
            <div class="current-photo">
                <img src="{{ $r->photo_url }}" alt="Current photo of the problem">
                <label class="check">
                    <input type="checkbox" name="remove_photo" value="1" @checked(old('remove_photo'))>
                    Remove this photo
                </label>
            </div>
        @endif
        @error('photo') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="field">
        <span class="label">How urgent is it?</span>
        <div class="priority-picker" role="radiogroup" aria-label="Priority">
            @foreach (\App\Models\MaintenanceReport::PRIORITIES as $key => $label)
                <label class="priority-option priority-option--{{ $key }}">
                    <input type="radio" name="priority" value="{{ $key }}" required
                           @checked(old('priority', $r?->priority ?? 'medium') === $key)>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <p class="hint">Choose Urgent only if someone could get hurt.</p>
        @error('priority') <p class="field-error">{{ $message }}</p> @enderror
    </div>
</fieldset>

@if ($isEdit)
    <fieldset class="form-section">
        <legend>Maintenance team update</legend>
        <div class="form-grid">
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (\App\Models\MaintenanceReport::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $r->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="assigned_to">Assigned to</label>
                <input id="assigned_to" name="assigned_to" type="text" maxlength="100"
                       value="{{ old('assigned_to', $r->assigned_to) }}" placeholder="e.g. Mr. Reyes">
                @error('assigned_to') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </fieldset>
@endif
