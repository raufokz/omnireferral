<label class="workspace-field workspace-field--full">
    <span>Assigned Realtor</span>
    <input
        type="text"
        id="realtorFilterSearch"
        placeholder="Type to search realtors..."
        autocomplete="off"
        oninput="filterRealtorOptions(this.value)"
        style="margin-bottom: 0.4rem;"
    >
    <select name="agent_id" id="realtorFilterSelect">
        <option value="">All realtors</option>
        @foreach($agents as $agent)
            <option value="{{ $agent->id }}" {{ (int) ($filters['agent_id'] ?? 0) === (int) $agent->id ? 'selected' : '' }}>
                {{ $agent->name }}
            </option>
        @endforeach
    </select>
</label>

<script>
    function filterRealtorOptions(query) {
        const select = document.getElementById('realtorFilterSelect');
        if (!select) return;
        const needle = query.trim().toLowerCase();

        Array.from(select.options).forEach(function (option) {
            if (option.value === '') return; // always keep "All realtors"
            const matches = option.text.toLowerCase().includes(needle);
            option.hidden = ! matches;
        });
    }
</script>
