@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-12">
                <div class="card custom--card">
                    <div class="card-body">
                        <form action="{{ route('advertiser.deposit.manual.update') }}" method="POST" class="disableSubmission" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert--primary">
                                        <div class="alert__icon">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                        </div>
                                        <div class="alert__content">
                                            <h4 class="alert__title">@lang('Confirmation Alert!')</h4>
                                            <p class="alert__desc"><i class="las la-info-circle"></i> @lang('You are requesting') <b>{{ showAmount($data['amount']) }}</b> @lang('to deposit.') @lang('Please pay')
                                                <b>{{ showAmount($data['final_amount'], currencyFormat: false) . ' ' . $data['method_currency'] }} </b> @lang('for successful payment.')
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-3">@php echo  $data->gateway->description @endphp</div>

                                </div>

                                <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}" />

                            </div>
                            <button type="submit" class="btn btn--base w-100">@lang('Pay Now')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
