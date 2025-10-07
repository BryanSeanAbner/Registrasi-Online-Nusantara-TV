<style>
    /* --- Container styling --- */
    .failed-container {
        font-family: system-ui, sans-serif;
        font-size: 14px;
        color: #e5e7eb;
        background-color: #1e1e1e;
        padding: 8px;
        border-radius: 6px;
    }

    /* --- Table styling --- */
    .failed-table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        background-color: #2a2a2a;
        border-radius: 6px;
        overflow: hidden;
    }

    .failed-table thead {
        background-color: #333;
    }

    .failed-table th, .failed-table td {
        padding: 10px 12px;
        text-align: left;
    }

    .failed-table th {
        color: #bbb;
        font-weight: 600;
        font-size: 13px;
        border-bottom: 1px solid #444;
    }

    .failed-table td {
        border-bottom: 1px solid #333;
    }

    .failed-table tr:hover td {
        background-color: #383838;
    }

    /* --- Exception column --- */
    .text-red {
        color: #e57373;
    }

    .truncate {
        max-width: 380px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    /* --- Queue column --- */
    .queue {
        color: #f5f5f5;
        font-weight: 600;
    }

    /* --- No data text --- */
    .no-data {
        text-align: center;
        color: #aaa;
        padding: 24px;
        font-style: italic;
    }
</style>

<div class="failed-container">
    @if ($failed->isEmpty())
        <div class="no-data">Tidak ada failed jobs 🚀</div>
    @else
        <table class="failed-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Queue</th>
                    <th>Exception</th>
                    <th>Failed At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($failed as $job)
                    <tr>
                        <td>{{ $job->id }}</td>
                        <td class="queue">{{ $job->queue }}</td>
                        <td class="text-red truncate" title="{{ $job->exception }}">
                            {{ Str::limit($job->exception, 100) }}
                        </td>
                        <td>{{ $job->failed_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
