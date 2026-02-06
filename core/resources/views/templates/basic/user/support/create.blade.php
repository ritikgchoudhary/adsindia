@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-md-12">
                <div class="card custom--card">
                    <div class="card-body">
                        <form action="{{ route('ticket.store') }}" class="disableSubmission" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form--label">@lang('Subject')</label>
                                    <input type="text" name="subject" value="{{ old('subject') }}" class="form--control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form--label">@lang('Priority')</label>
                                    <select name="priority" class="form--control select2" data-minimum-results-for-search="-1" required>
                                        <option value="3">@lang('High')</option>
                                        <option value="2">@lang('Medium')</option>
                                        <option value="1">@lang('Low')</option>
                                    </select>
                                </div>
                                <div class="col-12 form-group">
                                    <label class="form--label">@lang('Message')</label>
                                    <textarea name="message" id="inputMessage" rows="6" class="form--control" required>{{ old('message') }}</textarea>
                                </div>
                                <div class="col-md-9">
                                    <button type="button" class="btn btn--dark btn--sm addAttachment my-2"> <i class="fas fa-plus"></i> @lang('Add Attachment') </button>
                                    <p class="mb-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is ' . convertToReadableSize(ini_get('upload_max_filesize')) . ' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                                    <div class="row fileUploadsContainer">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn--base w-100 my-2" type="submit"><i class="las la-paper-plane"></i> @lang('Submit')
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }

        .select2+.select2-container .select2-selection__rendered {
            line-height: unset;
        }

        .select2-container--default .select2-selection--single {
            border-width: 1px !important;
            border-radius: 12px !important;
            border-color: var(--select2-border) !important;
        }


        .select2-container--open .select2-selection.select2-selection--single,
        .select2-container--open .select2-selection.select2-selection--multiple {
            border-radius: 12px !important;
        }

        .select2-container--default .select2-selection--single {
            padding: 16px 24px !important;
        }

        .select2+.select2-container .select2-selection__rendered {
            padding-right: 0px !important;
            padding-left: 0px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 28px !important;
        }

        .select2-results__option.select2-results__option--selected,
        .select2-results__option--selectable,
        .select2-container--default .select2-results__option--disabled {
            border-bottom-color: hsl(var(--border-color)) !important;
        }

        .select2-results__option.select2-results__option--selected,
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            color: hsl(var(--white)) !important;
            background-color: hsl(var(--base)) !important;
        }

        .select2-results__option.select2-results__option--selected,
        .select2-results__option--selectable,
        .select2-container--default .select2-results__option--disabled {
            border-bottom-color: hsl(var(--white)/0.2) !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            outline: none !important;
            box-shadow: none !important;
            border-color: hsl(var(--base)) !important;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            var fileAdded = 0;
            $('.addAttachment').on('click', function() {
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled', true)
                }
                $(".fileUploadsContainer").append(`
                    <div class="col-lg-4 col-md-12 removeFileInput">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control form--control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                                <button type="button" class="input-group-text removeFile bg--danger text-white border--danger"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                `)
            });
            $(document).on('click', '.removeFile', function() {
                $('.addAttachment').removeAttr('disabled', true)
                fileAdded--;
                $(this).closest('.removeFileInput').remove();
            });
        })(jQuery);
    </script>
@endpush
