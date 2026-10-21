{{-- Footer: "Showing x–y of z" + plain pagination (styled in layouts/admin). Expects $p (paginator). --}}
@if($p->total() > 0)
    <div class="um-pager">
        <span class="um-pager-info">Showing {{ $p->firstItem() }}–{{ $p->lastItem() }} of {{ number_format($p->total()) }}</span>
        {{ $p->withQueryString()->links('pagination::default') }}
    </div>
@endif
