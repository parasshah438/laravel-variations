@if($products->hasPages())
    <nav aria-label="Products pagination">
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            @if ($products->onFirstPage())
                <li class="page-item disabled"><span class="page-link">‹</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">‹</a></li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                @if ($page == $products->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($products->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">›</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">›</span></li>
            @endif
        </ul>
    </nav>

    <div class="text-center mt-3">
        <small class="text-muted">
            Page {{ $products->currentPage() }} of {{ $products->lastPage() }} 
            ({{ number_format($products->total()) }} total products)
        </small>
    </div>
@endif
