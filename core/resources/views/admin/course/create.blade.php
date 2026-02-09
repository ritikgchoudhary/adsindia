@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    <form action="{{ route('admin.course.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Course Name') <span class="text--danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Slug')</label>
                                    <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated if empty">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Category')</label>
                                    <input type="text" class="form-control" name="category" value="{{ old('category') }}" placeholder="e.g., Video Editing, Graphic Design">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Duration')</label>
                                    <input type="text" class="form-control" name="duration" value="{{ old('duration') }}" placeholder="e.g., 10 hours">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Video URL')</label>
                                    <input type="url" class="form-control" name="video_url" value="{{ old('video_url') }}" placeholder="https://example.com/video.mp4">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Required Package') <span class="text--danger">*</span></label>
                                    <select class="form-control" name="required_package_id" required>
                                        <option value="1" {{ old('required_package_id') == 1 ? 'selected' : '' }}>AdsLite (Package 1)</option>
                                        <option value="2" {{ old('required_package_id') == 2 ? 'selected' : '' }}>AdsPro (Package 2)</option>
                                        <option value="3" {{ old('required_package_id') == 3 ? 'selected' : '' }}>AdsSupreme (Package 3)</option>
                                        <option value="4" {{ old('required_package_id') == 4 ? 'selected' : '' }}>AdsPremium (Package 4)</option>
                                        <option value="5" {{ old('required_package_id') == 5 ? 'selected' : '' }}>AdsPremium+ (Package 5)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Price') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="price" value="{{ old('price', 0) }}" step="0.01" min="0" required>
                                        <span class="input-group-text">{{ $general->cur_sym }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Students Count')</label>
                                    <input type="number" class="form-control" name="students_count" value="{{ old('students_count', 0) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Sort Order')</label>
                                    <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Affiliate Commission (%)')</label>
                                    <input type="number" class="form-control" name="affiliate_commission_percent" value="{{ old('affiliate_commission_percent', 0) }}" step="0.01" min="0" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Affiliate Commission (Fixed)')</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="affiliate_commission_fixed" value="{{ old('affiliate_commission_fixed', 0) }}" step="0.01" min="0">
                                        <span class="input-group-text">{{ $general->cur_sym }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Course Image')</label>
                                    <x-image-uploader class="w-100" type="course" image="" :required=false />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Status') <span class="text--danger">*</span></label>
                                    <select class="form-control" name="status" required>
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>@lang('Active')</option>
                                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>@lang('Inactive')</option>
                                    </select>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_recommended" value="1" id="is_recommended" {{ old('is_recommended') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_recommended">
                                            @lang('Mark as Recommended')
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn--primary w-100 h-45">
                                <i class="fa fa-send"></i> @lang('Create Course')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.course.index') }}" class="btn btn--lg btn-outline--primary">
        <i class="las la-arrow-left"></i>@lang('Back')
    </a>
@endpush
