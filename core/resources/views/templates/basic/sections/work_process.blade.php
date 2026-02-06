@php
    $workProcess = getContent('work_process.content', true);
    $workSteps = getContent('work_process.element', orderById: true);
@endphp

<div class="how-work-section my-120">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-heading__subtitle">
                {{ __($workProcess?->data_values?->subtitle ?? '') }}
            </span>
            <h3 class="section-heading__title">
                {{ __($workProcess?->data_values?->heading ?? '') }}
            </h3>
            <p class="section-heading__desc">
                {{ __($workProcess?->data_values?->description ?? '') }}
            </p>
        </div>

        <div class="how-work-wrapper">
            <span class="bg-color"></span>
            <div class="row gy-5 justify-content-center">
                @foreach ($workSteps as $loopIndex => $step)
                    @php
                        $stepNumber = $loopIndex + 1;
                    @endphp
                    <div class="col-lg-4 col-sm-6 @if (!$loop->last) pe-lg-5 @endif">
                        <div class="how-work-item">
                            <span class="how-work-item__icon">
                                @php
                                    echo $step?->data_values?->icon;
                                @endphp
                                <span class="how-work-item__number">{{ $stepNumber }}</span>
                            </span>

                            <div class="how-work-item__content">
                                <h4 class="how-work-item__title">
                                    {{ __($step?->data_values?->title ?? '') }}
                                </h4>
                                <p class="how-work-item__desc">
                                    {{ __($step?->data_values?->description ?? '') }}
                                </p>
                            </div>
                            <div class="how-work-item__shape">
                                <img src="{{ asset($activeTemplateTrue . 'images/shapes/hw-2.png') }}" alt="@lang('shape')">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
