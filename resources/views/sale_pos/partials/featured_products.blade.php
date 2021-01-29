<div class="row" id="featured_products_box" style="display: none;">
@foreach($featured_products as $variation)
	<div class="col-md-3 col-xs-4 product_list no-print">
		<div class="product_box" data-toggle="tooltip" data-placement="bottom" data-variation_id="{{$variation->id}}" title="{{$variation->full_name}}">

		<div class="image-container" 
			style="background-image: url(
					@if(count($variation->media) > 0)
						{{$variation->media->first()->display_url}}
					@elseif(!empty($variation->product->product_image))
						{{asset('/uploads/img/' . rawurlencode($variation->product->product_image))}}
					@else
						{{asset('/img/default.png')}}
					@endif
				);
			background-repeat: no-repeat; background-position: center;
			background-size: contain;">
			
		</div>

		<div class="text_div">
			<small class="text text-muted">{{$variation->product->name}} 
			@if($variation->product->type == 'variable')
				- {{$variation->name}}
			@endif
			</small>

			<small class="text-muted">
				({{$variation->sub_sku}})
			</small>
		</div>
			
		</div>
	</div>
@endforeach
</div>