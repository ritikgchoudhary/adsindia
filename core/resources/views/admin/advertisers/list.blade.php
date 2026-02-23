@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('Advertiser')</th>
                                <th>@lang('Email-Mobile')</th>
                                <th>@lang('Country')</th>
                                <th>@lang('Joined At')</th>
                                <th>@lang('Balance')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($advertisers as $advertiser)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{$advertiser->fullname}}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.advertisers.detail', $advertiser->id) }}"><span>@</span>{{ $advertiser->username }}</a>
                                    </span>
                                </td>


                                <td>
                                    {{ $advertiser->email }}<br>{{ $advertiser->mobileNumber }}
                                </td>
                                <td>
                                    <span class="fw-bold" title="{{ $advertiser->country_name }}">{{ $advertiser->country_code }}</span>
                                </td>



                                <td>
                                    {{ showDateTime($advertiser->created_at) }} <br> {{ diffForHumans($advertiser->created_at) }}
                                </td>


                                <td>
                                    <span class="fw-bold">

                                    {{ showAmount($advertiser->balance) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.advertisers.detail', $advertiser->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-desktop"></i> @lang('Details')
                                        </a>
                                        @if (request()->routeIs('admin.advertisers.kyc.pending'))
                                        <a href="{{ route('admin.advertisers.kyc.details', $advertiser->id) }}" target="_blank" class="btn btn-sm btn-outline--dark">
                                            <i class="las la-advertiser-check"></i>@lang('KYC Data')
                                        </a>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($advertisers->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($advertisers) }}
                </div>
                @endif
            </div>
        </div>


    </div>
@endsection



@push('breadcrumb-plugins')
    <x-search-form placeholder="Username / Email" />
@endpush
