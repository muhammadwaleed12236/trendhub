@if ($sales->hasPages())
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 px-1">
    <div class="text-muted small">
        Showing <strong>{{ $sales->firstItem() }}</strong>–<strong>{{ $sales->lastItem() }}</strong>
        of <strong>{{ $sales->total() }}</strong> sales
        &nbsp;|&nbsp; Page {{ $sales->currentPage() }} of {{ $sales->lastPage() }}
    </div>
    <div>
        {{ $sales->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endif
