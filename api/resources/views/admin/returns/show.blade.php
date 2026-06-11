@extends('admin.layout')

@section('title', 'Return ' . $returnRequest->return_number)

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Returns Desk</div>
                        <h2 class="mb-0">{{ $returnRequest->return_number }}</h2>
                        <p class="lead mb-0" style="margin-top: 8px;">Linked order: <a href="{{ route('admin.orders.show', $returnRequest->order) }}">{{ $returnRequest->order->order_number }}</a></p>
                    </div>
                    <a href="{{ route('admin.returns.index') }}" class="button secondary small">Back to Returns</a>
                </div>

                @if (session('status'))
                    <div class="message mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 15px; border-radius: var(--radius-md); font-weight: 500;">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-lg-7">
                        <section class="admin-section mb-4">
                            <h3 class="mb-3">Requested Items</h3>
                            <div class="table-wrap">
                                <table class="admin-data-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>SKU</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (($returnRequest->requested_items ?? []) as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if (!empty($item['image']))
                                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="width: 56px; height: 56px; border-radius: 12px; object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <strong>{{ $item['name'] }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $item['sku'] ?? 'N/A' }}</td>
                                                <td>{{ $item['quantity'] }}</td>
                                                <td>₹{{ number_format((float) ($item['price'] ?? 0), 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="admin-section">
                            <h3 class="mb-3">Customer Notes</h3>
                            <p class="mb-3"><strong>Reason:</strong> {{ $returnRequest->reason }}</p>
                            <p class="muted">{{ $returnRequest->customer_notes ?: 'No extra customer note provided.' }}</p>
                        </section>
                    </div>

                    <div class="col-lg-5">
                        <section class="admin-section">
                            <h3 class="mb-3">Resolution Controls</h3>
                            <form method="POST" action="{{ route('admin.returns.update', $returnRequest) }}">
                                @csrf
                                @method('PUT')

                                <div class="field mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="status">
                                        @foreach (['requested', 'approved', 'rejected', 'received', 'refunded'] as $status)
                                            <option value="{{ $status }}" @selected($returnRequest->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field mb-3">
                                    <label>Approved Refund Amount</label>
                                    <input type="number" step="0.01" min="0" name="approved_amount" value="{{ old('approved_amount', $returnRequest->approved_amount ?: $returnRequest->requested_amount) }}">
                                </div>

                                <div class="field mb-3">
                                    <label>Admin Notes</label>
                                    <textarea name="admin_notes" rows="5">{{ old('admin_notes', $returnRequest->admin_notes) }}</textarea>
                                </div>

                                <div class="mb-3 muted" style="font-size: 13px;">
                                    Requested amount: ₹{{ number_format($returnRequest->requested_amount, 2) }}<br>
                                    Stock restored: {{ $returnRequest->stock_restored_at ? $returnRequest->stock_restored_at->format('d M Y, h:i A') : 'No' }}
                                </div>

                                <button class="button small w-100" type="submit">Update Return Request</button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
