@for ($i = 1; $i <= 5; $i++)
    <i class="bi {{ $n >= $i ? 'bi-star-fill' : ($n >= $i - 0.5 ? 'bi-star-half' : 'bi-star') }}"></i>
@endfor
