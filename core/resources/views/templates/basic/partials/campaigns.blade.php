@forelse($campaigns as $campaign)
    <div class="col-xl-4 col-sm-6">
        <x-campaign-card :campaign="$campaign" :loop-iteration="$loop->iteration" />
    </div>
@empty
    <div class="text-center mt-4">
        <p class="text-muted">{{ __('No campaigns found.') }}</p>
    </div>
@endforelse

@if ($campaigns->hasPages())
    <nav aria-label="Page navigation example" class="mt-5">
        {{ paginateLinks($campaigns) }}
    </nav>
@endif
