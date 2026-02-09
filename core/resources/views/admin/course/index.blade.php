@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Image')</th>
                                    <th>@lang('Course Name')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Package')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Students')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                    <tr>
                                        <td>
                                            <div class="thumb">
                                                <img class="plugin_bg" src="{{ $course->image ? getImage(getFilePath('course') . '/' . $course->image, getFileThumb('course')) : asset('assets/images/default.png') }}" alt="@lang('image')" style="width: 80px; height: 50px; object-fit: cover;">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="name">{{ __($course->name) }}</span>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($course->description ?? '', 50) }}</small>
                                        </td>
                                        <td>{{ $course->category ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $packages = [1 => 'AdsLite', 2 => 'AdsPro', 3 => 'AdsSupreme', 4 => 'AdsPremium', 5 => 'AdsPremium+'];
                                                echo $packages[$course->required_package_id] ?? 'N/A';
                                            @endphp
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ showAmount($course->price) }} {{ $general->cur_sym }}</span>
                                        </td>
                                        <td>{{ number_format($course->students_count ?? 0) }}</td>
                                        <td>
                                            @if($course->status == 1)
                                                <span class="badge badge--success">@lang('Active')</span>
                                            @else
                                                <span class="badge badge--danger">@lang('Inactive')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.course.edit', $course->id) }}" class="btn btn-outline--primary btn-sm">
                                                    <i class="las la-pen"></i>@lang('Edit')
                                                </a>
                                                <button class="btn btn-sm btn-outline--danger confirmationBtn"
                                                        data-question="@lang('Are you sure to delete this course?')"
                                                        data-action="{{ route('admin.course.delete', $course->id) }}">
                                                    <i class="las la-trash"></i>@lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No courses found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($courses->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($courses) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search by course name" />
    <a href="{{ route('admin.course.create') }}" class="btn btn--lg btn-outline--primary">
        <i class="las la-plus"></i>@lang('Add New Course')
    </a>
@endpush
