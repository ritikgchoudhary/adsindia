@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('S.N.')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($traffics as $traffic)
                                    <tr>
                                        <td>
                                            <span>{{ $traffics->firstItem() + $loop->index }}</span>
                                        </td>
                                        <td>
                                            <span class="name">{{ __($traffic->name) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                echo $traffic->statusBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button class="btn btn-sm btn-outline--primary editButton" data-traffic="{{ $traffic }}">
                                                    <i class="la la-pencil"></i> @lang('Edit')
                                                </button>
                                                @if ($traffic->status == Status::ENABLE)
                                                    <button class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.traffic.status', $traffic->id) }}" data-question="@lang('Are you sure to disable this traffic')?">
                                                        <i class="la la-eye-slash"></i> @lang('Disable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.traffic.status', $traffic->id) }}" data-question="@lang('Are you sure to enable this traffic')?">
                                                        <i class="la la-eye"></i> @lang('Enable')
                                                    </button>
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
                        </table>
                    </div>
                </div>
                @if ($traffics->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($traffics) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="modal fade" id="trafficModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input class="form-control" name="name" type="text" value="{{ old('name') }}" required>
                        </div>
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
    <button class="btn btn--lg btn-outline--primary createButton" type="button"><i class="las la-plus"></i>@lang('Add New')</button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict"

            let modal = $('#trafficModal');
            $('.createButton').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add New Traffic')`);
                modal.find('form').attr('action', `{{ route('admin.traffic.store', '') }}`);
                modal.modal('show');
            });
            $('.editButton').on('click', function() {
                var traffic = $(this).data('traffic');
                modal.find('.modal-title').text(`@lang('Update Traffic')`);
                modal.find('form').attr('action', `{{ route('admin.traffic.store', '') }}/${traffic.id}`);
                modal.find('[name=name]').val(traffic.name);
                modal.modal('show')
            });

            modal.on('hidden.bs.modal', function() {
                $('#trafficModal form')[0].reset();
            });

        })(jQuery);
    </script>
@endpush
