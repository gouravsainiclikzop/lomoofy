
@php
    $highlights = [];
    
    if ($serviceHighlight->highlight1_active && $serviceHighlight->highlight1_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight1_title,
            'text' => $serviceHighlight->highlight1_text,
            'icon' => $serviceHighlight->highlight1_icon ?: 'fas fa-shopping-basket'
        ];
    }
    if ($serviceHighlight->highlight2_active && $serviceHighlight->highlight2_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight2_title,
            'text' => $serviceHighlight->highlight2_text,
            'icon' => $serviceHighlight->highlight2_icon ?: 'far fa-credit-card'
        ];
    }
    if ($serviceHighlight->highlight3_active && $serviceHighlight->highlight3_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight3_title,
            'text' => $serviceHighlight->highlight3_text,
            'icon' => $serviceHighlight->highlight3_icon ?: 'fas fa-shield-alt'
        ];
    }
    if ($serviceHighlight->highlight4_active && $serviceHighlight->highlight4_title) {
        $highlights[] = [
            'title' => $serviceHighlight->highlight4_title,
            'text' => $serviceHighlight->highlight4_text,
            'icon' => $serviceHighlight->highlight4_icon ?: 'fas fa-headphones-alt'
        ];
    }
    
    $highlightCount = count($highlights);
    $colClass = $highlightCount > 0 ? 'col-xl-' . (12 / min($highlightCount, 4)) . ' col-lg-' . (12 / min($highlightCount, 4)) . ' col-md-6 col-sm-6' : 'col-xl-3 col-lg-3 col-md-6 col-sm-6';
@endphp

@if($highlightCount > 0)
<section class="px-0 py-3 br-top">
	<div class="container">
		<div class="row">
			@foreach($highlights as $highlight)
			<div class="{{ $colClass }}">
				<div class="d-flex align-items-center justify-content-start py-2">
					<div class="d_ico">
						<i class="{{ $highlight['icon'] }} theme-cl"></i>
					</div>
					<div class="d_capt">
						<h5 class="mb-0">{{ $highlight['title'] }}</h5>
						@if($highlight['text'])
							<span class="text-muted">{{ $highlight['text'] }}</span>
						@endif
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</section>
<!-- ======================= Customer Features ======================== -->
@endif
