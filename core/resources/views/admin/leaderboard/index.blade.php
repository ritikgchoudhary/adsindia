@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-header bg--primary">
                    <h5 class="text-white">Global Visibility Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.leaderboard.global.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Show Today Tab</label>
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="Show" data-off="Hide" name="lb_show_today" @if(gs('lb_show_today')) checked @endif>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Show Weekly Tab</label>
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="Show" data-off="Hide" name="lb_show_weekly" @if(gs('lb_show_weekly')) checked @endif>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Show Monthly Tab</label>
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="Show" data-off="Hide" name="lb_show_monthly" @if(gs('lb_show_monthly')) checked @endif>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Show All Time Tab</label>
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="Show" data-off="Hide" name="lb_show_all_time" @if(gs('lb_show_all_time')) checked @endif>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn--primary w-100">Update Global Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mt-4">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>User</th>
                                <th>Is Hidden</th>
                                <th>Ads Manual (T/W/M/A)</th>
                                <th>Aff Manual (T/W/M/A)</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{$user->fullname}}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.users.detail', $user->id) }}"><span>@</span>{{ $user->username }}</a>
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_lb_hidden)
                                        <span class="badge badge--danger">Hidden</span>
                                    @else
                                        <span class="badge badge--success">Visible</span>
                                    @endif
                                </td>
                                <td>
                                    {{ showAmount($user->lead_ads_today) }} / {{ showAmount($user->lead_ads_weekly) }} / {{ showAmount($user->lead_ads_monthly) }} / {{ showAmount($user->lead_ads_all_time) }}
                                </td>
                                <td>
                                    {{ showAmount($user->lead_aff_today) }} / {{ showAmount($user->lead_aff_weekly) }} / {{ showAmount($user->lead_aff_monthly) }} / {{ showAmount($user->lead_aff_all_time) }}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn--primary editBtn" 
                                        data-user="{{ $user }}"
                                        data-action="{{ route('admin.leaderboard.user.update', $user->id) }}">
                                        <i class="la la-pen"></i> Edit Hype
                                    </button>
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
                @if ($users->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($users) }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Leaderboard & Dashboard Hype</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h6 class="mb-3 text--primary text-center">Ads Dashboard Manual Income</h6>
                                <div class="form-group">
                                    <label>Add to Today</label>
                                    <input type="number" step="any" name="lead_ads_today" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Add to Weekly</label>
                                    <input type="number" step="any" name="lead_ads_weekly" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Add to Monthly</label>
                                    <input type="number" step="any" name="lead_ads_monthly" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Add to All Time</label>
                                    <input type="number" step="any" name="lead_ads_all_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3 text--success text-center">Affiliate Dashboard Manual Income</h6>
                                <div class="form-group">
                                    <label>Add to Today</label>
                                    <input type="number" step="any" name="lead_aff_today" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Add to Weekly</label>
                                    <input type="number" step="any" name="lead_aff_weekly" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Add to Monthly</label>
                                    <input type="number" step="any" name="lead_aff_monthly" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Add to All Time</label>
                                    <input type="number" step="any" name="lead_aff_all_time" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>Hide User from Leaderboard</label>
                            <input type="checkbox" data-width="100%" data-onstyle="-danger" data-offstyle="-success" data-bs-toggle="toggle" data-on="Hidden" data-off="Visible" name="is_lb_hidden">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <form action="" method="GET" class="form-inline float-sm-end bg--white mt-2">
        <div class="input-group has_append">
            <input type="text" name="search" class="form-control" placeholder="Username / Email" value="{{ request()->search }}">
            <div class="input-group-append">
                <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </form>
@endpush

@push('script')
<script>
    (function($){
        "use strict";
        $('.editBtn').on('click', function(){
            var modal = $('#editModal');
            var user = $(this).data('user');
            var action = $(this).data('action');
            
            modal.find('form').attr('action', action);
            modal.find('input[name=lead_ads_today]').val(user.lead_ads_today);
            modal.find('input[name=lead_ads_weekly]').val(user.lead_ads_weekly);
            modal.find('input[name=lead_ads_monthly]').val(user.lead_ads_monthly);
            modal.find('input[name=lead_ads_all_time]').val(user.lead_ads_all_time);
            
            modal.find('input[name=lead_aff_today]').val(user.lead_aff_today);
            modal.find('input[name=lead_aff_weekly]').val(user.lead_aff_weekly);
            modal.find('input[name=lead_aff_monthly]').val(user.lead_aff_monthly);
            modal.find('input[name=lead_aff_all_time]').val(user.lead_aff_all_time);
            
            if(user.is_lb_hidden == 1){
                modal.find('input[name=is_lb_hidden]').bootstrapToggle('on');
            }else{
                modal.find('input[name=is_lb_hidden]').bootstrapToggle('off');
            }
            
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
