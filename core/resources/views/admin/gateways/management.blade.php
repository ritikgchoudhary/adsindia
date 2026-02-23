@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gateways as $gateway)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb">
                                                    <img src="{{ getImage(getFilePath('gateway') . '/' . $gateway->image) }}" alt="image">
                                                </div>
                                                <span class="name">{{ __($gateway->name) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php echo $gateway->status_badge @endphp
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.gateway.management.toggle', $gateway->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @if($gateway->status == 1)
                                                    <button class="btn btn-sm btn--danger"><i class="la la-eye-slash"></i> @lang('Disable')</button>
                                                @else
                                                    <button class="btn btn-sm btn--success"><i class="la la-eye"></i> @lang('Enable')</button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-header bg--primary">
                    <h5 class="text-white">@lang('Custom QR Management')</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.gateway.management.upload.qr') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>@lang('Upload QR Images')</label>
                            <input type="file" name="qr_images[]" class="form-control" multiple accept="image/*">
                            <small class="text--small text-muted">@lang('You can upload multiple images at once (Max 4-6 recommended)')</small>
                        </div>
                        <button type="submit" class="btn btn--primary w-100">@lang('Upload')</button>
                    </form>

                    <div class="row mt-4">
                        @php $qrImages = json_decode($gateways->where('alias', 'custom_qr')->first()?->extra ?? '[]', true); @endphp
                        @foreach($qrImages as $index => $image)
                            <div class="col-md-3 mb-3">
                                <div class="card h-100">
                                    <img src="{{ getImage(getFilePath('gateway') . '/' . $image) }}" class="card-img-top" alt="QR Image">
                                    <div class="card-footer p-2">
                                        <form action="{{ route('admin.gateway.management.remove.qr', $index) }}" method="POST">
                                            @csrf
                                            <button class="btn btn--danger btn-sm w-100">@lang('Remove')</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
