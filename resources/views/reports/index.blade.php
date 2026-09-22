@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-0">Reports &amp; Analytics</h1>
        <p class="text-muted small mb-0">Analyze sales, inventory, stock movements, and business performance.</p>
    </div>

    <div class="row g-3">

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:44px;height:44px;">
                        <i class="bi bi-graph-up-arrow fs-5"></i>
                    </span>
                    <h2 class="h6 fw-bold mb-2">Sales Report</h2>
                    <p class="text-muted small flex-grow-1">Revenue, transactions, and sales trends over time.</p>
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary btn-sm mt-2">View Report</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <span class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:44px;height:44px;">
                        <i class="bi bi-boxes fs-5"></i>
                    </span>
                    <h2 class="h6 fw-bold mb-2">Inventory Report</h2>
                    <p class="text-muted small flex-grow-1">Current stock levels and value across all stores.</p>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-outline-primary btn-sm mt-2">View Report</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <span class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:44px;height:44px;">
                        <i class="bi bi-arrow-down-up fs-5"></i>
                    </span>
                    <h2 class="h6 fw-bold mb-2">Stock Movement Report</h2>
                    <p class="text-muted small flex-grow-1">Full history of stock in, stock out, and adjustments.</p>
                    <a href="{{ route('reports.stock-movements') }}" class="btn btn-outline-primary btn-sm mt-2">View Report</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <span class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:44px;height:44px;">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </span>
                    <h2 class="h6 fw-bold mb-2">Stock Transfer Report</h2>
                    <p class="text-muted small flex-grow-1">Transfer activity and status between stores.</p>
                    <a href="{{ route('reports.transfers') }}" class="btn btn-outline-primary btn-sm mt-2">View Report</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:44px;height:44px;">
                        <i class="bi bi-box-seam fs-5"></i>
                    </span>
                    <h2 class="h6 fw-bold mb-2">Product Performance</h2>
                    <p class="text-muted small flex-grow-1">Best and worst-selling products by units and revenue.</p>
                    <a href="{{ route('reports.products') }}" class="btn btn-outline-primary btn-sm mt-2">View Report</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <span class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:44px;height:44px;">
                        <i class="bi bi-diagram-3 fs-5"></i>
                    </span>
                    <h2 class="h6 fw-bold mb-2">Branch Performance</h2>
                    <p class="text-muted small flex-grow-1">Compare revenue and activity across branches.</p>
                    <a href="{{ route('reports.branches') }}" class="btn btn-outline-primary btn-sm mt-2">View Report</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <span class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:44px;height:44px;">
                        <i class="bi bi-shop fs-5"></i>
                    </span>
                    <h2 class="h6 fw-bold mb-2">Store Performance</h2>
                    <p class="text-muted small flex-grow-1">Compare sales performance across individual stores.</p>
                    <a href="{{ route('reports.stores') }}" class="btn btn-outline-primary btn-sm mt-2">View Report</a>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
@endsection
