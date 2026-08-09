{{--
    Shared AJAX table shell.

    Wrap a server-paginated table in this and its pagination stops being page
    navigation: pos-table.js fetches the next page, swaps the markup inside the
    viewport, and leaves the browser URL — and the scroll position — exactly
    where they were.

        <x-pos-table id="usage-ai-events" :state="['page' => $rows->currentPage(), 'per_page' => $perPage]">
            ...table, footer, pagination links...
        </x-pos-table>

    Contract for what goes inside:
      • pagination links render as usual ({{ $rows->links() }}) — anything inside
        a .pagination is intercepted;
      • a rows-per-page / filter form carries data-pos-table-form;
      • a filter link carries data-pos-table-link.
    Everything else (row actions, View buttons, mailto:) is left alone.

    `state` is the table's CURRENT state, and it is also the whitelist: only
    these keys are persisted to sessionStorage and only these are read back out
    of a URL. Pass page/per_page and any filter that is safe to remember; never
    pass a record id, a token or a cursor.

    Without JavaScript the links inside are still ordinary GET links, so the
    table keeps working (that path does show ?page= in the URL, as it did
    before).
--}}
@props(['id', 'state' => []])

<div class="pos-table"
     data-pos-table
     data-pos-table-id="{{ $id }}"
     data-pos-table-scope="{{ \App\Support\TableFragment::scopeToken() }}"
     data-pos-table-route="{{ optional(request()->route())->getName() ?: request()->path() }}"
     data-pos-table-endpoint="{{ url()->current() }}"
     data-pos-table-state="{{ json_encode((object) $state) }}"
     {{ $attributes }}>
    <div class="pos-table-viewport" data-pos-table-viewport>
        {{ $slot }}
    </div>
</div>
