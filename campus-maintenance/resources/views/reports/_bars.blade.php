<section class="sum-block">
    <h2>{{ $title }}</h2>
    @php $max = max(1, (int) collect($rows)->max()); @endphp

    @forelse ($rows as $label => $count)
        <div class="bar-row">
            <span class="bar-label">{{ $label }}</span>
            <span class="bar-track"><span class="bar-fill" style="width: {{ ($count / $max) * 100 }}%"></span></span>
            <span class="bar-count">{{ $count }}</span>
        </div>
    @empty
        <p class="hint">No reports this month.</p>
    @endforelse
</section>
