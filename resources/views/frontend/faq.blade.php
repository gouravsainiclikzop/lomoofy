@extends('layouts.frontend')

@section('title', 'FAQ\'s - Lomoofy Industries')

@section('breadcrumbs')
			<!-- ======================= Top Breadcrubms ======================== -->
		 
			<!-- ======================= Top Breadcrubms ======================== -->
@endsection
			
@section('content')
			<!-- ======================= FAQ's Detail ======================== -->
			<section class="middle">
				<div class="container">
				
					<div class="row justify-content-center">
						<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
							<div class="sec_title position-relative text-center">
								<h2 class="off_title">FAQ's Section</h2>
								<h3 class="ft-bold pt-3">Frequently Asked Questions</h3>
							</div>
						</div>
					</div>
					
					<div class="row align-items-center justify-content-between">
						<div class="col-xl-12 col-lg-11 col-md-12 col-sm-12">
							
							@forelse($faqs as $faqIndex => $faq)
							<div class="d-block position-relative border rounded py-3 px-3 mb-4">
								<h4 class="ft-medium">{{ $faq->category }}</h4>
								<div id="accordion{{ $faqIndex }}" class="accordion">
									@foreach($faq->questions_answers as $qaIndex => $qa)
									<div class="card">
										<div class="card-header" id="h{{ $faqIndex }}_{{ $qaIndex }}">
										  <h5 class="mb-0">
											<button class="btn btn-link {{ $qaIndex > 0 ? 'collapsed' : '' }}" 
													data-bs-toggle="collapse" 
													data-bs-target="#ord{{ $faqIndex }}_{{ $qaIndex }}" 
													aria-expanded="{{ $qaIndex === 0 ? 'true' : 'false' }}" 
													aria-controls="ord{{ $faqIndex }}_{{ $qaIndex }}">
												{{ $qa['question'] }}
											</button>
										  </h5>
										</div>

										<div id="ord{{ $faqIndex }}_{{ $qaIndex }}" 
											 class="collapse {{ $qaIndex === 0 ? 'show' : '' }}" 
											 aria-labelledby="h{{ $faqIndex }}_{{ $qaIndex }}" 
											 data-parent="#accordion{{ $faqIndex }}">
										  <div class="card-body">
											{{ $qa['answer'] }}
										  </div>
										</div>
									</div>
									@endforeach
								</div>
							</div>
							@empty
							<div class="d-block position-relative border rounded py-5 px-3 mb-4 text-center">
								<div class="mb-3">
									<i class="fas fa-question-circle fa-4x text-muted"></i>
								</div>
								<h5 class="text-muted">No FAQs Available</h5>
								<p class="text-muted mb-0">Frequently asked questions will appear here once they are added.</p>
							</div>
							@endforelse
							
						</div>
					</div>
				</div>
			</section>
			<!-- ======================= FAQ's End ======================== -->
@endsection
