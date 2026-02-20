@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="my-120">
        <div class="container">
            @php
                echo $policy?->data_values?->details ?? ($policy?->data_values?->description ?? '');
            @endphp
        </div>
    </section>
@endsection
