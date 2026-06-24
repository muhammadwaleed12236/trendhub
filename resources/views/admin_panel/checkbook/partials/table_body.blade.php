@foreach ($transactions as $txn)
    <tr>
        <td class="text-secondary fw-medium">{{ \Carbon\Carbon::parse($txn->entry_date)->format('d/m/Y') }}</td>
        <td class="text-secondary fw-medium">{{ $txn->description ?? '-' }}</td>
        <td class="text-secondary fw-medium">
            <span class="badge bg-light text-dark border">
                {{ $txn->account->title ?? 'Unknown' }}
            </span>
        </td>
        <td class="text-secondary fw-medium">
            @if($txn->source)
                @php
                    $sourceType = class_basename($txn->source_type);
                @endphp
                <span class="badge bg-info text-white">
                    {{ $sourceType }} #{{ $txn->source_id }}
                </span>
            @else
                -
            @endif
        </td>
        <td class="text-end fw-bold text-success">
            {{ $txn->debit > 0 ? number_format($txn->debit, 2) : '-' }}
        </td>
        <td class="text-end fw-bold text-danger">
            {{ $txn->credit > 0 ? number_format($txn->credit, 2) : '-' }}
        </td>
        <td class="text-end fw-bold {{ $txn->running_balance >= 0 ? 'text-primary' : 'text-danger' }}">
            {{ number_format($txn->running_balance, 2) }}
        </td>
    </tr>
@endforeach
