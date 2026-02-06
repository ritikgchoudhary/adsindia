@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Advertiser')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Featured')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ strLimit($campaign->title, 40) }}</span><br>
                                            <small class="text-muted">{{ showAmount($campaign->payout_per_conversion) }} / @lang('conversion')</small>
                                        </td>

                                        <td>
                                            <span class="fw-bold">{{ $campaign?->advertiser?->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ route('admin.advertisers.detail', $campaign?->advertiser?->id) }}"><span>@</span>{{ $campaign?->advertiser?->username }}</a>
                                            </span>
                                        </td>

                                        <td>{{ $campaign->category->name ?? '-' }}</td>


                                        <td>@php echo $campaign->statusBadge; @endphp</td>

                                        <td>
                                            @php echo $campaign->featuredBadge; @endphp
                                        </td>

                                        <td>
                                            {{ showDateTime($campaign->created_at) }}<br>
                                            <small>{{ diffForHumans($campaign->created_at) }}</small>
                                        </td>

                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.campaign.details', $campaign->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="las la-desktop"></i> @lang('Details')
                                                </a>

                                                @if ($campaign->is_featured == Status::YES)
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline--danger confirmationBtn"
                                                            data-action="{{ route('admin.campaign.toggle.featured', $campaign->id) }}"
                                                            data-question="@lang('Are you sure to remove this campaign from Featured?')">
                                                        <i class="las la-star-half-alt"></i> @lang('Non-Feature')
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline--success confirmationBtn"
                                                            data-action="{{ route('admin.campaign.toggle.featured', $campaign->id) }}"
                                                            data-question="@lang('Are you sure to mark this campaign as Featured?')">
                                                        <i class="las la-star"></i> @lang('Feature')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">
                                            {{ __($emptyMessage) }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($campaigns->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($campaigns) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Title / Advertiser" />
@endpush
